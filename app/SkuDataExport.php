<?php

namespace App;

use App\Product;
use App\ProductStock;
use App\Seller;
use App\User;
Use App\ShortingHub;
use App\Order;
use App\PeerPartner;
use App\OrderReferalCommision;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class SkuDataExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{	
	public function __construct($sortinghubid = NULL,$searchs = NUll)
    {
        $this->sorting_hub_id = $sortinghubid;
        $this->searchs = $searchs;
    }
    public function collection()
    {
        $searchs = $this->searchs;
    	if(isset($this->sorting_hub_id)){
                $sorting_hub_id = $this->sorting_hub_id;

                $products = DB::table('products')
                    ->orderBy('product_stocks.sku', 'ASC')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->join('sub_categories', 'products.subcategory_id', '=', 'sub_categories.id')
                    ->join('sub_sub_categories', 'products.subsubcategory_id', '=', 'sub_sub_categories.id')
                     ->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                    ->where('mapping_product.sorting_hub_id', $sorting_hub_id) 
                    ->select('products.id','products.name', 'product_stocks.sku','brands.name as brand_name','categories.name as category_name','sub_categories.name as subcategory_name','sub_sub_categories.name as subsubcategory_name','products.unit_price','products.purchase_price','products.unit','products.hsn_code','products.tax','products.tax_type','products.published','products.choice_options','products.attributes','product_stocks.price as stock_price','mapping_product.sorting_hub_id','mapping_product.selling_price','mapping_product.created_at','mapping_product.purchased_price');

            }else{
                $products = DB::table('products')
                    ->orderBy('product_stocks.sku', 'ASC')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->join('sub_categories', 'products.subcategory_id', '=', 'sub_categories.id')
                    ->join('sub_sub_categories', 'products.subsubcategory_id', '=', 'sub_sub_categories.id')
                     ->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                    ->select('products.id','products.name', 'product_stocks.sku','brands.name as brand_name','categories.name as category_name','sub_categories.name as subcategory_name','sub_sub_categories.name as subsubcategory_name','products.unit_price','products.purchase_price','products.unit','products.hsn_code','products.tax','products.tax_type','products.published','products.choice_options','products.attributes','product_stocks.price as stock_price','mapping_product.sorting_hub_id','mapping_product.selling_price','mapping_product.created_at','mapping_product.purchased_price');


            }
                if($searchs!=''){
                    $searchs = $this->searchs;
                    $products = $products->where('product_stocks.sku', 'like', '%'.$searchs.'%');
                }
                $products = $products->get();

		return $products;

    }

    public function headings(): array
    {
			
        return [
            'Product Name',
            'SKU',
            'Listing Date',
            'Sorting Hub',
            'Brand',
            'Category',
            'SubCategory',
            'SubSubCategory',
            // 'MRP',
            'Purchased Price',
            'MRP',
            'Weight',
            'Weight unit',
            'HSN Code',
            'GST%',
            'Published/Unpublished'
        ];
    }

    /**
    * @var Order $order
    */
    public function map($products): array
    {
// dd($products);
		$listing_date = date('d-m-Y',strtotime($products->created_at));
		$sortingHub = User::where('id',$products->sorting_hub_id)->first()->name;
		$stock_price = '₹ '.$products->stock_price;
		if($products->purchased_price == 0){
			$purchase_price =  '₹ '.$products->purchase_price;
		}else{
			$purchase_price =  '₹ '.$products->purchased_price;
		}

		if($products->selling_price == 0){
			$selling_price = '₹ '.$products->stock_price;
		}else{
			$selling_price = '₹ '.$products->selling_price;
		}

		$weight = "";
		if (@$products->choice_options != null){
			foreach (json_decode($products->choice_options) as $key => $choice){
				foreach ($choice->values as $key => $value){
					$weight .= $value;
				}
			}
											
			$weight .= '/';								
		}
																				
		if ($products->choice_options != null){
			$arr = array();
			foreach (json_decode($products->choice_options) as $key => $choice){
				$name = Attribute::where('id',$choice->attribute_id)->first()->name;
				array_push($arr,$name);
			}
			$weightUnit =  implode(' /',$arr);
		}							
									
		if($products->tax_type == 'percent'){
		 	$tax = $products->tax.' %';
		}else{
			$tax = '₹ '.$products->tax;
		}

		if($products->published == '1'){ $published = 'Published';}else{ $published = 'Unpublished';}
		return [
            $products->name,
            $products->sku,
            $listing_date,
            $sortingHub,
            $products->brand_name,
            $products->category_name,
            $products->subcategory_name,
            $products->subsubcategory_name,
            // $stock_price,
            $purchase_price,
            $selling_price,
            $value,
            $weightUnit,
            $products->hsn_code,
            $tax,
            $published
        ]; 
    }
}
