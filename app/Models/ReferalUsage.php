<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferalUsage extends Model
{
    protected $table = "referral_usages";
    protected $fillable = ['user_id','order_id','partner_id','referal_code','discount_rate','discount_amount','commision_rate'];
}
