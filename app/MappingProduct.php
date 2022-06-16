<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MappingProduct extends Model
{
    protected $table = "mapping_product";

    public function product(){
    	return $this->belongsTo(Product::class);
    }

    public function distributor(){
    	return $this->belongsTo(Distributor::class);
    }

    public function productstock(){
        return $this->hasOne('App\ProductStock','product_id');
    }
}