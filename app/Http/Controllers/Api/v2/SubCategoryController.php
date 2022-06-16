<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\SubCategoryCollection;
use App\Models\SubCategory;
// use App\Traits\LocationTrait;

class SubCategoryController extends Controller
{
    // use LocationTrait;
    public function index($id)
    {
        // $location = $this->location();
        // $subCategoryIds = $location->subCategoryIds;
        // return new SubCategoryCollection(SubCategory::where('category_id', $id)->whereIn('id', $subCategoryIds)->get());

        return new SubCategoryCollection(SubCategory::where('category_id', $id)->get());
    }
}
