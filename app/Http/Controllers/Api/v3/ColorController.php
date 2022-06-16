<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\ColorCollection;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return new ColorCollection(Color::all());
    }
}
