<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\SettingsCollection;
use App\Models\AppSettings;

class SettingsController extends Controller
{
    public function index()
    {
        return new SettingsCollection(AppSettings::all());
    }
}
