<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function show($id)
    {
        return new CustomerResource(Customer::find($id));
    }
}
