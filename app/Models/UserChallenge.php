<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
#use MongoDB\Laravel\Eloquent\Model;
use App\Models\ModelTrait;
use Request;
use URL;
class UserChallenge extends Model
{
    use CrudTrait;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'user_challenges';
    protected $primaryKey = '_id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['user_id', 'challenge_id', 'claimed', 'vote', 'take_vote', 'input_data', 'score_unworn'];
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
    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id', '_id');
    }

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
