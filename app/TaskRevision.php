<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskRevision extends Model
{
    //
    protected $table = 'revisions_of_tasks';

    protected $guarded = ['id'];

    public $timestamps = false;
}
