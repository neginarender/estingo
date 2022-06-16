<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArchivedOrderDetail extends Model
{
    //

    protected $table = "archive_order_details";

    public function order()
    {
        return $this->belongsTo(ArchivedOrder::class,'order_id');
    }
}
