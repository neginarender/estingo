<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletPayout extends Model
{
    protected $guarded = [];
    protected $table = "payout_requests";
    public function user(){
    	return $this->belongsTo(User::class);
    }
}
