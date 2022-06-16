<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FinalProduct extends Model
{
	protected $fillable = [
        'name', 'product_id', 'category_id',
        'subcategory_id','subsubcategory_id',
        'stock_price','base_price','variant','tags',
        'json_tags','quantity','max_purchase_qty','discount_type',
    'discount_percentage','customer_off','thumbnail_image',
    'photos','sorting_hub_id','flash_deal',
    'top_product','published','choice_options','unit','sales','rating','links','slug'];

}