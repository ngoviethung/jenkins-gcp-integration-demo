<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\ModelTrait;
use Request;
use URL;
use App\Services\User\UserService;

use Jenssegers\Mongodb\Auth\User as Authenticatable;
class User extends Authenticatable implements JWTSubject
{
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $connection = 'mongodb';
    protected $table = 'users';
    protected $primaryKey = '_id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['_id', 'name', 'facebook_name', 'email', 'google_id',
     'facebook_id', 'avatar', 'ticket', 'hard', 'soft', 'ip', 'uuid', 'exp', 'level', 'current_streak_vote'];
    protected $hidden = ['created_at', 'updated_at'];
    // protected $dates = [];
    //protected $appends = ['id'];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function service()
    {
        return new UserService($this);
    }

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

    public function getExpAttribute($value){
       
        if(!$value){
            return 0;
        }
        return $value;
    }
    public function getSoftAttribute($value){
       
        if(!$value){
            return 0;
        }
        return $value;
    }
    public function getHardAttribute($value){
       
        if(!$value){
            return 0;
        }
        return $value;
    }

    public function getCurrentStreakVoteAttribute($value){
        if(!$value){
            return 0;
        }
        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */


}
