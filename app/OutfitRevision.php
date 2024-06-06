<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutfitRevision extends Model
{
    //
    protected $table = 'revisions_of_outfits';

    protected $guarded = ['id'];

    public $timestamps = false;
}
