<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\BusinessSettingCollection;
use App\Models\BusinessSetting;

class BusinessSettingController extends Controller
{
    public function index()
    {
        return new BusinessSettingCollection(BusinessSetting::all());
    }
}
