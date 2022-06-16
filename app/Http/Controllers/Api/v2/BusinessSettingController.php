<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\BusinessSettingCollection;
use App\Models\BusinessSetting;

class BusinessSettingController extends Controller
{
    public function index()
    {
        return new BusinessSettingCollection(BusinessSetting::all());
    }
}
