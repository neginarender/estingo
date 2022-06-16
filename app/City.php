<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
   	public function state(){
    	return $this->hasOne(State::class)->where('status', 1);
    }
}
