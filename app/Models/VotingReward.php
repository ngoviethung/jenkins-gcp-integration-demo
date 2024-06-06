<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
use Request;
use URL;
use DB;
class VotingReward extends Model
{
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'voting_rewards';
    protected $primaryKey = '_id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['streak', 'step', 'rewards'];
    protected $hidden = [];
    // protected $dates = [];
    //protected $appends = ['id'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    public function getRewardsAttribute($value){

        if(!Request::is('api*')){
            $value = json_encode($value);
        }
        return $value;
    
    }
    public function getStreakAttribute($value){
        return (int)$value;
    }
    public function getStepAttribute($value){
        return (int)$value;
    }

    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setRewardsAttribute($value){

        $value = json_decode($value, true);
        $this->attributes['rewards'] = $value;
    
    }
    public function setStreakAttribute($value){
        $this->attributes['streak'] = (int)$value;
    }
    public function setStepAttribute($value){
        $this->attributes['step'] = (int)$value;
    }

    public function save(array $options = [])
    {
    
        return parent::save($options); // Call the parent save method to save the model
    }


}
