<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class EloConvert extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'elo_converts';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
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

    public function topic() {
        return $this->belongsTo(Topic::class);
    }

    public function type() {
        return $this->belongsTo(Type::class);
    }

    public function publish() {
        $items = $this->topic->items()->where('type_id', '=', $this->type_id)->get();

        $result = [];
        // (x - x1) / (x2 - x1) = (y - y1) / (y2 - y1)
        // y = (x - x1) x (y2 - y1) / (x2 - x1) + y1

        // $point = ($item->elo_score - $this->min_elo_score) * ($this->max_score - $this->min_score) / ($this->max_elo_score - $this->min_elo_score) + $this->min_score;

        //Dep Id =1
        foreach ($items as $item) {
            $point = ($item->elo_score - $this->min_elo_score) * ($this->max_score - $this->min_score) / ($this->max_elo_score - $this->min_elo_score) + $this->min_score;
            $result[$item->id] = [
                'score' => round($point, 2),
                'old_score' => $item->styles()->where('style_id', '=', 1)->first()->pivot->score,
                'elo_score' => $item->elo_score,
            ];

            $item->styles()->updateExistingPivot(1, ['score' => round($point, 2)]);
        }
        return json_encode($result);
    }
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
