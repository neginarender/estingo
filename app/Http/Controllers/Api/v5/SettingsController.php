<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\SettingsCollection;
use App\Models\AppSettings;

class SettingsController extends Controller
{
    public function index()
    {
        return new SettingsCollection(AppSettings::all());
    }
}
