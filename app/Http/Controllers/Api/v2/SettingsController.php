<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\SettingsCollection;
use App\Models\AppSettings;

class SettingsController extends Controller
{
    public function index()
    {
        return new SettingsCollection(AppSettings::all());
    }
}
