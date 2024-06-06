<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
use Request;
use URL;
use DB;
class Shop extends Model
{
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'shops';
    protected $primaryKey = '_id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['metadata', 'version'];
    protected $hidden = ['created_at', 'updated_at'];
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

    public function getMetadataAttribute($value){

        $metadata = json_encode($value);

        return $metadata;
    
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setMetadataAttribute($value){

        $metadata = json_decode($value, true);
        $this->attributes['metadata'] = $metadata;
    
    }


}
