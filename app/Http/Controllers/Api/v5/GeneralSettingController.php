<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\GeneralSettingCollection;
use App\Models\GeneralSetting;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return new GeneralSettingCollection(GeneralSetting::all());
    }
}
