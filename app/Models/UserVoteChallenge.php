<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
#use MongoDB\Laravel\Eloquent\Model;
use App\Models\ModelTrait;
use Request;
use URL;
class UserVoteChallenge extends Model
{
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'user_vote_challenges';
    protected $primaryKey = '_id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['user_id', 'user_challenge_id', 'challenge_id'];
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



    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */


}
