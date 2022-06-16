<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\ColorCollection;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return new ColorCollection(Color::all());
    }
}
