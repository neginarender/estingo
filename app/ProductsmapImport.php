<?php

namespace App;

use App\MappingProduct;
use App\Product;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Auth;
Use DB;
use App\PeerSetting;

class ProductsmapImport implements ToModel, WithHeadingRow, WithValidation
{
    
    public function model(array $row)
    {
     // dd($row);
        // if($row['published']=='Published'){
        //     $published = 1;
        // }else{
        //     $published = 0;
        // }

        $published = 0;
      MappingProduct::where('id', $row['mapping_id'])
       ->update([
           'sorting_hub_id' => $row['sorting_hub_id'],
           'purchased_price' => $row['purchased_price'],
           'selling_price' => $row['mrp'],
           'published' => $published,
           'qty' => $row['stock']
        ]);
        // return $mapproducts;
       $sortinghubid = $row['sorting_hub_id'];
       $product_s = Product::where('id', $row['product_id'])->first();

                $peer_discount_check = PeerSetting::where('product_id', '"'.$row['product_id'].'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghubid. '"]\')')->latest()->first(); 

                if(!empty($peer_discount_check)){

                    if($row['peerdiscount_status']==1){
                        $a = '"';
                        $b = '"';
                        $peers_percentage = $a.$row['peer_off_percentage'].$b;
                        $customer_percentage = $a.$row['customer_off_percentage'].$b;
                        $master_percentage = $a.$row['master_peer_off_percentage'].$b;

                        
                    }else{
                        $peers_percentage = $peer_discount_check['peer_discount'];
                        $customer_percentage = $peer_discount_check['customer_discount'];
                        $master_percentage = $peer_discount_check['company_margin'];
                    }
                    $data = PeerSetting::where('product_id', '"'.$row['product_id'].'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghubid. '"]\')')->latest('id')->first();

                        if($data != NULL){
                            DB::table('peer_settings')
                        ->where('id', $data->id)
                        ->update(['status' => 0]);
                        }
                    // echo $peers_percentage; die;
         
                    $unit_price = $row['purchased_price'];
                    $variant_price = $row['mrp'];

                    $customer_discount = substr($customer_percentage, 1, -1);
                    $customercheck_price = ($variant_price*$customer_discount)/100;
                    $finalamount = $variant_price - $customercheck_price;
                    $unitprice_tax = ($unit_price*$product_s->tax)/100;
                    $variantprice_tax = ($finalamount*$product_s->tax)/100;

                    if($variantprice_tax > $unitprice_tax){
                        $taxdifference = $variantprice_tax - $unitprice_tax;
                    }else{
                        $taxdifference = 0;
                    }

                    if($finalamount > $unit_price){
                        $difference = $finalamount - $unit_price;
                        $after_tax =  $difference - $taxdifference;
                    }else{
                        $after_tax = 0;
                    }

                    if($after_tax!=0){
                        $peer_discount = substr($peers_percentage, 1, -1);
                        $company_margin = substr($master_percentage, 1, -1);

                         $peer_commission = ($after_tax*$peer_discount)/100;
                         $master_commission = ($after_tax*$company_margin)/100; 
                             $last_margin = $peer_discount + $company_margin;
                             $margin = 100 - $last_margin; 
                         $rozana_commission = ($after_tax*$margin)/100;   
                    }else{
                        $peer_commission = 0;
                        $master_commission = 0;
                        $rozana_commission = 0;
                    }

                    // echo json_encode($sortinghubid);
                    // echo '<br>';
                    // die;
                   
                        $mapping_product = new PeerSetting;
                        $mapping_product->sorting_hub_id = $peer_discount_check['sorting_hub_id'];
                        $mapping_product->category_id = $peer_discount_check['category_id'];
                        $mapping_product->sub_category_id = $peer_discount_check['sub_category_id'];
                        $mapping_product->product_id = $peer_discount_check['product_id'];
                        $mapping_product->discount = $peer_discount_check['discount'];
                        $mapping_product->peer_discount = $peers_percentage;
                        $mapping_product->customer_discount = $customer_percentage;
                        $mapping_product->company_margin = $master_percentage;

                        $mapping_product->customer_off = $customercheck_price;
                        $mapping_product->peer_commission = $peer_commission;
                        $mapping_product->master_commission = $master_commission;
                        $mapping_product->rozana_margin = $rozana_commission;
                        $mapping_product->status = 1;
                        $mapping_product->save();
                }else{
                  // dd($row['product_id']);
                    $a = '"';
                    $b = '"';
                    $peers_percentage = $a.$row['peer_off_percentage'].$b;
                    $customer_percentage = $a.$row['customer_off_percentage'].$b;
                    $master_percentage = $a.$row['master_peer_off_percentage'].$b;

                    $unit_price = $row['purchased_price'];
                    $variant_price = $row['mrp'];

                    $customer_discount = substr($customer_percentage, 1, -1);
                    $customercheck_price = ($variant_price*$customer_discount)/100;
                    $finalamount = $variant_price - $customercheck_price;
                    $unitprice_tax = ($unit_price*$product_s->tax)/100;
                    $variantprice_tax = ($finalamount*$product_s->tax)/100;

                    if($variantprice_tax > $unitprice_tax){
                        $taxdifference = $variantprice_tax - $unitprice_tax;
                    }else{
                        $taxdifference = 0;
                    }

                    if($finalamount > $unit_price){
                        $difference = $finalamount - $unit_price;
                        $after_tax =  $difference - $taxdifference;
                    }else{
                        $after_tax = 0;
                    }

                    if($after_tax!=0){
                        $peer_discount = substr($peers_percentage, 1, -1);
                        $company_margin = substr($master_percentage, 1, -1);

                         $peer_commission = ($after_tax*$peer_discount)/100;
                         $master_commission = ($after_tax*$company_margin)/100; 
                             $last_margin = $peer_discount + $company_margin;
                             $margin = 100 - $last_margin; 
                         $rozana_commission = ($after_tax*$margin)/100;   
                    }else{
                        $peer_commission = 0;
                        $master_commission = 0;
                        $rozana_commission = 0;
                    }

                    // echo json_encode($sortinghubid);
                    // echo '<br>';
                    // die; $mapping_product = new PeerSetting;
                        $aa = '[';
                        $bb = '"';
                        $cc = '"';
                        $dd = ']';
                        $sortinghubids = $aa.$bb.$row['sorting_hub_id'].$cc.$dd;
                        $prod_id = $bb.$row['product_id'].$cc;

                   
                        $mapping_product = new PeerSetting;
                        $mapping_product->sorting_hub_id = $sortinghubids;
                        $mapping_product->category_id = json_encode(["$product_s->category_id"]);
                        $mapping_product->sub_category_id = json_encode(["$product_s->subcategory_id"]);
                        $mapping_product->product_id = $prod_id;
                        $mapping_product->discount = json_encode("percent");
                        $mapping_product->peer_discount = $peers_percentage;
                        $mapping_product->customer_discount = $customer_percentage;
                        $mapping_product->company_margin = $master_percentage;

                        $mapping_product->customer_off = $customercheck_price;
                        $mapping_product->peer_commission = $peer_commission;
                        $mapping_product->master_commission = $master_commission;
                        $mapping_product->rozana_margin = $rozana_commission;
                        $mapping_product->status = 1;
                        $mapping_product->save();
                }        

    }

    public function rules(): array
    {
        return [
             // Can also use callback validation rules
             'selling_price' => function($attribute, $value, $onFailure) {
                  if (!is_numeric($value)) {
                       $onFailure('Selling price is not numeric');
                  }
              }

        ];
    }
}
