<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\BusinessSettingCollection;
use App\Models\BusinessSetting;

class BusinessSettingController extends Controller
{
    public function index()
    {
        return new BusinessSettingCollection(BusinessSetting::all());
    }
}
