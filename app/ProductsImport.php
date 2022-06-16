<?php

namespace App;

use App\Product;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Auth;
Use DB;
Use App\ProductStock;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        
       // dd($row);
      // [{"attribute_id":"3","values":["1.5 Kg"]}]

      $choice_options = array();
        if(!empty($row['choice_no'])){
       
                $str = $row['value'];
                $item['attribute_id'] = $row['choice_no'];
                $item['values'] = explode(',', implode('|', [$str]));
                array_push($choice_options, $item);            
        }
        // dd($choice_options);
        if (!empty($row['choice_no'])) {
            $product_attributes = json_encode($row['choice_no']);
            $a = '["';
            $c = '"]';
            $product_attributes = $a.$product_attributes.$c;
        } else {
            $product_attributes = json_encode(array());
        }
        
        // dd();
        // $product->choice_options = json_encode($choice_options);

       


        $products = Product::create([
           'name'     => $row['name'],
           'added_by'    => Auth::user()->user_type == 'seller' ? 'seller' : 'admin',
           'user_id'    => Auth::user()->user_type == 'seller' ? Auth::user()->id : User::where('user_type', 'admin')->first()->id,
           'category_id'    => $row['category_id'],
           'subcategory_id'    => $row['subcategory_id'],
           'subsubcategory_id'    => $row['subsubcategory_id'],
           'brand_id'    => $row['brand_id'],
           //'min_qty'     => $row['min_qty'],
           'tags'        => $row['tags'],
           'hsn_code'    => 1234,
           'tax'         => $row['tax'],
           'tax_type' => $row['tax_type'],
           'description' => $row['description'],
           'video_provider'    => $row['video_provider'],
           // 'shipping_type' => $row['shipping_type'],
           // 'shipping_cost' => $row['shipping_cost'],
           'video_link'    => $row['video_link'],
           'unit_price'    => $row['unit_price'],
           'purchase_price'    => $row['purchase_price'],
           'variant_product'    => 1, 
           'unit'    => $row['unit'],
           'current_stock' => $row['current_stock'],
           'meta_title' => $row['meta_title'],
           'meta_description' => $row['meta_description'],

           'photos'         => $row['photos'],
           'thumbnail_img' => $row['thumbnail_img'],
           'meta_img' => $row['meta_img'],
           'json_tags' => $row['json_tags'],

           'colors' => json_encode(array()),
           'attributes' => $product_attributes,
           'choice_options' => json_encode($choice_options),
           'variations' => json_encode(array()),
           'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $row['name'])).'-'.Str::random(5),
        ]);


        

 // dd($products->id);

          $product_sku_name = ProductStock::orderBy('id', 'desc')->select('sku')->first();
          $last_sku = $product_sku_name->sku;
          $split_sku = explode("ROZ",$last_sku);
          $new_sku = $split_sku[1] + 1;
          $sku = 'ROZ'.$new_sku;

           $productid = Product::orderBy('id', 'desc')->select('id')->first();
          // dd($productid->id);
           $product_stock = new ProductStock;
           $product_stock->product_id     = $productid->id;
           $product_stock->variant     = $row['variant'];
           $product_stock->sku    = $sku;
           $product_stock->price    = $row['mrp'];
           $product_stock->discount    = 0.00;
           $product_stock->discount_type    = $row['tax_type'];
           $product_stock->qty    = $row['qty'];
           $product_stock->save();
          

         return $products;


    }

    public function rules(): array
    {
        return [
             // Can also use callback validation rules
             'unit_price' => function($attribute, $value, $onFailure) {
                  if (!is_numeric($value)) {
                       $onFailure('Unit price is not numeric');
                  }
              }
        ];
    }
}
