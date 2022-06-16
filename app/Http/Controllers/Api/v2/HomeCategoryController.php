<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\HomeCategoryCollection;
use App\Models\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        return new HomeCategoryCollection(HomeCategory::all());
    }
}
