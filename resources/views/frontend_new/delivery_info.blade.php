
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
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon mb-0 ">
                                <i class="fa fa-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">3. Delivery info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
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
                        <form class="form-default guest_chkout"   action="{{ route('phoneapi.payment_options') }}" role="form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                              <div class="card mb-3 product_item_list">
                                <div class="card-header  py-3">
                                    <h5 class="heading-6 mb-0"> Grocery &amp; Fresh Products </h5>
                                </div>
                                <div class="card-body">
                                    @foreach($carts as $key => $cart)
                                    @php $cid = $cart->id.$cart->variation.$cart->product_id;@endphp
                                      <div class="row mt-2 mb-2 0 product_item cart" id="row{{$cart->id}}">
                                        <div class="col-md-2 col-4">
                                            <a href="" target="_blank">
                                                <img loading="lazy"src="{{ Storage::disk('s3')->url($cart->product->image) }}">
                                            </a>
                                        </div>
                                        @php 
                                       
                                            $price = $cart->stock_price;
                                            if(Cookie::has('peer')){
                                                $price = $cart->base_price;
                                            }
                                        
                                        @endphp
                                         <div class="col-md-10 col-8 pt-3">
                                            <h4 class="mb-0"><a href="{{ route('product.details',['id'=>encrypt($cart->product_id)]) }}" target="_blank" class="d-block c-base-2">
                                               {{ translate($cart->product->name) }}
                                            </a> </h4>
                                            <span class="p-price strong-600 text-danger" id="p-price{{$cid}}" p-price{{$cid}}="{{$cart->base_price}}">
                                            {{ single_price($price) }}
                                            </span>
                                            @if(Cookie::has('peer'))
                                            <del class="old-product-price strong-400 text-danger">{{ single_price($cart->stock_price) }}</del>
                                            @endif
                                            @if(!$cart->available)
                                            <p class="text-danger">Product Not avaiable at this location</p>
                                            @endif
                                             <div class="p-btns float-md-right">
                                             <div class="input-group input-group--style-2 pr-4"  >
                                                <span class="input-group-btn">
                                                    <button class="btn btn-number minus-button" type="button" data-type="minus" data-field="pqty{{ $cid }}">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </span>
                                                <input type="text" style="width:50px;text-align:center;" name="pqty{{ $cid }}" id="pqty{{ $cid }}" class="form-control h-auto input-number" dcid="{{$cart->id}}" page-cart="{{$cid}}" onchange="updateCart('{{$cid}}')" placeholder="1" value="{{ $cart->quantity }}" min="1" max="{{ $cart->max_purchase_qty }}"   readonly="">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-number plus-button" type="button" data-type="plus" data-field="pqty{{ $cid }}">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            <br />
                                                <button type="button" class="f-12 btn btn-secondary" id="removebtn{{$cart->id}}" onclick="removeFromCart('{{ $cart->id }}')" page-cart="{{$cid}}">Remove</button>
                                                 </div>
                                            
                                            
                                        </div>
                                    </div>
                                    @endforeach
                                      
                                     
                                </div>
                            </div>
                            <div class="card mb-3 product_item_list">
                                <div class="card-header  py-3">
                                    <h5 class="heading-6 mb-0">Select Delivery Option</h5>
                                </div>
                                 <div class="card-body">
                                        <div class="row">    
                                            <div class="col-md-6">
                                               <label>  <input type="radio" id="normal_delivery" name="delivery_type" value="normal" checked="">
                                                <span  ><strong>Normal Delivery: Within 24 Hrs. </strong></span> </label>
                                            </div>
                                           
                                            <!-- <div class="col-md-6">
                                                 <label><input type="radio" id="slotted_delivery" name="delivery_type" value="scheduled">
                                                <span  ><strong> Scheduled Delivery</strong></span> </label>
                                            </div> -->
                                            
                                        </div>
                                    </div>
                                
                            </div>

                            <input type="hidden" name="fresh_incart" value="{{ $availSlots->is_fresh }}" />
                            <input type="hidden" name="grocery_incart" value="{{ $availSlots->is_grocery }}" />
                            {{--
                            <div class="card mb-3 product_item_list" id="deliveryDateTime"  >
                               <div class="card-header   py-3">
                                <h5 class="heading-6 mb-0">Select Date &amp; Time</h5>
                                </div>                                
                                <div class="card-body" id="grocery_card"  >
                                    @if($availSlots->is_grocery)
                                    <div class="row gro">                                         
                                       <div class="col-md-12 sub-head" >
                                            <h4 class="text-dark">GROCERY</h4>
                                        </div>
                                    </div>
                                    <!-- available dates -->
                                    @php
                                    
                                        $grocery_today_slot = count($availSlots->grocery_detail->todaySlot_grocery);
                                        $grocery_tomorrow_slot = count($availSlots->grocery_detail->tommorowSlot_grocery);
                                    @endphp
                                    <div class="row gro">
                                        <div class="col-md-4">
                                            <label><input type="radio" id="today_slot_grocery" name="delivery_date_grocery" value="{{date('Y-m-d',strtotime($todayDate))}}" onclick="selectSlot('grocery','today',{{ $grocery_today_slot }})" @if($grocery_today_slot) checked @else disabled @endif>
                                            <label for="html">@if($grocery_today_slot) {{ $todayDate }} (Today) @else <del> {{ $todayDate }} (Today)<del></del></del> @endif </label>
                                            </div>
                                         <div class="col-md-6">
                                           <label> <input type="radio" id="tommorow_slot_grocery" name="delivery_date_grocery" value="{{date('Y-m-d',strtotime($tommorowDate))}}" onclick="selectSlot('grocery','tomorrow')" checked="checked">
                                             <strong> {{ $tommorowDate }} (Tommorow)</strong></label>
                                        </div>
                                    </div>
                                      <!-- available times slots -->
                                    <div class="row gro" id="tommorow_avail_slot_grocery"  >
                                    @foreach($availSlots->grocery_detail->todaySlot_grocery as $key => $todayGro)
                                        <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6 today-gro">
                                            <label class="d_time">
                                                <input checked="checked" type="radio" class="delivery_slot_grocery_today" name="delivery_slot_grocery" value="{{ $todayGro }}">
                                            <span> {{ $todayGro }}</span>
                                        </label>
                                        </div>
                                    @endforeach
                                        @foreach($availSlots->grocery_detail->tommorowSlot_grocery as $key => $tomorrowGro)
                                        <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6 gro tomorrow-gro">
                                            <label class="d_time">
                                                <input type="radio" class="delivery_slot_grocery_tom" name="delivery_slot_grocery_tom" value="{{ $tomorrowGro }} ">
                                            <span> {{ $tomorrowGro }}</span>
                                        </label>
                                        </div>
                                        @endforeach
                                        
                                         
                                                                                 
                                    </div>
                                    @endif 
                                    @if($availSlots->is_fresh)
                                    <div class="row fresh">
                                       <div class="col-md-12 sub-head mt-4"  >
                                                <h4 class="text-dark">FRUITS, VEGETABLES &amp; DAIRY</h4>
                                        </div>
                                    </div>
                                    @php
                                        $fresh_today_slot = count($availSlots->fresh_detail->todaySlot_fresh);
                                        $fresh_tomorrow_slot = count($availSlots->fresh_detail->tommorowSlot_fresh);
                                    @endphp
                                    <div class="row fresh"> 
                                         <div class="col-md-4">
                                                <input type="radio" id="today_slot_fresh" name="delivery_date_fresh" value="{{date('Y-m-d',strtotime($todayDate))}}" onclick="selectSlot('fresh','today',{{ $fresh_today_slot }})" @if($fresh_today_slot) checked @else disabled @endif>
                                                <label for="html">@if($fresh_today_slot) {{ $todayDate }} (Today) @else <del> {{ $todayDate }} (Today)<del></del></del> @endif </label>
                                            </div>
                                             <div class="col-md-6">
                                                <label> <input type="radio" id="tommorow_slot_fresh" name="delivery_date_fresh" value="{{date('Y-m-d',strtotime($tommorowDate))}}" onclick="selectSlot('fresh','tomorrow')" checked="checked">
                                                <strong>{{ $tommorowDate }} (Tommorow)</strong></label>
                                            </div>   
                                    </div>
                                    <div class="row fresh" id="tommorow_avail_slot_fresh">
                                        @foreach($availSlots->fresh_detail->todaySlot_fresh as $key => $todayFresh)
                                           <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6 today-fresh">
                                                <label class="d_time">  
                                                    <input checked="checked" type="radio" class="delivery_slot_fresh_today" name="delivery_slot_fresh" value="{{ $todayFresh }}">
                                                    <span>{{ $todayFresh }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                        @foreach($availSlots->fresh_detail->tommorowSlot_fresh as $key => $tomorrowFresh)
                                             <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6 tomorrow-fresh">
                                                <label class="d_time">  
                                                    <input type="radio" class="delivery_slot_fresh_tom" name="delivery_slot_fresh_tom" value="{{ $tomorrowFresh }}">
                                                    <span>{{ $tomorrowFresh }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                     </div>


                                </div>
                                @endif
                            </div> --}}
                                
                            <div class="row align-items-center pt-4 cart_bottom pb-4">
                                <div class="col-md-6 col-sm-6 col-5">
                                    <a href="javascript:void(0)" onclick="window.history.go(-1); return false;" class="link link--style-3">
                                        <i class="fa fa-arrow-left"></i>
                                        Return Back
                                    </a>
                                </div>
                                <div class="col-md-6  col-sm-6 col-7 text-right">
                                    <button  onclick="window.open('payment.html','_self')" class="btn btn-styled btn-base-1">Continue to Payment
                                  </button>
                               </div>
                            </div>
                        </form>
                    </div>
                     <!-- right sidebar  -->
                
                    <div
                     class="col-xl-4 ml-lg-auto" id="cart-summary">
                      
                   </div>
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

            function proceedToShipping(){
                if("{{ Cookie::get('logged')}}"){
                    window.location.href="{{ route('phoneapi.shipping_info') }}";
                }else{
                    console.log('user not logged in');
                }
            }

            function selectSlot(type,slot,status="enable"){
                if(type=="fresh"){
                    if(slot=="today"){
                        $(".delivery_slot_fresh_tom:first").removeAttr('checked');
                        $(".delivery_slot_fresh_today:first").attr('checked','checked');
                        
                        $(".tomorrow-fresh").hide();
                        $(".today-fresh").show();
                    }
                    else{
                        $(".delivery_slot_fresh_today:first").removeAttr('checked');
                        $(".delivery_slot_fresh_tom:first").attr('checked','checked');
                        
                        $(".today-fresh").hide();
                        $(".tomorrow-fresh").show(); 
                    }
                }
                else{
                    if(slot=="today"){
                        $(".delivery_slot_grocery_tom:first").removeAttr('checked');
                        $(".delivery_slot_grocery_today:first").attr('checked','checked');
                        
                        $(".tomorrow-gro").hide();
                        $(".today-gro").show();
                    }
                    else{
                        $(".delivery_slot_grocery_today:first").removeAttr('checked');
                        $(".delivery_slot_grocery_tom:first").attr('checked','checked');
                        $(".today-gro").hide();
                        $(".tomorrow-gro").show(); 
                    } 
                }
            } 

            
            </script>
        @endsection