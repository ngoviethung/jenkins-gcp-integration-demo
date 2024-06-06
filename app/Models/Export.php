<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use URL;
use Carbon\Carbon;

class Export extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'exports';
    // protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getDownloadLink(){

        if($this->file != ''){
            return '<a href="'. URL::to("$this->file") .'">Download</a>';
        }else{
            return 'Creating file...';
        }

    }

    public function getCompletionTime(){
        $created_at = new Carbon($this->created_at);
        $updated_at = new Carbon($this->updated_at);

        $diff = $created_at->diffInSeconds($updated_at);

        if($diff < 60){
            return $diff.'s';
        }
        $minutes = floor($diff / 60);
        $secodes = $diff % 60;

        return $minutes.'ph '. $secodes.'s ';
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
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

}
