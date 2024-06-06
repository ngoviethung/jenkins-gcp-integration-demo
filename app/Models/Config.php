<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
use Request;
use URL;
use DB;
class Config extends Model
{
    
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'configs';
    protected $primaryKey = '_id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['metadata', 'time_voting', 'time_between_ad'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = [''];

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




    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function getVotingRewardsDefaultAttribute(){
    
        $metadata = $this->metadata;
        foreach($metadata as $data){
            if($data['key'] == 'voting_rewards_default'){
                return json_decode($data['value']);
            }
        }
        return 0;

    }
    public function getTimeVotingAttribute(){
    
        $metadata = $this->metadata;
        foreach($metadata as $data){
            if($data['key'] == 'time_voting'){
                return json_decode($data['value']);
            }
        }
        return 0;

    }

    

    public function setMetadataAttribute($value){

        $metadata = json_decode($value, true);
        $this->attributes['metadata'] = $metadata;
    
    }


}
