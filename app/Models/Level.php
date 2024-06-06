<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
use Request;
use URL;
use DB;
class Level extends Model
{
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'levels';
    protected $primaryKey = '_id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['level', 'exp', 'rewards'];
    protected $hidden = ['created_at', 'updated_at'];
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

        $value = json_encode($value);
        return $value;
    
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

    public function setLevelAttribute($value){
        $this->attributes['level'] = (int)$value;
    
    }

    public function save(array $options = [])
    {
    
        return parent::save($options); // Call the parent save method to save the model
    }


}
