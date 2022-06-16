<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FinalOrder extends Model
{
    protected $fillable = [
        'order_id',
        'order_code', 
        'no_of_items',
        'user_id',
        'guest_id',
        'shipping_address',
        'pincode',
        'sortinghub_id',
        'grand_total',
        'delivery_status',
        'order_status',
        'payment_method',
        'payment_status',
        'total_discount',
        'delivery_type', 
        'order_date', 
        'referal_code',
        'phone',
        'customer_name',
        'platform'];
	
}