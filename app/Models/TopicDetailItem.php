<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicDetailItem extends Model
{
    protected $table = 'topic_detail_items';
    protected $fillable = ['topic_detal_id', 'item_id'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
