<?php

namespace App\Http\Controllers\Admin\Api;

use App\Models\Style;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StyleController extends Controller
{
    public function getStyles(){
        $styles = Style::all(['id','name']);
        return $styles;
    }
}
