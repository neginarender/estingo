<style type="text/css">
	.c_code{font-weight: 800;
                font-family: arial;
                color: #009848;
                border: dashed 1px #009848;
                display: inline-block;
                padding: 9px 25px; height: 40px;
                margin: 0 5px;
                 
                /* background: #841c1c; */
                border-radius: 4px;}
               @media (max-width: 640px){

               }
</style>
<div class="card sticky-top">
    <div class="card-title py-3">
        <div class="row align-items-center">
            <div class="col-6">
                <h3 class="heading heading-3 strong-400 mb-0">
                    <span>{{translate('Summary')}}</span>
                </h3>
            </div>

            <div class="col-6 text-right">
                <span class="badge badge-md badge-success">{{ count(Session::get('cart')) }} {{translate('Items')}}</span>
            </div>
        </div>
    </div>

    <div class="card-body">
        @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
            @php
                $total_point = 0;
            @endphp
            @foreach (Session::get('cart') as $key => $cartItem)
                @php
                    $product = \App\Product::find($cartItem['id']);
                    $total_point += $product->earn_point*$cartItem['quantity'];
                @endphp
            @endforeach
            <div class="club-point mb-3 bg-soft-base-1 border-light-base-1 border">
                {{ translate("Total Club point") }}:
                <span class="strong-700 float-right">{{ $total_point }}</span>
            </div>
        @endif
        <table class="table-cart table-cart-review">
            <thead>
                <tr>
                    <th class="product-name">{{translate('Product')}}</th>
                    <th class="product-total text-right">{{translate('Total')}}</th>
                </tr>
            </thead>
            <tbody>
                @php
                set_shipping(['shipping_type_admin'=>'home_delivery']);
                Session::put('total_saving',0);
                $self = 0;
                if(Auth::check()){
                    if(Auth::user()->user_type=="partner" && Auth::user()->peer_partner==1){
                        $self = 1;
                    }
                }
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $sub_price = 0;
                    $peer_disc_price = 0;
                    $ttl = 0;
                    $is_fresh = 0;
                    $is_grocery = 0;
                    $total = 0;
                    $min_order_amount = (int) env("MIN_ORDER_AMOUNT");
                    $free_shipping_amount = (int) env("FREE_SHIPPING_AMOUNT");

                    $shippingInfo = Session::get('shipping_info');
                    $productAvail = "";
                    $shortingHub = "";
                    $canProcess = array();
                    if(!empty($shippingInfo)){               
                    $shortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $shippingInfo['postal_code'] . '"]\')')->pluck('user_id')->first();
                    } 
                @endphp
                @foreach (Session::get('cart') as $key => $cartItem)
                    @php
                        if(Session::has('referal_discount')){
                            $referal_discount = ($cartItem['price']*$cartItem['quantity'] * Session::get('referal_discount')) / 100;
                            //$subtotal += $cartItem['price'] *$cartItem['quantity'] -  $referal_discount; 
                        }

                            
                            $subtotal += $cartItem['price']*$cartItem['quantity'];

                       
                        
                      
                        $product = \App\Product::find($cartItem['id']);
                        
                        if(!empty($shortingHub)){
                             $productAvail = \App\MappingProduct::where(['sorting_hub_id'=>$shortingHub,'product_id'=>$product->id,'published'=>1])->first();
                             if(empty($productAvail)){
                                array_push($canProcess,$product->id);   
                                }
                            }

                        //$tax += $cartItem['tax']*$cartItem['quantity'];
                        //$taxquantity = $product->tax*$cartItem['quantity'];
                       // $taxes += ($cartItem['price']*$taxquantity)/100;

                        $shipping += $cartItem['shipping'];

                        $product_name_with_choice = $product->name;
                        if ($cartItem['variant'] != null) {
                            $product_name_with_choice = $product->name.' - '.$cartItem['variant'];
                        }

                           
                    
                    if(Session::has('referal_discount')){
                            $id = $cartItem['id'];

                            $product = App\Product::findOrFail($id);
                            $price = $product->unit_price;
                            $productstock = App\ProductStock::where('product_id', $id)->select('price')->first();
                            $stock_price = $product->unit_price;
                            if(!is_null($productstock)){
                                $stock_price = $productstock->price; 
                            }
                            
                         
                            $shortId = "";
                            if(!empty(Cookie::get('pincode')))
                            { 
                                $pincode = Cookie::get('pincode');
                                $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
                                
                            }

                            if(!empty($shortId)){
                                $product_map = \App\MappingProduct::where(['sorting_hub_id'=>$shortId->sorting_hub_id,'product_id'=>$id])->first();
                                $price = $product_map['purchased_price'];
                                $stock_price = $product_map['selling_price'];

                                if($price == 0 || $stock_price == 0){
                                    $id = $cartItem['id'];
                                    $product = App\Product::findOrFail($id);
                                    $price = $product->unit_price;
                                    $productstock = App\ProductStock::where('product_id', $id)->select('price')->first();
                                    $stock_price = $product->unit_price;
                                    if(!is_null($productstock)){
                                        $stock_price = $productstock->price; 
                                    }
                                }  

                            }
                          
                           //$main_discount = $stock_price - $price;

                           $peer_discount_check = App\PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
                            $prices = $stock_price;
                            $last_price = 0;
                            if(!empty($peer_discount_check)){
                                $customer_off =  $peer_discount_check->customer_off;
                           $customer_discount = $peer_discount_check->customer_discount;
                           $discount_type = $peer_discount_check->discount; 
                           $peer_commission = $peer_discount_check->peer_commission;
                           $master_commission = $peer_discount_check->master_commission;

                           $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);
                           //$last_price = ($stock_price * $discount_percent)/100; 
                           if($self==1){
                            
                            $prices = $stock_price -($peer_commission+$master_commission+$peer_discount_check->customer_off);
                            $last_price =  $peer_commission+$master_commission+$peer_discount_check->customer_off;
                           }else{
                            $last_price = $peer_discount_check['customer_off'];
                            $prices = $stock_price - $last_price;
                           }
                            }
                           
                          
                           

                           $sub_price += $prices*$cartItem['quantity'];
                           $peer_disc = $last_price*$cartItem['quantity'];
                           $peer_disc_price += $peer_disc;
                           $ttl = $subtotal - $peer_disc_price;

                            if($ttl>=$free_shipping_amount)
                            {
                                $shipping = 0;
                            }

                           $product = App\Product::findOrFail($id);
                          // $tax = round($ttl * $product->tax)/100;
                           $taxquantity = $product->tax*$cartItem['quantity'];
                          // $tax += ($prices*$taxquantity)/100;

                          $taxp = ($prices*100)/(100+$product->tax);
                           $tax += (($taxp*$product->tax)/100)*$cartItem['quantity'];

                           $ttl = $ttl + $shipping;
                           $total = $ttl;
                            
                    }else{
                            if($subtotal>=$free_shipping_amount){
                                $shipping = 0;
                            }
                            $total = $subtotal+$shipping;
                            $product = \App\Product::find($cartItem['id']);                      
                            //$tax += $cartItem['tax']*$cartItem['quantity'];
                            $taxquantity = $product->tax*$cartItem['quantity'];
                           
                            //$tax += ($cartItem['price']*$taxquantity)/100;
                            $taxp = ($cartItem['price']*100)/(100+$product->tax);
                            $tax += (($taxp*$product->tax)/100)*$cartItem['quantity'];
                            
                            
                            //Preeti - 11-10-2021
                            //$tax += (peer_discounted_newbase_price($cartItem['id'])*$taxquantity)/100;

                    } 
                    if(isFreshInCategories($product->category_id) || isFreshInSubCategories($product->subcategory_id)){
                       $is_fresh = 1;
                    }else{
                        $is_grocery = 1;
                    }
                    @endphp
                    <tr class="cart_item">
                        <td class="product-name">
                            {{ $product_name_with_choice }}
                            <strong class="product-quantity">?? {{ $cartItem['quantity'] }}</strong>
                            <!-- <span><button type="button" class="btn btn-danger btn-sm">x</button></span>
                            <p style="color: red;">Not Available at this address. Please remove from cart</p> -->
                        </td>
                        <td class="product-total text-right">
                    <?php if(Session::has('referal_discount')){?>


                        <!-- preeti- 11-10-2021 -->
                        <span class="pl-4">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                        <!--<span>{{ single_price(peer_discounted_newbase_price($cartItem['id'],$shortId)*$cartItem['quantity']) }}</span>-->
                        
                    <?php }else{ ?>
                        <span class="pl-4">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                    <?php } ?>
                            
                        </td>
                    </tr>
                @endforeach
                @php
                session()->put('total_amount',$total);
                    $wallet_amount = 0;
                    if(Auth::check()){
                        $wallet_amount = Auth::user()->balance;
                    }
                    session()->put('wallet_amount',$wallet_amount);
                    session()->put('wallet_insert_amount',$total);
                        if($subtotal>=1500)
                        {
                            $shipping = 0;
                        }
                
                @endphp
            </tbody>
        </table>

        <table class="table-cart table-cart-review">

            <tfoot>
                <tr class="cart-subtotal">
                    <th>{{translate('Cart Value')}}</th>
                    <td class="text-right">
                        
                    <?php if(Session::has('referal_discount')){ ?>
                          <span class="strong-600">{{ single_price($subtotal) }}<!-- {{single_price($sub_price) }} --></span>
                    <?php }else{ ?>
                         <span class="strong-600">{{ single_price($subtotal) }}</span>
                    <?php } ?>




                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{translate('Tax')}}</th>
                    <td class="text-right">
                        <span class="text-italic">{{ single_price($tax) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{translate('Delivery Charges')}}</th>
                    <td class="text-right">
                        <span class="text-italic">{{ single_price($shipping) }}</span>
                    </td>
                </tr>

                @if (Session::has('coupon_discount'))
                    <tr class="cart-shipping">
                        <th>{{translate('Coupon Discount')}}</th>
                        <td class="text-right">
                            <span class="text-italic">{{ single_price(Session::get('coupon_discount')) }}</span>
                        </td>
                    </tr>
                @endif
                
                @if(!empty(Auth::user()))
                <tr class="cart-total">
                    <th>
                        <span class="strong-600">
                            <?php 
                                 $uri_path = $_SERVER['REQUEST_URI']; 
                                 $uri_parts = explode('/', $uri_path);
                                 $request_url = end($uri_parts);

                                if(Session::has('referal_discount')){
                                    $check_pay_wallet = $ttl;
                                 }else{
                                     $check_pay_wallet = $total;
                                 }   
                            ?>
                        @if(Auth::user()->balance != 0)
                            @if($request_url == 'payment_select')
                                @if(Auth::user()->balance > $check_pay_wallet)
                                    {{translate('Wallet Balance')}}</span>
                                    <input type="hidden" name="not_partial" id="not_partial" value="not_partial" />
                                </th>
                                @else
                                     <input type="checkbox" id="checkbox1" value="{{ Auth::user()->balance }}" /> {{translate('Use Wallet Balance')}}</span></th>
                                @endif
                                
                            @else
                                {{translate('Wallet Balance')}}</span></th>
                            @endif

                        @else
                        <input type="hidden" name="not_partial" id="not_partial" value="not_partial" />
                            {{translate('Wallet Balance')}}</span></th>
                        @endif
                    <td class="text-right">
                          <!-- <input type="hidden" id="wallet_insert_amount" name="wallet_insert_amount" value="0" /> -->
                          <input type="hidden" id="wallet_amount" value="{{ Auth::user()->balance }}" />
                          <strong><span>{{ single_price(Auth::user()->balance) }}</span></strong>
                    </td>
                </tr>
                @endif
                
                <tr class="cart-total">
                     <?php $cartcount = count(Session::get('cart')); ?>
                    <th><span class="strong-600">{{translate('Total Amount Payable')}}</span></th>
                    <td class="text-right">
                         <?php if($cartcount !=0){ ?>
                        <?php if(Session::has('referal_discount')){ ?>
                              <strong><span><input type="text" style="border: none;text-align: right;font-weight: bold;" value="@if(!empty($ttl)){{ single_price($ttl) }}@endif" readonly="readonly" class="last_amount"></span></strong>
                        <?php }else{ ?>
                              <strong><span><input type="text" style="border: none;text-align: right;font-weight: bold;" value="{{ single_price($total) }}" readonly="readonly" class="last_amount"></span></strong>
                        <?php } ?>
                        <?php } ?>
                       
                    </td>
                </tr>
                @if(Session::has('referal_discount'))
                    <tr class="cart-shipping">
                        <th><span style="color: #009245;font-weight: bold">{{translate('Total Savings')}}</span></th>
                        <td class="text-right">
                             <?php if(Session::has('referal_discount')){ Session::put('total_saving',$peer_disc_price); ?>
                                <span class="text-italic">{{single_price($peer_disc_price)}}</span>
                            <?php }else{ ?>
                                <span class="text-italic">{{ single_price($referal_discount) }}</span>
                            <?php } ?>
                            
                        </td>
                    </tr>
                @endif
                <tr><th></th><td class="text-right">Inclusive of all Taxes</td></tr>
            </tfoot>
        </table>

        @if (Auth::check() && \App\BusinessSetting::where('type', 'coupon_system')->first()->value == 1)
            @if (Session::has('coupon_discount'))
                <div class="mt-3">
                    <form class="form-inline" action="{{ route('checkout.remove_coupon_code') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group flex-grow-1">
                            <div class="form-control bg-gray w-100">{{ \App\Coupon::find(Session::get('coupon_id'))->code }}</div>
                        </div>
                        <button type="submit" class="btn btn-base-1">{{translate('Change Coupon')}}</button>
                    </form>
                </div>
            @else
                <div class="mt-3 referral">
						<!--start 27-09-2021-->
						@if(Session::has('referal_discount'))
                        <form action="{{route('discount.remove_partner_coupon_code')}}" method="post">
                            @csrf
                            <input style="margin-left: 10px" type="text" name="code" value="{{Session::get('referal_code')}}" placeholder="Enter Peer Code" autocomplete="off">
                            <input type="hidden" name="used_referral_code" value="0">
                            <button type="submit" class="btn"><i class="fa fa-paper-plane"></i></button>
                        </form>
                        @else
                        <form action="{{route('discount.apply_partner_coupon_code')}}" method="post" id="apply_coupon_code">
                            @csrf
                            <input style="margin-left:10px" type="text" name="code" value="{{Session::get('referal_code')}}" id="referral_code" placeholder="Enter Peer Code" required="" autocomplete="off">
                            <input type="hidden" name="used_referral_code" value="1">
                            <!-- <button type="submit" class="btn"><i class="fa fa-paper-plane"></i></button> -->
                        </form>
                        @endif
						
                        <!--@if(Session::has('referal_discount'))
                        <form action="{{route('discount.remove_partner_coupon_code')}}" method="post">
                            @csrf
                            <input style="margin-left: 10px" type="text" name="code" value="{{Session::get('referal_code')}}" placeholder="Enter Peer Partner Code" autocomplete="off">
                            <button type="submit" class="btn"><i class="fa fa-paper-plane"></i></button>
                        </form>
                        @else
                        <form action="{{route('discount.apply_partner_coupon_code')}}" method="post">
                            @csrf
                            <input style="margin-left:10px" type="text" name="code" value="{{Session::get('referal_code')}}" placeholder="Enter Peer Partner Code" required="" autocomplete="off">
                            <button type="submit" class="btn"><i class="fa fa-paper-plane"></i></button>
                        </form>
                        @endif -->
						
						<!--End 27-09-2021-->
						
                    <!-- <form class="form-inline" action="{{ route('checkout.apply_coupon_code') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group flex-grow-1">
                            <input type="text" class="form-control w-100" name="code" placeholder="{{translate('Have coupon code? Enter here')}}" required>
                        </div>
                        <button type="submit" class="btn btn-base-1">{{translate('Apply')}}</button>
                    </form> -->
                    
                </div>
            @endif
        @else
        <!-- 04-10-2021 -->
            <form action="{{route('discount.apply_partner_coupon_code_without_login')}}" method="post" id="apply_coupon_code_without_login">
                @csrf
                <input style="margin-left:10px" type="hidden" name="code" value="{{Session::get('referal_code')}}" id="referral_code" placeholder="Enter Peer Code" required="" autocomplete="off">
                <input type="hidden" name="used_referral_code" value="1">
            </form>
        @endif
        <!-- <p style="background: #fff;padding:  4px 10px; text-align: center; margin: 10px 0 0; font-weight: 600; line-height: 16px; font-size: 14px">Use Peer Code <span style="font-weight: 800; font-family: arial; border:dashed 2px #009848; color: #009848; display: inline-block; padding: 3px 8px; margin: 0 5px">ROZANA7</span> to avail a discount on your entire order.</p> -->
        <p style="color:#009245;font-style: oblique;background: #fff;padding:  4px 10px; text-align: center; margin: 10px 0 0; font-weight: 600; line-height: 16px; font-size: 14px">
        Enter your peer code here to avail amazing discounts on your order.<br></p>
        <p style="color:#009245;font-style: oblique;background: #fff;padding:  4px 10px; text-align: center; margin: 10px 0 0; font-weight: 600; line-height: 16px; font-size: 14px">Don't have referral code? <span id="click_here" style="color: #4183c4;cursor: pointer;"><i>Click Here<i><span></p>
        <div style="display: none; text-align: center;" id="code_box">
            <span id="code" class="c_code" style="" value="ROZANA7">ROZANA7</span>
            <button id="copy_code" name="copy_code" style="height: 40px; margin-top: -2px" class="btn btn-styled btn-base-1" >APPLY CODE</button>
		</div>
	</div>
</div>
<input type="hidden" id="can-process"  value="@if(empty($canProcess)) 1 @else 0 @endif" />
<input type="hidden" name="is-fresh" id="is-fresh" value="{{ $is_fresh }}" />
<input type="hidden" name="is-grocery" id="is-grocery" value="{{ $is_grocery }}" />

 <script type="text/javascript">
	$(document).ready(function() {
		$('#click_here').click(function(){
			$('#code_box').toggle();
		});
		$('#copy_code').click(function(){
			var code = $('#code').text();
			$('#referral_code').val(code);
			$('#code_box').toggle();
            $('#apply_coupon_code').submit();
            $('#apply_coupon_code_without_login').submit(); //04-10-2021
		});
    });
</script>