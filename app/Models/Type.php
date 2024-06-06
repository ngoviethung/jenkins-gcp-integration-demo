<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class Type extends Model
{
    use CrudTrait, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'types';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['name', 'icon', 'icon_selected', 'category', 'order', 'order_num', 'pos_x', 'pos_y', 'vip'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*position
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function parent() {
        return $this->belongsTo(Type::class, 'parent_id');
    }
    public function children() {
        return $this->hasMany(Type::class, 'parent_id');
    }

    public function position() {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function items() {
        return $this->hasMany(Item::class, 'type_id');
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
//    public function getOrderAttribute(){
//        $position = Position::find($this->position_id);
//        return $position->order ?? 0;
//    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function groupleveltype() {
        return $this->belongsTo(GroupLevelType::class,'group_level_type_id');
    }


}
