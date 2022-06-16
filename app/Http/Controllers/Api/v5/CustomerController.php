<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function show($id)
    {
        return new CustomerResource(Customer::find($id));
    }

    public function detail($id){
        dd($request->all());
    }
}
