<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\CurrencyCollection;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return new CurrencyCollection(Currency::all());
    }
}
