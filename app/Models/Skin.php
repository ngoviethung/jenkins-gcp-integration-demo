<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ModelTrait;
class Skin extends Model
{
    use CrudTrait;
    use SoftDeletes;
    use ModelTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'skins';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

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
    | ACCESSORS
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setBodyImageAttribute($value)
    {
        $this->uploadImageToDisk($value, 'body_image', 'public','uploads/skin/image');
    }
    public function setLeftHandImageAttribute($value)
    {
        $this->uploadImageToDisk($value, 'left_hand_image', 'public','uploads/skin/image');
    }
    public function setRightHandImageAttribute($value)
    {
        $this->uploadImageToDisk($value, 'right_hand_image', 'public','uploads/skin/image');
    }
    public function setThumbnailAttribute($value)
    {
        $this->uploadImageToDisk($value, 'thumbnail', 'public','uploads/skin/thumb');
    }
}
