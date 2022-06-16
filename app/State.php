<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
  protected $table = 'states';

 /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
 protected $fillable = [];

 	public function city(){
      return $this->hasMany(city::class, 'state_id');
  	}	
}
