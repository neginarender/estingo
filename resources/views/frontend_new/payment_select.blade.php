
@extends('frontend_new.layouts.app')
@section('content')
         <section class="slice-xs sct-color-2 border-bottom">
            <div class="container container-sm">
                <div class="row cols-delimited justify-content-center">
                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">1. My Cart</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center " >
                            <div class="block-icon  mb-0">
                                <i class="fa fa-map"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">2. Shipping info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0 ">
                                <i class="fa fa-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">3. Delivery info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">4. Payment</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">5. Confirmation</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </section>

         <section class="py-4 gry-bg">
            <div class="container">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-8">
                        <form class="form-default" id="payment_form" action="{{ route('phoneapi.generate_order') }}" role="form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                               <div class="card">
                                    <div class="card-title px-4 py-3">
                                        <h3 class="heading heading-5 strong-500">
                                            Select a payment option
                                        </h3>
                                    </div>
                                    <div class="card-body text-center">
                                      <div class="row">
                                        <div class="col-md-6 mx-auto">
                                            <div class="row">
                                                <!-- <div class="col-6">
                                                    <label class="payment_option mb-4" data-toggle="tooltip" data-title="Razorpay">
                                                        <input type="radio" id="" name="payment_option" value="razorpay" class="online_payment">
                                                        <span>
                                                            <img loading="lazy" src="{{ static_asset('frontend/new/assets/images/rozarpay.png') }}" class="img-fluid">
                                                        </span>
                                                    </label>
                                                </div>  -->
                                                 <!-- <div class="col-6">
                                                    <label class="payment_option mb-4" data-toggle="tooltip" data-title="Paytm">
                                                        <input type="radio" id="" name="payment_option" value="paytm" class="online_payment">
                                                        <span>
                                                            <img loading="lazy" src="{{ static_asset('frontend/new/assets/images/paytm.jpg') }}" class="img-fluid">
                                                        </span>
                                                    </label>
                                                 </div> -->
                                                   <div class="col-6" id="cod">
                                                        <label class="payment_option mb-4" data-toggle="tooltip" data-title="Cash on Delivery">
                                                            <input type="radio" id="cod_option" name="payment_option" value="cash_on_delivery" class="online_payment" checked="">
                                                            <span>
                                                                <img loading="lazy" src="{{ static_asset('frontend/new/assets/images/cash_on_delivery.jpg') }}" class="img-fluid" >
                                                            </span>
                                                        </label>
                                                    </div>
                                                    {{--
                                                    @php 
                                                        $loggedin = 0;
                                                        $wallet_balance = 0;
                                                        if(session()->has('user')){
                                                            $loggedin = 1;
                                                            $user = json_decode(session()->get('user'));
                                                            $wallet_balance = $user->wallet_balance;
                                                        }
                                                        
                                                        
                                                       
                                                    @endphp
                                                    --}}
                                            </div>
                                        </div>
                                        
                                    </div>
                                    {{--
                                    @if ($loggedin && \App\BusinessSetting::where('type', 'wallet_system')->first()->value == 1)
                                        <div class="or or--1 mt-2">
                                            <span>or</span>
                                        </div>
                                       
                                        <div class="row">
                                            <div class="col-xxl-6 col-lg-8 col-md-10 mx-auto">
                                                <div class="text-center bg-gray py-4">
                                                    <i class="fa"></i>
                                                    <input type="hidden" id="wallet_insert_amount" name="wallet_insert_amount" value="0" />
                                                    <div class="h5 mb-4">{{ translate('Your wallet balance :')}} <strong>{{ single_price($wallet_balance) }}</strong></div>

                                                 
                                                    @if($wallet_balance < 0)
                                                        <button type="button" class="btn btn-base-2" disabled>{{ translate('Insufficient balance')}}</button>
                                                    @else
                                                    <input type="hidden" value="{{ $wallet_balance }}" name="walletamount" class="walletamount">
                                                        @if($wallet_balance >= 10)
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
                                        --}}
                                    </div>
                            </div>
                            <div class="pt-3">
                                <input id="agree_checkbox" checked="" type="checkbox" required="">
                                <label for="agree_checkbox " class="text-large1"  >I agree to the</label>
                                <a href="terms.html" target="_blank">Terms and conditions</a>
                                <a href="privacypolicy.html" target="_blank">Privacy policy</a>
                            </div>
                                
                            <div class="row align-items-center pt-4 cart_bottom pb-4">
                                <div class="col-md-6 col-sm-6 col-5">
                                <a href="javascript:void(0)" onclick="window.history.go(-1); return false;" class="link link--style-3">
                                        <i class="fa fa-arrow-left"></i>
                                        Return Back
                                    </a>
                                </div>
                                <div class="col-md-6  col-sm-6 col-7 text-right">
                                    <button type="button" onclick="proceedToPlaceOrder(this)" class="btn btn-styled btn-base-1">Complete Order
                                  </button>
                                  <button type="button" class="btn btn-styled btn-base-1" id="process_button" style="display:none;" disabled>
                                    <span class="spinner-border spinner-border-sm"></span>
                                    {{ translate("Processing.. Wait") }}
                                </button>
                               </div>
                            </div>
                        </form>
                    </div>
                     <!-- right sidebar  -->
                
                    <div  class="col-xl-4 ml-lg-auto" id="cart-summary">
                      
                    </div>
            </div>
        </section>
        @endsection
        @section('script')
            <script>
                $(document).ready(function(){
                    loadCartSummary();
                    $("#today_slot_grocery").trigger('click');
                    $("#today_slot_fresh").trigger('click');
                });
            function loadCartSummary(){
                $.ajax({
                        url: '{{ route('ajax.cart_summary') }}',
                        type: 'post',
                        data: {_token:"{{ csrf_token() }}",sessionID:getCookie("sessionID")} ,
                        cache:true,
                        async:false,
                        success:function(data){
                            $("#cart-summary").html(data);
                        }
                    });
            }

            function proceedToPlaceOrder(el){
                var total_amount = $("#total_amount").val();
                var min_order_amount = parseInt("{{ env('MIN_ORDER_AMOUNT') }}");
                var free_shipping_amount = parseInt("{{ env('FREE_SHIPPING_AMOUNT') }}");
                if($('#agree_checkbox').is(":checked")){
                // Agreed with term and conditions
                    //check minimum order amount
                    if(total_amount > min_order_amount){
                       //replace button with processing 
                       $(el).hide();
                       $("#process_button").show(); 
                       $("#payment_form").submit();
                    }
                }else{
                    //please agreed with terms and condition
                    console.log("please agreed with terms and condition");
                }
            }
            </script>
        @endsection