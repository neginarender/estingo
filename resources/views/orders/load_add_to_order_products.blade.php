@if(count($product_ids) > 0)
    <!-- <label class="col-sm-3 control-label">{{translate('Discounts')}}</label> -->
    <div class="col-sm-12">
        <table class="table table-bordered">
    		<thead>
    			<tr>
    				<td class="text-center" width="40%">
    					<label for="" class="control-label">{{translate('Product')}}</label>
    				</td>
                    <td class="text-center">
    					<label for="" class="control-label">{{translate('Price')}}</label>
    				</td>
    				<td class="text-center">
    					<label for="" class="control-label">{{translate('Discount')}}</label>
    				</td>
                    <td>
                        <label for="" class="control-label">{{translate('Tax')}}</label>
                    </td>
                    <td>
                        <label for="" class="control-label">{{translate('Quantity')}}</label>
                    </td>
    			</tr>
    		</thead>
    		<tbody>
                @php
                $total_amount = 0;
                $total_mrp = 0;
                $total_tax = 0;
                $total_discount = 0;
                $discount = 0;
                $peer_commission = 0;
                $master_commission = 0;
                $total_peer_commission = 0;
                $total_master_commission = 0;
                $total_customer_discount_percent = 0;
                $total_peer_percent = 0;
                $total_master_percent = 0;
                @endphp
                @foreach ($product_ids as $key => $id)
                	@php
                        $qty = 1;
                        if(count($quantity)>0){
                            $qty = $quantity[$key];
                        }

                		$product = \App\Product::findOrFail($id);

                        if(!empty($shortId)){
                            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
                        }else{
                            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
                        }   

                        if(!is_null($peer_discount_check) && $order->referal_discount!=0){
                            $customer_off = $peer_discount_check->customer_off;
                            $peer_commission = $peer_discount_check->peer_commission;
                            $master_commission = $peer_discount_check->master_commission;
                            if($self == 1){
                                $customer_off = ($customer_off+$peer_commission+$master_commission);
                                $master_commission = 0;
                                $peer_commission = 0;
                            }else{
                                $peer_commission = ($peer_discount_check->peer_commission*$qty);
                                $master_commission = ($peer_discount_check->master_commission*$qty);
                            }
                            $discount = $customer_off*$qty;
                            $total_discount += $discount;
                            

                            $total_peer_commission += $peer_commission;
                            $total_master_commission += $master_commission;

                            $total_customer_discount_percent += substr($peer_discount_check['customer_discount'],1,-1);
                            $total_peer_percent += substr($peer_discount_check['peer_discount'],1,-1);
                            $total_master_percent += substr($peer_discount_check['company_margin'],1,-1);

                        }
                        
                        $price = price($product->id,$shortId);
                        
                        $tax = round((($price-$discount)*100)/(100+$product->tax));
                        $total_mrp +=$price*$qty;
                        $total_tax += $tax*$product->tax/100;
                        
                    $productStock = \App\ProductStock::where('product_id',$id)->first();
                    $variant = "";
                    if(!is_null($productStock)){
                        $variant = $productStock->variant;
                    }
                	@endphp
                		<tr>
                			<td>
                                <div class="col-sm-3">
                                <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image">
                                </div>
                                <div class="col-sm-9">
                				<label for="" class="control-label" style="float:right;">{{ __($product->name) }}</label>
                                <br />
                                <strong style="text-align:center;float:right;">{{ $variant }}</strong>
                                <input type="hidden" name="variant[]" value="{{ $variant }}" />
                                </div>
                			</td>
                            <td>
                				<label for="" class="control-label">{{ single_price($price) }}</label>

                                <input type="hidden" name="price[]" value="{{ $price }}" />
                			</td>
                			<td>
                                <label for="" class="control-label">
                                    @if($order->referal_discount!=0)

                                        {{ single_price($customer_off) }}

                                    @else
                                        0
                                    @endif
                                    <input type="text" name="discount[]" value="{{ $discount }}" readonly/>
                                    <input type="hidden" name="peer_commission[]" value="{{ $peer_commission}}" readonly/>
                                    <input type="hidden" name="master_commission[]" value="{{ $master_commission}}" readonly/>
                                </label>
                				<!-- <input type="number" name="discount_{{ $id }}" value="{{ $product->discount }}" min="0" step="1" class="form-control" required> -->
                			</td>
                            <td>
                            {{ single_price($total_tax) }}&nbsp;&nbsp;<strong>({{ $product->tax }}%)</strong>
                            <input type="hidden" name="tax[]" value="{{ $total_tax }}" />
                            </td>
                            <td><input type="number" name="qty[]" onchange='update_quantity()' value="{{ $qty }}" class="form-control qty" style="width:50px;" /></td>
                		</tr>
                        
                @endforeach
                <tr>
                    <td colspan="4" style="text-align:right;">
                    <p>MRP</p>
                    <p>Discount</p>
                    <p>Tax</p>
                    <p><strong>Total Amount</strong></p>
                   
                    </td>
                    <td>
                        <p>{{ single_price($total_mrp) }}</p>
                        <p>{{ single_price($total_discount) }}</p>
                        <p>{{ single_price($total_tax) }}</p>
                        @php
                        $total_amount = $total_mrp-$total_discount; 
                        @endphp
                        <p><strong>{{ single_price($total_amount) }}</strong></p>
                        <input type="hidden" name="total_amount" value="{{$total_amount}}" />
                        <input type="hidden" name="total_discount" value="{{$total_discount}}" />
                        <input type="hidden" name="total_peer_commission" value="{{ $total_peer_commission }}" />
                        <input type="hidden" name="total_master_commission" value="{{ $total_master_commission }}" />
                        <input type="hidden" name="total_customer_discount_percent" value="{{ $total_customer_discount_percent }}" />
                        <input type="hidden" name="total_peer_percent" value="{{ $total_peer_percent }}" />
                        <input type="hidden" name="total_master_percent" value="{{ $total_master_percent }}" />
                        <p style="font-size:10px;">Inclusive of all taxes</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
