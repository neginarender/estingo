@if(count($products) > 0)
    <div class="col-sm-12">
        <input type="checkbox" id="selectall">Select all</input>
 
        <table class="table table-bordered">
    		<thead>
    			<tr>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('S.No')}}</label>
                    </td>
    				<td class="" width="30%">
    					<label for="" class="control-label">{{translate('Product')}}</label>
    				</td>
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Buying Price')}}</label>
                    </td>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('MRP')}}</label>
                    </td>
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Customer Discount')}}</label>
                    </td>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('Peer Commission')}}</label>
                    </td>                    
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Master Commission')}}</label>
                    </td>
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Rozana Margin')}}</label>
                    </td>
                    <td class="">
    					<label for="" class="control-label">{{translate('image')}}</label>
    				</td>
    				
                    <td class="text-center">
                        <label for="" class="control-label">{{translate('Action')}}</label>
                    </td>

                    <td class="text-center">
                        <label for="" class="control-label">{{translate('Flash Deal')}}</label>
                    </td>
    			</tr>
    		</thead>
    		<tbody>
                @foreach ($products as $key => $product)
                @php
                 $variant_prices = App\ProductStock::where('product_id', $product->id)->latest('price')->first();

                 if(!empty($sortinghub_id)){
                 
                    $products =  App\MappingProduct::where(['sorting_hub_id'=>$sortinghub_id,'product_id'=>$product->id])->latest()->first();
                    $unit_price = $products['purchased_price'];
                    $variant_price = $products['selling_price'];
                    if($unit_price == 0 || $variant_price == 0){
                        $unit_price = $product->unit_price;
                        $variant_price = $variant_prices['price'];
                    } 
                 }else{
                        $unit_price = $product->unit_price;
                        $variant_price = $variant_prices['price'];
                 }

                 
                 $unitprice_tax = ($unit_price*100)/(100+$product->tax);
                 $unitprice_tax = ($unitprice_tax*$product->tax)/100;
                 $variant_price_tax = ($variant_price*100)/(100+$product->tax);
                 $variant_price_tax = ($variant_price_tax*$product->tax)/100;

                 $last_margin = $peer_map + $company_map;
                 $margin = 100 - $last_margin;


                 if($product->tax!=0){

                         if($customer_map!=0){
                            $customer_price = ($variant_price*$customer_map)/100;
                            if($customer_price!=0){
                                $price_after_discount = $variant_price - $customer_price;
                                $customer_price_tax =  ($price_after_discount*100)/(100+$product->tax);
                                $customer_price_tax = ($customer_price_tax*$product->tax)/100;
                                if($customer_price_tax > $unitprice_tax){
                                    $taxes = $customer_price_tax - $unitprice_tax;
                                    $difference = $price_after_discount - $taxes;
                                    if($customer_price_tax > $unitprice_tax){
                                        $main_price = $difference - $unit_price;
                                        $peer_price = round(($main_price*$peer_map)/100,2);  
                                        $master_price = round(($main_price*$company_map)/100,2);  
                                        $rozana_margin = round(($main_price*$margin)/100,2);
                                    }else{
                                        $main_price = 0;
                                        $peer_price = round(($main_price*$peer_map)/100,2);  
                                        $master_price = round(($main_price*$company_map)/100,2);  
                                        $rozana_margin = round(($main_price*$margin)/100,2);
                                    }
                                    
                                }else{
                                     $taxes = 0;
                                     $difference = $price_after_discount - $taxes;
                                     if($customer_price_tax > $unitprice_tax){
                                        $main_price = $difference - $unit_price;
                                    }else{
                                        $main_price = 0;
                                        $peer_price = 0;
                                        $master_price = 0;
                                        $rozana_margin = 0;
                                    }
                                }
                            
                            }
                         }else{
                            $customer_price = 0;
                            $price_difference = $variant_price - $unit_price;
                            $tax_difference = $variant_price_tax - $unitprice_tax;

                            $main_price = $price_difference - $tax_difference;
                            $peer_price = round(($main_price*$peer_map)/100,2);  
                            $master_price = round(($main_price*$company_map)/100,2);  
                            $rozana_margin = round(($main_price*$margin)/100,2);
                         }
                }else{
                      if($customer_map!=0){
                            $customer_price = ($variant_price*$customer_map)/100;
                            $price_after_discount = $variant_price - $customer_price;
                            if($price_after_discount > $unit_price){
                                $price_difference = $price_after_discount - $unit_price;
                                $main_price = $price_difference;
                                $peer_price = round(($main_price*$peer_map)/100,2);  
                                $master_price = round(($main_price*$company_map)/100,2);  
                                $rozana_margin = round(($main_price*$margin)/100,2);
                            }else{
                                $customer_price = ($variant_price*$customer_map)/100;
                                $peer_price = 0;
                                $master_price = 0;
                                $rozana_margin = 0;
                            }    
                      }else{
                            $customer_price = 0;
                            $price_difference = $variant_price - $unit_price;
                            $main_price = $price_difference;
                            $peer_price = round(($main_price*$peer_map)/100,2);  
                            $master_price = round(($main_price*$company_map)/100,2);  
                            $rozana_margin = round(($main_price*$margin)/100,2);
                      } 
                }

               
                 
                @endphp  
                		<tr>
                            <td>
                                <label for="" class="control-label">{{ $key+1 }}</label>
                            </td>
                			<td>
                				<label for="" class="control-label">{{$product->name. ' ('.@$product->stocks[0]->variant. ')'}}
                                    <span style="display: block; text-align: left;">Sku -{{@$product->stocks[0]->sku}}</span>
                                </label>
                			</td>
                            <td>
                                 {{$unit_price}}
                            </td>
                            <td>
                                {{$variant_price}}
                            </td> 
                            <td>
                                {{$customer_price}}
                            </td>
                            <td>
                                {{$peer_price}}
                            </td>
                            <td>
                                {{$master_price}}
                            </td>
                            <td>
                                {{$rozana_margin}}
                            </td>     
                            <td>
                                <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image">
                            </td>
                           
                			<td align="center">
                				<input type="checkbox" class="form-control selectedId" data-myval="{{$rozana_margin}}" id="myDiv" name="products[]" value="{{$product->id}}" style="width: 16px;">
                			</td>
                            <td align="center">
                                <input type="checkbox" class="form-control selectedDeal"  id="flash_deal" name="flash_deal[]" value="{{$product->id}}" style="width: 16px;">
                            </td>
                            
                		</tr>
                @endforeach
            </tbody>
        </table>
        
    </div>
@endif
<script type="text/javascript">
$(document).ready(function () {
    $('#selectall').click(function () {
        $('.selectedId').prop('checked', this.checked);
    });

    $('.selectedId').change(function () {
        var check = ($('.selectedId').filter(":checked").length == $('.selectedId').length);
        $('#selectall').prop("checked", check);
    });
});
</script>
