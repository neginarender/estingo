<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArchivedOrder extends Model
{
    //
    protected $table = "archive_orders";

    public function orderDetails()
    {
        return $this->hasMany(ArchivedOrderDetail::class,'order_id');
    }
}
