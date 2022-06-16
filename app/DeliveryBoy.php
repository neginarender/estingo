<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    //
    protected $table = 'delivery_boy';
    protected $guarded = [];
    
    public function user(){
      return $this->belongsTo(User::class);
    }
    
    public function sorting_hub(){
        return $this->HasOne(User::class, 'id', 'sorting_hub_id');
      }
  
      public function cluster_hub(){
        return $this->HasOne(User::class, 'id', 'cluster_hub_id');
      }
}
