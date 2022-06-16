<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShortingHub extends Model
{
    public function user(){
	  return $this->belongsTo(User::class);
	}

	public function cluster_user(){
	  return $this->belongsTo(User::class,'cluster_hub_id','id');
	}

	public function cluster(){
	  return $this->hasOne(Cluster::class, 'user_id', 'cluster_hub_id');
	}

	public function distributor(){
		return $this->hasMany(Distributor::class, 'sorting_hub_id', 'user_id');
	}
	public function deliveryBoy(){
		return $this->hasMany(DeliveryBoy::class,'sorting_hub_id','user_id');
	}
}
