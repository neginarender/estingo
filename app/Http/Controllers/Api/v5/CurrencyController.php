<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\CurrencyCollection;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return new CurrencyCollection(Currency::all());
    }
}
