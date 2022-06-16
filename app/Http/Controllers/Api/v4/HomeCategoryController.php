<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\HomeCategoryCollection;
use App\Models\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        return new HomeCategoryCollection(HomeCategory::all());
    }
}
