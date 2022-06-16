<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReplacementOrder extends Model
{
    //
    protected $table = "replacement_orders";
    protected $guarded = [];

    public function order(){
        return $this->hasOne('App\Order','id','order_id');
    }

    public function order_detail(){
        return $this->hasOne('App\OrderDetail','id','order_detail_id');
    }
}
