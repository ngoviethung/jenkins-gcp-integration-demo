<?php

namespace App\Models;

use App\TaskRevision;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Task extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'tasks';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['name', 'description', 'cover', 'background', 'require_topic','weight','min_score','reward_coin', 'currency', 'in_local'];
    protected $fakeColumns = ['name', 'description'];
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
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'require_topic', 'id');
    }

    public function styles()
    {
        return $this->belongsToMany(Style::class)->withPivot('style_id', 'factor');
    }

    public function types()
    {
        return $this->belongsToMany(Type::class)->withPivot('type_id', 'factor');
    }

    public function groupleveltask() {
        return $this->belongsTo(GroupLevelTask::class,'group_level_task_id');
    }

    public function category() {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public static function getLastVersion() {
        return TaskRevision::all()->sortByDesc('id')->first();
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
    public function getListStylesAttribute(){
        $styles = $this->styles;
        $html = '';
        foreach ($styles as $style) {
            $html .= "{$style->name}: {$style->pivot->factor} <br/>";
        }
        return $html;
    }
    public function getListTypesAttribute(){
        $types = $this->types;
        $html = '';

        foreach ($types as $type) {
//            $html .= "{$type->name}: {$type->pivot->factor} <br/>";
            $html .= "{$type->name} <br/>";
        }
        return $html;
    }
    public function getPriceCurrencyAttribute()
    {

        if($this->currency == 1){
            $currency = "Soft";
        }elseif($this->currency == 2){
            $currency = "Hard";
        }else{
            $currency = '';
        }
        return $currency;

    }
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
