<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliverySlot extends Model
{
    //
    protected $table = 'delivery_slot';
    protected $guarded = [];
    protected $fillable = ["*"];


    public function shortingHub(){
        return $this->belongsTo(ShortingHub::class);
    }

}

