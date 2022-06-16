<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\ColorCollection;
use App\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return new ColorCollection(Color::all());
    }
}
