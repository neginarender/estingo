<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\BusinessSettingCollection;
use App\Models\BusinessSetting;

class BusinessSettingController extends Controller
{
    public function index()
    {
        return new BusinessSettingCollection(BusinessSetting::all());
    }
}
