<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    //
    protected $table = 'receipts';

    public $timestamps = false;

    protected $guarded = ['id'];
}
