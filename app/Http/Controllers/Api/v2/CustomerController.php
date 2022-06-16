<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function show($id)
    {
        return new CustomerResource(Customer::find($id));
    }
}
