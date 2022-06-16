<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\SettingsCollection;
use App\Models\AppSettings;

class SettingsController extends Controller
{
    public function index()
    {
        return new SettingsCollection(AppSettings::all());
    }
}
