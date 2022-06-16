<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderReferalCommision extends Model
{
    protected $table = "order_referal_commision";

      public function user(){
    	return $this->belongsTo(User::class, 'partner_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
