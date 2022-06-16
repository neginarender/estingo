<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\HomeCategoryCollection;
use App\Models\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        return new HomeCategoryCollection(HomeCategory::all());
    }
}
