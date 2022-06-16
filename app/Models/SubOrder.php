<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    protected $table = "sub_orders";
    public $timestamps = false;

    protected $fillable = [
        'order_id','sub_order_code','delivery_name', 'delivery_type', 'delivery_date', 'delivery_time', 'delivery_status', 'payment_status', 'payment_mode', 'payment_response', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}

