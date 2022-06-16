<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\BrandCollection;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        return new BrandCollection(Brand::all());
    }

    public function top()
    {
        return new BrandCollection(Brand::where('top', 1)->get());
    }
}
