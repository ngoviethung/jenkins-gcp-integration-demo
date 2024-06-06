<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Mongodb\Eloquent\Model;
#use MongoDB\Laravel\Eloquent\Model;
use App\Models\ModelTrait;
use Request;
use URL;

class UserReward extends Model
{
    use ModelTrait;
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $connection = 'mongodb';
    protected $table = 'user_rewards';
    protected $primaryKey = '_id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = [
        'user_id', 'type', 'reward', 'challenge_id', 'level'
    ];
    protected $hidden = ['created_at', 'updated_at'];
    // protected $dates = [];
    protected $appends = [];

}
