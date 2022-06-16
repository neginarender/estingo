<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\CurrencyCollection;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return new CurrencyCollection(Currency::all());
    }
}
