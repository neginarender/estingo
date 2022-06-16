<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    public function user(){
	  return $this->belongsTo(User::class);
	}

	public function region(){
	  return $this->belongsTo(Region::class);
	}

	public function state(){
	  return $this->belongsTo(State::class);
	}

	public function sortinghubs(){
	  return $this->hasMany(ShortingHub::class, 'cluster_hub_id', 'user_id');
	}
}
