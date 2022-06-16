@if(count($products) > 0)
    <div class="col-sm-12">
       
 
        <table class="table table-bordered">
    		<thead>
    			<tr>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('S.No')}}</label>
                    </td>
    				<td class="" width="30%">
    					<label for="" class="control-label">{{translate('Product')}}</label>
    				</td>
                    @if($peer_code != null)
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Peer Discount Price')}}</label>
                    </td>
                    @endif
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Quantity')}}</label>
                    </td>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('MRP')}}</label>
                    </td>
                    <td class="">
    					<label for="" class="control-label">{{translate('image')}}</label>
    				</td>
    				
                    <td class="text-center">
                        <label for="" class="control-label">{{translate('Action')}}</label>
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

                 if($peer_code != null){
                     $peerPrice =  App\PeerSetting::whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghub_id . '"]\')')->where('product_id', '"'.$product->id.'"')->first();
                     
                        if(!empty($peerPrice)){
                            $discounted_price = $variant_price;
                            if($peerPrice->discount == '"percent"'){
                                $customer_discount = preg_replace("/[^\d]/", "", $peerPrice->customer_discount);
                                $discounted_price = $variant_price - ($variant_price*$customer_discount/100);

                            }elseif($peerPrice->discount == "flat"){
                                $customer_discount = preg_replace("/[^\d]/", "", $peerPrice->customer_discount);
                                $discounted_price = $variant_price - $customer_discount;

                            }
                            
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
                            @if($peer_code != null)
                            <td>
                                 {{$discounted_price}}
                            </td>
                            @endif
                            <td width="15%">
                                <input type="quantity" width="15%" id = "qty_{{$product->id}}" max="5" min="1" value = "1">
                            </td>
                            <td>
                                {{$variant_price}}
                            </td>     
                            <td>
                                <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image">
                            </td>
                            {{-- @php
                                if($peer_code != null){
                                $variant_price = $discounted_price;
                                }  
                            @endphp --}}
                           
                			<td align="center">
                				<input type="checkbox" class="form-control selectedId" name="products[]" value="{{$product->id}}" price = {{$variant_price}} style="width: 16px;">
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
