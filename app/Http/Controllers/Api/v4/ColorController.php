<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\ColorCollection;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return new ColorCollection(Color::all());
    }
}
