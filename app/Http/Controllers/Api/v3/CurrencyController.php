<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\CurrencyCollection;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return new CurrencyCollection(Currency::all());
    }
}
