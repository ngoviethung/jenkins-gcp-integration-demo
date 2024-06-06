<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
use Request;
use URL;
use DB;
use App\Services\Challenge\ChallengeService;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use CrudTrait;
    use ModelTrait;
    use SoftDeletes;
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'challenges';
    protected $primaryKey = '_id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = [
        'name', 'cover', 'background', 'short_description',
        'long_description', 'start_time', 'end_time', 'tag',
        'max_unworn_value', 'entry_reward', 'requirement', 'dress_code', 'prizes'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    // protected $dates = [];
    protected $appends = [];


    public function service()
    {
        return new ChallengeService($this);
    }



    public function setNameAttribute($value){
        $this->attributes['name'] = trim($value);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }


    public function getPrizesAttribute($value){

        $value = json_encode($value);
        return $value;
    
    }
    public function getDressCodeAttribute($value){

        $value = json_encode($value);
        return $value;
    
    }

    public function setEntryRewardAttribute($value){
        $this->attributes['entry_reward'] = (int)$value;
    }

    public function setRequireCostAttribute($value){
        $this->attributes['max_unworn_value'] = (int)$value;
    }

    public function setPrizesAttribute($value){

        $value = json_decode($value, true);
        $this->attributes['prizes'] = $value;
    
    }
    public function setDressCodeAttribute($value){

        $value = json_decode($value, true);
        $this->attributes['dress_code'] = $value;
    
    }

    public function setCoverAttribute($value)
    {
        $this->uploadImageToDisk($value, 'cover', 'public', 'uploads/challenge/cover');
    }
    public function setBackgroundAttribute($value)
    {
        $this->uploadImageToDisk($value, 'background', 'public', 'uploads/challenge/background');
    }

    

}
