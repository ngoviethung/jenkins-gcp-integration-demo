<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;
use Request;
use URL;
use DB;

class Pattern extends Model
{
    use CrudTrait;
    use RevisionableTrait;
    use ModelTrait;
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

//    protected $connection= 'mysql2';

    protected $table = 'patterns';
    protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['name'];
    protected $hidden = [];
    // protected $dates = [];
    protected $appends = [];




}
