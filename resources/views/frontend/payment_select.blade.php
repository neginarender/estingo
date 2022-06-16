@extends('frontend.layouts.app')

@section('content')
<style type="text/css">
    @media(max-width: 800px){
        .tooltip{z-index: 10!important}
    }
</style>
    <div id="page-content">
        <section class="slice-xs sct-color-2 border-bottom">
            <div class="container container-sm">
                <div class="row cols-delimited justify-content-center">
                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="la la-shopping-cart"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">{{ translate('1. My Cart')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="la la-map-o"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">{{ translate('2. Shipping info')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="la la-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">{{ translate('3. Delivery info')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon mb-0">
                                <i class="la la-credit-card"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">{{ translate('4. Payment')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="la la-check-circle"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">{{ translate('5. Confirmation')}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-3 gry-bg">
            <div class="container">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-8">
                        <form action="{{ route('payment.checkout') }}" class="form-default" data-toggle="validator" role="form" method="POST" id="checkout-form">
                            @csrf
                            <div class="card">
                                <div class="card-title px-4 py-3">
                                    <h3 class="heading heading-5 strong-500">
                                        {{ translate('Select a payment option')}}
                                    </h3>
                                </div>
                                <div class="card-body text-center">
                                    <div class="row">
                                        <div class="col-md-6 mx-auto">
                                            <div class="row">
                                                @if(\App\BusinessSetting::where('type', 'paypal_payment')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Paypal">
                                                            <input type="radio" id="" name="payment_option" value="paypal" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/paypal.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'stripe_payment')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Stripe">
                                                            <input type="radio" id="" name="payment_option" value="stripe" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/stripe.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'sslcommerz_payment')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="sslcommerz">
                                                            <input type="radio" id="" name="payment_option" value="sslcommerz" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/sslcommerz.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'instamojo_payment')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Instamojo">
                                                            <input type="radio" id="" name="payment_option" value="instamojo" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/instamojo.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                {{--@if(\App\BusinessSetting::where('type', 'razorpay')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Razorpay">
                                                            <input type="radio" id="" name="payment_option" value="razorpay" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/rozarpay.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div> 
                                                @endif--}}
                                                @if(\App\BusinessSetting::where('type', 'paystack')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Paystack">
                                                            <input type="radio" id="" name="payment_option" value="paystack" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/paystack.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'voguepay')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="VoguePay">
                                                            <input type="radio" id="" name="payment_option" value="voguepay" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/vogue.png')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'payhere')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="payhere">
                                                            <input type="radio" id="" name="payment_option" value="payhere" class="online_payment">
                                                            <span>
                                                               <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/payhere.png')}}" class="img-fluid">
                                                           </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'letzpay_payment')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Letzpay payment">
                                                            <input type="radio" id="" name="payment_option" value="letzpay_payment" class="online_payment">
                                                            <span>
                                                                <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/letzpay.jpg')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\BusinessSetting::where('type', 'ngenius')->first()->value == 1)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="ngenius">
                                                            <input type="radio" id="" name="payment_option" value="ngenius" class="online_payment">
                                                            <span>
                                                           <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/ngenius.png')}}" class="img-fluid">
                                                       </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(\App\Addon::where('unique_identifier', 'african_pg')->first() != null && \App\Addon::where('unique_identifier', 'african_pg')->first()->activated)
                                                    @if(\App\BusinessSetting::where('type', 'mpesa')->first()->value == 1)
                                                        <div class="col-6">
                                                            <label class="payment_option mb-4" data-toggle="tooltip" data-title="mpesa">
                                                                <input type="radio" id="" name="payment_option" value="mpesa" class="online_payment">
                                                                <span>
                                                            <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/mpesa.png')}}" class="img-fluid">
                                                        </span>
                                                            </label>
                                                        </div>
                                                    @endif
                                                    @if(\App\BusinessSetting::where('type', 'flutterwave')->first()->value == 1)
                                                        <div class="col-6">
                                                            <label class="payment_option mb-4" data-toggle="tooltip" data-title="flutterwave">
                                                                <input type="radio" id="" name="payment_option" value="flutterwave" class="online_payment">
                                                                <span>
                                                            <img loading="lazy"  src="{{ static_asset('frontend/images/icons/cards/flutterwave.png')}}" class="img-fluid">
                                                        </span>
                                                            </label>
                                                        </div>
                                                    @endif
                                                @endif
                                                {{-- @if(\App\Addon::where('unique_identifier', 'paytm')->first() != null && \App\Addon::where('unique_identifier', 'paytm')->first()->activated)
                                                    <div class="col-6">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Paytm">
                                                            <input type="radio" id="" name="payment_option" value="paytm" class="online_payment">
                                                            <span>
                                                                <img loading="lazy" src="{{ static_asset('frontend/images/icons/cards/paytm.jpg')}}" class="img-fluid">
                                                            </span>
                                                        </label>
                                                    </div>
                                             @endif --}}
                                                @if(\App\BusinessSetting::where('type', 'cash_payment')->first()->value == 1)
                                                    @php
                                                    $min_order_amount = (int) env("MIN_ORDER_AMOUNT");
                                                    $free_shipping_amount = (int) env("FREE_SHIPPING_AMOUNT");
                                                        $digital = 0;
                                                        $subtotal = 0;
                                                        $shipping = 0;
                                                        $sub_price = 0;
                                                        $peer_disc_price = 0;
                                                        foreach(Session::get('cart') as $cartItem){

                                                        $subtotal += $cartItem['price']*$cartItem['quantity'];
                                                        $shipping += $cartItem['shipping'];
                                                            if($cartItem['digital'] == 1){
                                                                $digital = 1;
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
                                                                    $product = \App\MappingProduct::where(['sorting_hub_id'=>$shortId->sorting_hub_id,'product_id'=>$id])->first();
                                                                    $price = $product['purchased_price'];
                                                                    $stock_price = $product['selling_price'];

                                                                    if($price == 0 || $stock_price == 0){
                                                                        $id = $cartItem['id'];
                                                                        $product = App\Product::findOrFail($id);
                                                                        $price = $product->unit_price;
                                                                        $productstock = App\ProductStock::where('product_id', $id)->select('price')->first();
                                                                        $stock_price = $productstock->price; 
                                                                    }  

                                                                }
                                                              
                                                               $main_discount = $stock_price - $price;
                                                                if(!empty($shortId)){
                                                                    $peer_discount_check = App\PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
                                                                }else{
                                                                    $peer_discount_check = App\PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
                                                                }    

                                                               $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);
                                                               $last_price = ($main_discount * $discount_percent)/100; 
                                                               $prices = $stock_price - $last_price;
                                                               $sub_price += $prices*$cartItem['quantity'];
                                                               $peer_disc = $last_price*$cartItem['quantity'];
                                                               $peer_disc_price += $peer_disc;

                                                               $all_ttl = $subtotal - $peer_disc_price;
                                                                if($all_ttl>=$free_shipping_amount)
                                                                {
                                                                    $shipping = 0;
                                                                }
                                                               $check_ttl = $all_ttl + $shipping;

                                                        }else{
                                                            if($subtotal>=$free_shipping_amount)
                                                            {
                                                                $shipping = 0;
                                                            }
                                                            $check_ttl = $subtotal + $shipping;
                                                        }

                                                        }
                                                    @endphp
                                                    @if($digital != 1)
                                                        <div class="col-6" id="cod">
                                                            <label class="payment_option mb-4" data-toggle="tooltip" data-title="Cash on Delivery">
                                                                <input type="radio" id="cod_option" name="payment_option" value="cash_on_delivery" class="online_payment" checked>
                                                                <span>
                                                                    <img loading="lazy" src="{{ static_asset('frontend/images/icons/cards/cash_on_delivery.jpg')}}" class="img-fluid" style="border-radius:5px">
                                                                </span>
                                                            </label>
                                                        </div>
                                                        @if(dofoCheck(Session::get('shipping_info')) == 1)
                                                        {{getdofoDetail(Session::get('shipping_info'))}}
                                                        {{-- <div class="col-6">
                                                         <strong>Select Date:</strong> <input type="text" id="datepicker" class="from-control" name="order_date">
                                                         <strong>Select Time:</strong> <input type="text" id="timepicker" class="from-control" name = "order_time">
                                                         </div> --}}
                                                        @endif 
                                                    @endif
                                                @endif
                                                @if (Auth::check())
                                                    @if (\App\Addon::where('unique_identifier', 'offline_payment')->first() != null && \App\Addon::where('unique_identifier', 'offline_payment')->first()->activated)
                                                        @foreach(\App\ManualPaymentMethod::all() as $method)
                                                            <div class="col-6">
                                                                <label class="payment_option mb-4" data-toggle="tooltip" data-title="{{ $method->heading }}">
                                                                    <input type="radio" id="" name="payment_option" value="{{ $method->heading }}" onchange="toggleManualPaymentData({{ $method->id }})">
                                                                    <span>
                                                                      <img loading="lazy"  src="{{ static_asset($method->photo)}}" class="img-fluid">
                                                                  </span>
                                                                </label>
                                                            </div>
                                                        @endforeach

                                                        @foreach(\App\ManualPaymentMethod::all() as $method)
                                                            <div id="manual_payment_info_{{ $method->id }}" class="d-none">
                                                                @php echo $method->description @endphp
                                                                @if ($method->bank_info != null)
                                                                    <ul>
                                                                        @foreach (json_decode($method->bank_info) as $key => $info)
                                                                            <li>Bank Name - {{ $info->bank_name }}, Account Name - {{ $info->account_name }}, Account Number - {{ $info->account_number}}, Routing Number - {{ $info->routing_number }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </div>
                                                    <div class="row display-none" id="cod_not_avail">
                                                        <div class="col-md-12">
                                                          <span class="alert alert-danger" style="display:block;padding:25px;"><h5>Dear Customer, please be informed that Cash on delivery is not valid for orders above Rs {{ env('MAX_COD_AMOUNT') }}/-</h5></span>  
                                                        </div>
                                                    </div>
                                        </div>
                                    </div>

                                    <div class="card mb-3 bg-gray text-left p-3 d-none">
                                        <div id="manual_payment_description">

                                        </div>
                                    </div>
                                    @if (Auth::check() && \App\BusinessSetting::where('type', 'wallet_system')->first()->value == 1)
                                        <div class="or or--1 mt-2">
                                            <span>or</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-xxl-6 col-lg-8 col-md-10 mx-auto">
                                                <div class="text-center bg-gray py-4">
                                                    <i class="fa"></i>
                                                    <input type="hidden" id="wallet_insert_amount" name="wallet_insert_amount" value="0" />
                                                    <div class="h5 mb-4">{{ translate('Your wallet balance :')}} <strong>{{ single_price(Auth::user()->balance) }}</strong></div>

                                                 
                                                    @if(Auth::user()->balance < 0)
                                                        <button type="button" class="btn btn-base-2" disabled>{{ translate('Insufficient balance')}}</button>
                                                    @else
                                                    <input type="hidden" value="{{ Auth::user()->balance }}" name="walletamount" class="walletamount">
                                                        @if(Auth::user()->balance >= $check_ttl)
                                                        <button  type="button" onclick="use_wallet()" id="wallet_pay_button" class="btn btn-base-1">{{ translate('Pay with wallet')}}</button>
                                                            <button class="btn btn-success" id="process_button" style="display:none;" disabled>
                                                                <span class="spinner-border spinner-border-sm"></span>
                                                                Processing..
                                                            </button>
                                                         @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-3">
                                <input id="agree_checkbox" checked="" type="checkbox" required>
                                <label for="agree_checkbox text-medium" style="font-size: 1.05rem;">{{ translate('I agree to the')}}</label>
                                <a href="{{ route('terms') }}" target="_blank">{{ translate('terms and conditions')}}</a>
                                <!-- <a href="{{ route('returnpolicy') }}" target="_blank">{{ translate('return policy')}}</a>  -->&
                                <a href="{{ route('privacypolicy') }}" target="_blank">{{ translate('privacy policy')}}</a>
                            </div>

                            <div class="row align-items-center pt-3">
                                <div class="col-6">
                                <a href="javascript:void(0)" onclick="window.history.back()" class="link link--style-3">
                                        <i class="ion-android-arrow-back"></i>
                                        {{ translate('Return Back')}}
                                    </a>
                                </div>
                                <div class="col-6 text-right">
                                    <button type="button" onclick="submitOrder(this)" class="btn btn-styled btn-base-1">{{ translate('Complete Order')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4 ml-lg-auto" id="cart-summary">
                        @include('frontend.partials.cart_summary')
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@php 
$shipping = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
@endphp
@section('script')
      <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
      <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script type="text/javascript">

$(document).ready(function($) {
        $( "#datepicker" ).datepicker({ 
            dateFormat: "yy-mm-dd"
        });
        $('#timepicker').timepicker({
            showSecond: true,
            timeFormat: 'hh:mm:ss p',
            showMeridian: false 
        });
    });

        $(document).ready(function() {
            var final_amount= parseFloat($("#total_amount").val());
            console.log(final_amount);
            var max_cod_amount = "{{ env('MAX_COD_AMOUNT') }}";
            if(final_amount>parseInt(max_cod_amount)){
                $("#cod").hide();
                $("#cod_option").removeAttr("checked");
                $("#cod_not_avail").removeClass('display-none');
            }
            $('#checkbox1').change(function() {
                var payment_method = $("input[class='online_payment']:checked").val();
                var checkbox = $('#checkbox1');
                
                if(payment_method == 'razorpay'){
                    var checked = $(this).is(':checked');

                    if (checked) {
                        // alert('checked');
                        $(this).prop("checked", true);
                        var total_amount = $('#total_amount').val();
                        var wallet_amount = $("input[id='checkbox1']:checked").val();

                        if (parseInt(total_amount) > parseInt(wallet_amount)) {
                           wallet_amount = wallet_amount;
                        }else{
                            wallet_amount = total_amount;
                        }

                        var grand_total = total_amount - wallet_amount;
                        $('#total_amount').val(grand_total);

                        // var str1 = '?';
                        // var res = str1.concat(grand_total);
                        var res = new Intl.NumberFormat('en-IN', {
                          style: 'currency',
                          currency: 'INR'
                        }).format(grand_total);
                        var str2 = $('.last_amount').val(res);

                        $('#wallet_insert_amount').val(wallet_amount);
                    } else {
                        // alert('unchecked');
                        var total_amount = $('#total_a').val();
                        $('#checkbox1').prop("checked", false);
                        $('#total_amount').val(total_amount);

                        // var str1 = '?';
                        // var res = str1.concat(total_amount);
                        var res = new Intl.NumberFormat('en-IN', {
                          style: 'currency',
                          currency: 'INR'
                        }).format(total_amount);
                        var str2 = $('.last_amount').val(res);
                        $('#wallet_insert_amount').val(0);
                    }
                        
                }else{
                    alert('Please select online payment option for wallet balance uses.');
                    $(this).prop("checked", false);
                }        
            });
        });

        $(document).ready(function(){
            $(".online_payment").click(function(){
                $('#manual_payment_description').parent().addClass('d-none');
                 var payment_method = $("input[class='online_payment']:checked").val();
                    
                    var total_amount = $('#total_a').val();
                    if(payment_method == 'cash_on_delivery'){
                        $('#checkbox1').prop("checked", false);
                        $('#total_amount').val(total_amount);

                        // var str1 = '?';
                        // var res = str1.concat(total_amount);
                        var res = new Intl.NumberFormat('en-IN', {
                          style: 'currency',
                          currency: 'INR'
                        }).format(total_amount);
                        var str2 = $('.last_amount').val(res);
                        $('#wallet_insert_amount').val(0);
                    }
            });
        });

        function use_wallet(){
            $("#wallet_pay_button").hide();
            $("#process_button").show();
            var total_amount = Math.fround($('#total_a').val());
            var walletamount = Math.fround($('.walletamount').val());
            // alert(total_amount);
            // alert(walletamount);
            if(walletamount >= total_amount){
               // alert('dd');
               if($('#agree_checkbox').is(":checked")){
                    $('input[name=payment_option]').val('wallet');
                    $('#checkout-form').submit();
                }else{
                    showFrontendAlert('error','{{ translate('To complete your purchase, please agree with our policies') }}');
                    $("#wallet_pay_button").show();
                    $("#process_button").hide();
                }
            }else{
                    alert('Insufficient blance.');
                    $("#wallet_pay_button").show();
                    $("#process_button").hide();
            }
            
            if($('#agree_checkbox').is(":checked")){
                $('#checkout-form').submit();
            }else{
                showFrontendAlert('error','{{ translate('To complete your purchase, please agree with our policies') }}');
                    $("#wallet_pay_button").show();
                    $("#process_button").hide();
            }
        }
        function submitOrder(el){
            var total_amount = $("#total_amount").val();
            var min_order_amount = parseInt("{{ env('MIN_ORDER_AMOUNT') }}");
            var free_shipping_amount = parseInt("{{ env('FREE_SHIPPING_AMOUNT') }}");
            $(el).prop('disabled', true);
            if($('#agree_checkbox').is(":checked")){
                //$('#checkout-form').submit();
                if(total_amount < min_order_amount && $("#not_partial").val()=="not_partial")
                {
                    Swal.fire({
                icon: 'info',
                title: 'Minimum order amount Rs. '+min_order_amount,
                html:
                    '<p style="text-align: justify; color:red;"><span>Note*</span><br><b>1- Below Rs.'+min_order_amount+' = Rs. {{ $shipping }} Delivery charge</b> <br /><b>2- Above Rs. '+free_shipping_amount+' Free Delivery<b></p>'
                });
                $(el).prop('disabled', false);
                return false;
                }
                else if(total_amount < min_order_amount && $('#checkbox1').is(":checked")==false){

                    Swal.fire({
                icon: 'info',
                title: 'Minimum order amount Rs. '+min_order_amount,
                html:
                    '<p style="text-align: justify; color:red;"><span>Note*</span><br><b>1- Bewlo Rs. '+min_order_amount+' = Rs. {{ $shipping }} Delivery charge</b> <br /><b>2- Above Rs. '+free_shipping_amount+' Free Delivery<b></p>'
                });
                $(el).prop('disabled', false);
                return false;
                }

                else{
                    $('#checkout-form').submit();
                }
               
            }else{
                
                    showFrontendAlert('error','{{ translate('To complete your purchase, please agree with our policies') }}');
                    $(el).prop('disabled', false);
                
            }
        }

        function toggleManualPaymentData(id){
            $('#manual_payment_description').parent().removeClass('d-none');
            $('#manual_payment_description').html($('#manual_payment_info_'+id).html());
        }
    </script>
@endsection
