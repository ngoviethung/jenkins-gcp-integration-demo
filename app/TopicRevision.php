<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopicRevision extends Model
{
    //
    protected $table = 'revisions_of_topics';

    protected $guarded = ['id'];

    public $timestamps = false;
}
