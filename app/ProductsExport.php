<?php

namespace App;

use App\Product;
use App\ProductStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ProductsExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        /*return Product::all();*/
		
		$product = Product::leftJoin('product_stocks', function($join) {
		  $join->on('products.id', '=', 'product_stocks.product_id');
		})->select('products.*','product_stocks.price as selling_price','product_stocks.sku')
		->get();

		return $product;

    }

    public function headings(): array
    {
			
        return [
            'id',
            'name',
            'sku',
            'added_by',
            'user_id',
            'category_id',
            'subcategory_id',
            'subsubcategory_id',
            'brand_id',
            'video_provider',
            'video_link',
            'unit_price',
            'purchase_price',
			'mrp',
			'value', 
            'unit',
            'tax',
            'tax_type',
            'current_stock',
            'meta_title',
            'meta_description',
        ];
    }

    /**
    * @var Product $product
    */
    public function map($product): array
    {
		$final = array();
		$prod = array();
		$data = json_decode($product->choice_options);
		foreach($data as $key => $choice){
            foreach ($choice->values as $key => $value){
					$value_data =   $value;
					array_push($final,$product->id,
						$product->name,
                        $product->sku,
						$product->added_by,
						$product->user_id,
						$product->category_id,
						$product->subcategory_id,
						$product->subsubcategory_id,
						$product->brand_id,
						$product->video_provider,
						$product->video_link,
						$product->unit_price,
						$product->purchase_price,
						$product->selling_price,
						$value_data,
						$product->unit,
						$product->tax,
						$product->tax_type,
						$product->current_stock);
	   
			}
		}

		return $final;
        /* return [
            $product->id,
            $product->name,
            $product->added_by,
            $product->user_id,
            $product->category_id,
            $product->subcategory_id,
            $product->subsubcategory_id,
            $product->brand_id,
            $product->video_provider,
            $product->video_link,
            $product->unit_price,
            $product->purchase_price,
			$product->selling_price,
			$value_data,
            $product->unit,
            $product->tax,
            $product->tax_type,
            $product->current_stock,
        ]; */
    }
}
