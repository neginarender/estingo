<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\HomeCategoryCollection;
use App\Models\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        return new HomeCategoryCollection(HomeCategory::all());
    }
}
