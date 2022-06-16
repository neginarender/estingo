<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\GeneralSettingCollection;
use App\Models\GeneralSetting;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return new GeneralSettingCollection(GeneralSetting::all());
    }
}
