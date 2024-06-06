<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicDetail extends Model
{
    protected $table = 'topic_details';
    protected $fillable = ['topic_id', 'type_id'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function topicDetailItems()
    {
        return $this->hasMany(TopicDetailItem::class, 'topic_detail_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}
