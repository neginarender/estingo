<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
 	public function sorting_hub(){
	  return $this->HasOne(User::class, 'id', 'sorting_hub_id');
	}

	public function cluster_hub(){
	  return $this->HasOne(User::class, 'id', 'cluster_hub_id');
	}
}
