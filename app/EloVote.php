<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EloVote extends Model
{
    //
    protected $table = 'elo_votes';

    protected $guarded = ['id'];

    public $timestamps = true;
}
