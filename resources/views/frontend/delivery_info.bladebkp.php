@extends('frontend.layouts.app')

@section('content')

<style type="text/css">
    @media(max-width: 800px){
        .referral {width: 100%; position: relative;}
    }
    .f-12{
        font-size:12px;
    }
    .custom-button{
        background: #28a745;
        color: #fff;
        border: none;
        padding: 5px 5px 5px 5px;
        border-radius: 1px;
        border-color: #28a745;
        font-size:11px;
    }
    .slot-heading{
        padding-top: 5px;
        background-color: #37a3e3;
        height: 30px;
        text-align: left;
        color: white;
    }
    .card-header {background: #ddd;}
    .p-btns {margin-top: -30px;}
    .p_price {font-size: 15px!important; font-weight: 600; margin: 0;}
    .p_qty {font-size: 13px!important; font-weight: 600; margin: 0;}
    .d_time input{display: none;}
    .d_time {width: 100%; font-weight: 600;}
    .d_time span {display: block; background: #ddd; width: 100%; padding: 10px ; text-align: center; cursor: pointer; font-size: 13px;}
    .d_time input:checked + span {background: #172E51; color: #fff;}
    @media (max-width: 640px){
          .p-btns {margin-top: 10px;}
          .p-btns .btn {padding: 2px 7px;}
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
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon mb-0">
                                <i class="la la-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">{{ translate('3. Delivery info')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
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
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">5. {{ translate('Confirmation')}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-4 gry-bg">
            <div class="container">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-xl-8">
                        <form class="form-default" data-toggle="validator" id="delivery_info_form" action="{{ route('checkout.store_delivery_info') }}" role="form" method="POST">
                            @csrf
                            @php
                                $admin_products = array();
                                $seller_products = array();
                                //$fresh_products = [];
                                $grocerry_products = [];
                                $gift_cards = [];
                                $gift_flag = 0;
                                $address_flag = 0;
                                $gift_keys = [];
                                $grocerry_keys = [];
                                foreach (Session::get('cart') as $key => $cartItem){
                                    $product = \App\Product::find($cartItem['id']);
                                    if($product->added_by == 'admin'){
                                        
                                            if($product->gift_card){
                                             array_push($gift_keys,$key);
                                             array_push($gift_cards,$product->id);
                                             $gift_flag = 1;
                                             
                                            }
                                            else{
                                                array_push($grocerry_keys,$key);
                                                array_push($grocerry_products,$product->id);
                                               
                                            }
                                    }
                                    else{
                                        $product_ids = array();
                                        if(array_key_exists(\App\Product::find($cartItem['id'])->user_id, $seller_products)){
                                            $product_ids = $seller_products[\App\Product::find($cartItem['id'])->user_id];
                                        }
                                        array_push($product_ids, $cartItem['id']);
                                        $seller_products[\App\Product::find($cartItem['id'])->user_id] = $product_ids;
                                    }
                                }
                                /*if(!empty($fresh_products)){
                                    $admin_products['fresh'] = $fresh_products;
                                }*/
                                
                                if(!empty($grocerry_products)){
                                    $admin_products['grocery'] = $grocerry_products;
                                }
                                if(!empty($gift_cards)){
                                    $admin_products['gift_cards'] = $gift_cards;
                                }
                                
                            @endphp
                            
                            <input type="hidden" name="count_gift_cards" id="count_gift_cards" value="{{ count($gift_cards)}}" />
                            @if (!empty($admin_products))
                            @foreach ($admin_products as $k => $allProducts)
                            
                              
                            <div class="card mb-3">
                                <div class="card-header bg-white py-3">
                                    <h5 class="heading-6 mb-0">@if($k=='fresh') Fresh Products @elseif($k=='gift_cards') Gift Cards @else Grocery & Fresh Products @endif</h5>
                                </div>
                                <div class="card-body">
                                   
                                            
                                            
                                                @php
                                                
                                                $shippingInfo = Session::get('shipping_info');
                                                $delivery_schedule = Session::get('delivery_schedule');
                                                               $productAvail = "";
                                                               $shortinhHub = "";
                                                $buttonDisable = array();
                                                if(!empty($shippingInfo)){               
                                                    $shortinhHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $shippingInfo['postal_code'] . '"]\')')->pluck('user_id')->first();
                                                }              
                                                @endphp

                                                    @foreach ($allProducts as $key => $id)

                                                    @php
                                                            
                                                            if($k=='gift_cards'){
                                                                $removeKey = $gift_keys[$key];
                                                                $qty = Session::get('cart')[$removeKey]['quantity'];
                                                            }else{
                                                                $removeKey =$grocerry_keys[$key];
                                                                $qty = Session::get('cart')[$removeKey]['quantity'];
                                                            }
                                                            if(!empty($shortinhHub)){
                                                                $productAvail = \App\MappingProduct::where(['sorting_hub_id'=>$shortinhHub,'product_id'=>$id,'published'=>1])->first();
                                                                if(empty($productAvail)){
                                                                    array_push($buttonDisable,$id);   
                                                                }
                                                            }
                                                            $prod = \App\Product::find($id);
                                                            @endphp
                                                    
                                                    <div class="row mt-2 mb-2 {{$removeKey}}" style="border-bottom:1px dashed #f1f1f1;">
                                                        <div class="col-md-2 col-4">
                                                            <a href="{{ route('product', \App\Product::find($id)->slug) }}" target="_blank">
                                                                <img loading="lazy" style="max-width:100%;"  src="{{ my_asset(\App\Product::find($id)->thumbnail_img) }}">
                                                                <!-- <img loading="lazy" style="max-width:100%;"  src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"> -->
                                                            </a>
                                                        </div>
                                                        <div class="col-md-4 col-8">
                                                            <h4><a href="{{ route('product', \App\Product::find($id)->slug) }}" target="_blank" class="d-block c-base-2">
                                                                {{ $prod->name }}
                                                            </a> </h4>
                                                           
                                                            <a href="javascript:void();" class="f-12" onclick="removeFromCart({{ $removeKey }})">Remove</a>
                                                            @if(!empty($productAvail))
                                                            | <a href="javascript:void(0);" class="f-12" onclick="updateQtyForm({{$id}})">Edit</a>
                                                            @endif
                                                            @if($k=='gift_cards')
                                                            <span class="add-recipient">&nbsp;|&nbsp;<a href="javascript:void();" class="f-12" id="add-recipient{{$id}}" onclick='showModal("{{$id}}")' data-toggle="modal" data-target="#adress_modal" data-product="{{$id}}">
                                                                @if(!Session::has('address'.$id))
                                                                Add Recipient
                                                                @else
                                                                Edit Recipient
                                                                @endif
                                                            </a></span>
                                                            <p></p>
                                                                @if(!Session::has('address'.$id))
                                                                
                                                                <span style="color:red" class="invalid-feedback{{$id}}"><i class="fa fa-exclamation-circle"></i> Please add recipient details</span>
                                                                    
                                                            @endif
                                                            @endif
                                                            
                                                            @if(!empty($productAvail))
                                                        <div class="form-group" id="updateQty{{$id}}" style="display:none;">
                                                        <input type="hidden" id="max_purchase_limit{{$id}}" value="{{ $prod->max_purchase_qty }}" />
                                                                    <input type="hidden" id="product_id{{$id}}" />
                                                                    <input type="hidden" value="{{ $removeKey }}" id="key{{$id}}" />
                                                                    <input type="number" min="1" value="{{$qty}}" class="mb-2" style="width:50px;margin-right:2px;" name="qty{{$id}}" id="qty{{$id}}"   onkeyup="this.value=this.value.replace(/[^\d]/,'')"/>
                                                                    <button type="button" class="custom-button" onclick='updateQty("{{$id}}")'>Update</button>
                                                        </div>            
                                                            @else
                                                            <br /><b class='f-12' style ='color:red' >Not available at this address. Please Remove from cart.</b>
                                                            @endif
                                                            <p id="message{{$id}}" class='f-12'></p>
                                                        </div>
                                                        @if($k=='gift_cards')
                                                    <div class="col-md-6 col-12">
                                                        <div id="address{{$id}}">
                                                             @if(Session::has('address'.$id))
                                                        
                                                        @php 
                                                        $recipient = json_decode(Session::get('address'.$id));
                                                        $address_flag +=1;
                                                        @endphp
                                                       
                                                            <div class='card mt-3 mt-md-1 mb-3'>
                                                                <div class='card-body'>
                                                                    <h5 class='card-title'>Recipient Details</h5>
                                                                    <strong>Message:</strong> &nbsp;<span class="c-gray-light" id="data-rec-message{{$id}}" data-rec-message="{{ $recipient->message }}">{{ $recipient->message }}</span>
                                                                    <p></p>
                                                                    <strong>From:</strong> &nbsp;<span class="c-gray-light" id="data-rec-from{{$id}}" data-rec-from="{{ $recipient->from}}">{{ $recipient->from}}</span>
                                                                    <p></p>
                                                                    <strong>Send To:</strong> &nbsp;<span class="c-gray-light" id="data-rec-to{{$id}}" data-rec-to="{{$recipient->to}}">{{$recipient->to}}</span>
                                                                    <p></p>
                                                                    <strong>Name:</strong>&nbsp;<span class="c-gray-light" id="data-rec-name{{$id}}" data-rec-name="{{ $recipient->name }}">{{ $recipient->name }} </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                       
                                                        </div>
                                                        @endif
                                                    </div>
                                                   
                                                    @endforeach
                                                
                                        </div>
                                        <div class="col-md-3 offset-md-1">
                                            <div class="row">
                                                <div class="col-12">
                                                <input type="hidden" name="shipping_type_admin" value="home_delivery" checked class="d-none" onchange="show_pickup_point(this)" data-target=".pickup_point_id_admin">
                                                    {{-- <label class="d-flex align-items-center p-3 border rounded gry-bg c-pointer">
                                                        
                                                        <span class="radio-box"></span>
                                                        <span class="d-block ml-2 strong-600">
                                                            {{  translate('Home Delivery') }}
                                                        </span>
                                                    </label> --}}

                                                    <!-- <label class="d-flex align-items-center p-3 border rounded gry-bg c-pointer">
                                                                <input type="radio" name="shipping_type_admin" value="Office_delivery" class="d-none" onchange="show_pickup_point(this)" data-target=".pickup_point_id_{{ $key }}">
                                                                <span class="radio-box"></span>
                                                                <span class="d-block ml-2 strong-600">
                                                                    {{  translate('Office Delivery') }}
                                                                </span>
                                                            </label> -->
                                                </div>
                                                @if (\App\BusinessSetting::where('type', 'pickup_point')->first()->value == 1)
                                                    <div class="col-12">
                                                        <label class="d-flex align-items-center p-3 border rounded gry-bg c-pointer">
                                                            <input type="radio" name="shipping_type_admin" value="pickup_point" class="d-none" onchange="show_pickup_point(this)" data-target=".pickup_point_id_admin">
                                                            <span class="radio-box"></span>
                                                            <span class="d-block ml-2 strong-600">
                                                                {{  translate('Local Pickup') }}
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                            </div>

                                            @if (\App\BusinessSetting::where('type', 'pickup_point')->first()->value == 1)
                                                <div class="mt-3 pickup_point_id_admin d-none">
                                                    <select class="pickup-select form-control-lg w-100" name="pickup_point_id_admin" data-placeholder="{{ translate('Select a pickup point') }}">
                                                            <option>{{ translate('Select your nearest pickup point')}}</option>
                                                        @foreach (\App\PickupPoint::where('pick_up_status',1)->get() as $key => $pick_up_point)
                                                            <option value="{{ $pick_up_point->id }}" data-address="{{ $pick_up_point->address }}" data-phone="{{ $pick_up_point->phone }}">
                                                                {{ $pick_up_point->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                </div>
                            </div>
                            @endforeach
                            @endif
                             <input type="hidden" name="address_count" id="address_count" value="{{ $address_flag }}" />
                            @if (!empty($seller_products))
                                @foreach ($seller_products as $key => $seller_product)
                                    <div class="card mb-3">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="heading-6 mb-0">{{ \App\Shop::where('user_id', $key)->first()->name }} Products</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row no-gutters">
                                                <div class="col-md-8">
                                                    <table class="table-cart">
                                                        <tbody>
                                                            @foreach ($seller_product as $id)
                                                            <tr class="cart-item">
                                                                <td class="product-image" width="25%">
                                                                    <a href="{{ route('product', \App\Product::find($id)->slug) }}" target="_blank">
                                                                        <img loading="lazy"  src="{{ my_asset(\App\Product::find($id)->thumbnail_img) }}">
                                                                        <!-- <img loading="lazy"  src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"> -->
                                                                    </a>
                                                                </td>
                                                                <td class="product-name strong-600">
                                                                    <a href="{{ route('product', \App\Product::find($id)->slug) }}" target="_blank" class="d-block c-base-2">
                                                                        {{ \App\Product::find($id)->name }}
                                                                    </a>
                                                                    @php
                                                                    if(!empty($shortinhHub)){
                                                                        $productAvail = \App\MappingProduct::where(['sorting_hub_id'=>$shortinhHub,'product_id'=>$id])->first();
                                                                        if(empty($productAvail)){
                                                                            array_push($buttonDisable,$id);
                                                                            echo "<p style ='color:red' >Not available. Please Remove from cart.</p>";
                                                                        }
                                                                    }
                                                            @endphp
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-3 offset-md-1">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label class="d-flex align-items-center p-3 border rounded gry-bg c-pointer">
                                                                <input type="radio" name="shipping_type_{{ $key }}" value="home_delivery" checked class="d-none" onchange="show_pickup_point(this)" data-target=".pickup_point_id_{{ $key }}">
                                                                <span class="radio-box"></span>
                                                                <span class="d-block ml-2 strong-600">
                                                                    {{  translate('Home Delivery') }}
                                                                </span>
                                                            </label>

                                                             <label class="d-flex align-items-center p-3 border rounded gry-bg c-pointer">
                                                                <input type="radio" name="shipping_type_{{ $key }}" value="Office_delivery" class="d-none" onchange="show_pickup_point(this)" data-target=".pickup_point_id_{{ $key }}">
                                                                <span class="radio-box"></span>
                                                                <span class="d-block ml-2 strong-600">
                                                                    {{  translate('Office Delivery') }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                        @if (\App\BusinessSetting::where('type', 'pickup_point')->first()->value == 1)
                                                            @if (is_array(json_decode(\App\Shop::where('user_id', $key)->first()->pick_up_point_id)))
                                                                <div class="col-12">
                                                                    <label class="d-flex align-items-center p-3 border rounded gry-bg c-pointer">
                                                                        <input type="radio" name="shipping_type_{{ $key }}" value="pickup_point" class="d-none" onchange="show_pickup_point(this)" data-target=".pickup_point_id_{{ $key }}">
                                                                        <span class="radio-box"></span>
                                                                        <span class="d-block ml-2 strong-600">
                                                                            {{  translate('Local Pickup') }}
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>

                                                    @if (\App\BusinessSetting::where('type', 'pickup_point')->first()->value == 1)
                                                        @if (is_array(json_decode(\App\Shop::where('user_id', $key)->first()->pick_up_point_id)))
                                                            <div class="mt-3 pickup_point_id_{{ $key }} d-none">
                                                                <select class="pickup-select form-control-lg w-100" name="pickup_point_id_{{ $key }}" data-placeholder="{{ translate('Select a pickup point') }}">
                                                                    <option>{{ translate('Select your nearest pickup point')}}</option>
                                                                    @foreach (json_decode(\App\Shop::where('user_id', $key)->first()->pick_up_point_id) as $pick_up_point)
                                                                        @if (\App\PickupPoint::find($pick_up_point) != null)
                                                                            <option value="{{ \App\PickupPoint::find($pick_up_point)->id }}" data-address="{{ \App\PickupPoint::find($pick_up_point)->address }}" data-phone="{{ \App\PickupPoint::find($pick_up_point)->phone }}">
                                                                                {{ \App\PickupPoint::find($pick_up_point)->name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            <!-- 22-01-2022 -->
                             <div class="card mb-3">
                                <div class="card-header  py-3">
                                    <h5 class="heading-6 mb-0">Select Delivery Option</h5>
                                    
                                </div>
                                    <?php
                                    
                                    $shipping_info = Session::get('shipping_info');
                                    $shipping_pincode = $shipping_info['postal_code'];
                                    $shortingHubId = App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $shipping_pincode . '"]\')')->pluck('id')->first();
                                

                                        use Carbon\Carbon;
                                        $currentTime = Carbon::now();
                                        $currentTime = date('H:i:s',strtotime($currentTime));
                                        $todayDate = date('d-M, Y');
                                        $tommorowDate = date('d-M, Y', strtotime(date('Y-m-d'). ' + 1 day'));


                                        $dairy = 0;
                                        $grocery = 0;

                                        foreach (Session::get('cart') as $key => $cartItem){
                                            $pro = App\Product::where('id',$cartItem['id'])->select('category_id','subcategory_id','subsubcategory_id')->first();
                                             
                                            if(isFreshInCategories($pro->category_id) || isFreshInSubCategories($pro->subcategory_id)){
                                                $dairy = 1;
                                            }else{
                                                $grocery = 1;
                                            }
                                            
                                        }
                                        

                                    ?>
                                    <div class="card-body">
                                        <div class="row">    
                                            <div class="col-md-8">
                                               <label>  <input type="radio" id="normal_delivery" name="delivery_type" value="normal" checked>
                                                <span style="font-size: small;"><strong>Normal Delivery: Within 24 Hrs. </strong></span> </label>
                                            </div>
                                           
                                            <div class="col-md-4">
                                                 <label><input type="radio" id="slotted_delivery" name="delivery_type" value="scheduled">
                                                <span style="font-size: small;"><strong> Scheduled Delivery</strong></span> </label>
                                            </div>
                                            
                                        </div>
                                    </div>
                                
                            </div>
                            
                            <div class="card mb-3" id="deliveryDateTime"  style = "display:none;">
                                <div class="card-header   py-3">
                                    <h5 class="heading-6 mb-0">Select Date & Time</h5>
                                    </div>
                                    <input type="hidden" id="grocery_incart" name="grocery_incart" value="{{$grocery}}">
                                    @if($grocery == 1)

                                        <div class="card-body" id="grocery_card" style="display: none;  padding-top: 10px;">

                                            <div class="row">                                         
                                               <div class="col-md-12" style="padding: 8px 12px;background-color: #d5dfef;height: 35px;text-align: left;color: white; margin-bottom: 20px;">
                                                    <h4 class="text-dark">GROCERY</h4>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @php
                                                $todaySlot = App\DeliverySlot::where('status','1')->where('shorting_hub_id',$shortingHubId)->where('cut_off','>',date('H:i:s',strtotime($currentTime)))->where('type',2)->orderBy('delivery_time', 'ASC')->get();
                                                @endphp 
                                                @if(count($todaySlot) == 0) 
                                                <input type="hidden" value="0" name="slot_flag" id="slot_flag">  
                                                <div class="col-md-4">
                                                    <label  ><input type="radio" id="today_slot_grocery" name="delivery_date_grocery" value="{{date('Y-m-d',strtotime($todayDate))}}" disabled>
                                                    <del>{{$todayDate}} (Today)</del></label>
                                                </div>
                                                
                                                @else
                                                <div class="col-md-4">
                                                   <label > <input type="radio" id="today_slot_grocery" name="delivery_date_grocery" value="{{date('Y-m-d',strtotime($todayDate))}}" checked>
                                                     <strong>{{$todayDate}} (Today)</strong></label>
                                                </div>

                                                @endif
                                                 <div class="col-md-6">
                                                   <label> <input type="radio" id="tommorow_slot_grocery" name="delivery_date_grocery" value="{{date('Y-m-d',strtotime($tommorowDate))}}" @if(count($todaySlot) == 0) checked="checked" @endif>
                                                     <strong> {{$tommorowDate}} (Tomorrow)</strong></label>
                                                </div>
                                            </div>
                                            
                                            <div class="row" id="today_avail_slot_grocery" style="display: none;">
                                                @if(count($todaySlot) != 0)  
                                                
                                                    @foreach($todaySlot as $key => $value)
                                                    <div class="col-md-3 pt-2 pt-md-3  col-sm-4 col-6 ">
                                                     <label class="d_time" >
                                                        <input @if($key == 0) checked="checked" @endif type="radio" class="delivery_slot_grocery" id="deliveryGrocerySlot_{{$key}}" name="delivery_slot_grocery" value="{{ dateFormatConvert($value['delivery_time']) }}">
                                                     <span> {{dateFormatConvert($value['delivery_time'])}}</span>
                                                     </label>
                                                    </div>
                                                    
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="row" id="tommorow_avail_slot_grocery" style="display: none;">
                                                @php
                                                $availSlotTom = App\DeliverySlot::where('status','1')->where('type',2)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();
                                                $i = 0;
                                                @endphp
                                                @if(count($availSlotTom) != 0)
                                                    
                                                @foreach($availSlotTom as $key => $value)

                                                <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6">
                                                    <label class="d_time">
                                                        <input @if($key == 0) checked="checked" @endif type="radio" class="delivery_slot_grocery_tom" id="deliveryGrocerySlotTom_{{$key}}" name="delivery_slot_grocery_tom" value="{{dateFormatConvert($value['delivery_time'])}}">
                                                    <span> {{dateFormatConvert($value['delivery_time'])}}</span>
                                                </label>
                                                </div>
                                                @endforeach 
                                                @endif
                                                
                                            </div>
                                        </div>
                                        @endif
                            <input type="hidden" id="fresh_incart" name="fresh_incart" value="{{$dairy}}">
                                    @if($dairy == 1)
                                    <div class="card-body" id="fresh_card" style="display: none;">
                                        
                                        <div class="row">
                                           <div class="col-md-12" style="padding: 8px 12px;background-color: #d5dfef;height: 35px;text-align: left;color: white; margin-bottom: 20px;">
                                                    <h4 class="text-dark">FRUITS, VEGETABLES & DAIRY</h4>
                                            </div>
                                        </div>
                                        <div class="row"> 
                                        @php
                                        $availSlot = App\DeliverySlot::where('status','1')->where('cut_off','>',date('H:i:s',strtotime($currentTime)))->where('type', 1)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();
                                        @endphp
                                        @if(count($availSlot) == 0)
                                        <input type="hidden" value="0" name="slot_flag_fresh" id="slot_flag_fresh">
                                            <div class="col-md-4">
                                                <input type="radio" id="today_slot_fresh" name="delivery_date_fresh" value="{{date('Y-m-d',strtotime($todayDate))}}" disabled>
                                                <label for="html"><del> {{$todayDate}} (Today)<del></label>
                                            </div>
                                            
                                        @else
                                            <div class="col-md-4">
                                               <label  > <input type="radio" id="today_slot_fresh" name="delivery_date_fresh" value="{{date('Y-m-d',strtotime($todayDate))}}" checked>
                                                 <strong> {{$todayDate}} (Today) </strong></label>
                                            </div>
                                        @endif
                                         <div class="col-md-6">
                                                <label  > <input type="radio" id="tommorow_slot_fresh" name="delivery_date_fresh" value="{{date('Y-m-d',strtotime($tommorowDate))}}" @if(count($availSlot) == 0) checked="checked" @endif>
                                                <strong>{{$tommorowDate}} (Tomorrow)</strong></label>
                                            </div>    
                                        </div>
                                        <div class="row" id="today_avail_slot_fresh" >
                                            <?php
                                            
                                            if(count($availSlot) != 0){
                                                foreach($availSlot as $key => $value){
                                                ?>
                                                <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6">
                                                    <label class="d_time"> 
                                                        <input @if($key == 0) checked="checked" @endif type="radio" class="delivery_slot_fresh" id="deliveryFreshSlotToday_{{$key}}" name="delivery_slot_fresh" value="<?php echo dateFormatConvert($value['delivery_time']);?> ">
                                                        <span> <?php echo dateFormatConvert($value['delivery_time']);?></span>
                                                    </label>
                                                </div>
                                                <?php }
                                            }
                                                
                                        ?>
                                        </div>
                                        <div class="row" id="tommorow_avail_slot_fresh" style="display: none;">
                                            <?php
                                            $availSlotTom = App\DeliverySlot::where('status',1)->where('type',1)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();
                                            if(count($availSlotTom) != 0){
                                            
                                                foreach($availSlotTom as $key => $value){    
                                                ?>
                                            <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6">
                                                <label class="d_time">  
                                                    <input @if($key == 0) checked="checked" @endif type="radio" class="delivery_slot_fresh_tom" id="deliveryFreshSlot_{{$key}}" name="delivery_slot_fresh_tom" value="<?php echo dateFormatConvert($value['delivery_time']);?>">
                                                    <span><?php echo dateFormatConvert($value['delivery_time']);?></span>
                                                </label>
                                            </div>
                                            <?php 
                                                }
                                             
                                            }
                                        ?>
                                        </div>
                                    </div>
                                    @endif
                                 
                            </div>
                            <!-- deliverySlot End -->
                            <!-- 18-10-2021 -->
                             <!--<div class="card mb-3">
                                <div class="card-header bg-white py-3">
                                    <h5 class="heading-6 mb-0">Delivery Slot</h5>
                                    <hr>
									<?php
										/* use Carbon\Carbon;
										$currentTime = Carbon::now();
										if(strtotime('05:00:00')> strtotime($currentTime) && strtotime('00:00:00')<strtotime($currentTime)){
											$slot = 'TODAY';
										}else{
											$slot = 'TOMORROW';
										}
										echo $slot; */
									?>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="radio" id="sevenToNine" name="delivery_slot" value="<?php //echo $slot;?> 7:00 AM - 9:00 AM" checked>
                                                <label for="html"> <?php //echo $slot;?> 7:00 AM - 9:00 AM</label>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="radio" id="nineToEleven" name="delivery_slot" value="<?php //echo $slot;?> 9:00 AM - 11:00 AM">
                                                <label for="html"> <?php //echo $slot;?> 9:00 AM - 11:00 AM</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->

                            <div class="row align-items-center pt-4">
                                <div class="col-md-6">
                                <a href="javascript:void(0)" onclick="window.history.back()" class="link link--style-3">
                                        <i class="ion-android-arrow-back"></i>
                                        {{ translate('Return Back')}}
                                    </a>
                                </div>
                               
                                <div class="col-md-6 text-right">
                                <button type="button" onclick="check_total()" id="process" class="btn btn-styled btn-base-1" @if($gift_flag==1 && $address_flag!=count($gift_cards) || !empty($buttonDisable)) disabled @endif>{{ translate('Continue to Payment')}}</button>
                                <!-- <button type="submit"id="continue" class="btn btn-styled btn-base-1" @if($gift_flag==1 && $address_flag!=count($gift_cards)) disabled @endif>{{ translate('Continue to Payment')}}</button>
                                 -->
                            </div>
                                
                            </div>
                        </form>
						<!-- <div class="row align-items-center pt-4">
							<div class="col-md-2"></div>
							<?php 
        //                         use Carbon\Carbon;
								// $currentTime = Carbon::now();
								// if(strtotime(date('H:i:s'))>=strtotime('18:00:00')){
								// $addDate = Carbon::now()->addDay(1);
								// 	$date = $addDate->toDateString();
								// }else{
								// 	$date = $currentTime->toDateString();
								// }
							?>
							<div class="col-md-8" style="color:#009245; text-align:center;">
                            <p>Your order will be delivered on <?php //echo date('D',strtotime($date))." ".date('d-m-Y',strtotime($date));?> between 07:00 AM and 08:00 PM.<br> Fresh Fruits, Dairy & Vegetables will be delivered separately between 07:00 AM and 11 AM.</p>
							</div>
							<div class="col-md-2"></div>
						</div> -->
                    </div>
                    <div class="col-lg-4 ml-lg-auto" id="cart-summary">
                        @include('frontend.partials.cart_summary')
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal fade" id="adress_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-zoom" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Recipient</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4 added-to-cart">
        <div class="row">
            <!--Hidden field -->
            <input type="hidden" id="product_id" />

            <div class="col-md-12">
                <div class="form-group">
                    <label>From*</label>
                    <input type="text" class="form-control" id="from" name="from" />
                </div>
                <div class="form-group">
                    <label>To*</label>
                    <input type="text" class="form-control" id="name" name="name" />
                </div>
                <div class="form-group">
                    <label>Email*</label>
                    <input type="email" class="form-control" id="email" name="email" />
                    <!-- <span style="color:red;font-size:12px;">Note*- Add (,) seprated emails</span> -->
                </div>
                <div class="form-group">
                    <label>Message*</label>
                    <textarea class="form-control" id="message" name="message"></textarea>
                </div>
                <!-- <div class="form-group">
                    <span style="color:red;font-size:12px;">Note*- 1 Gift Card To Each</span>
                </div> -->
            </div>
            <div class="col-md-12">
                                <strong class="alert alert-danger message" style="display:none;">{{ translate('Fill all required* fields')}}</strong>
                            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="saveAddress(this)"></i> Add Recipient</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


@endsection
@php 
$shipping = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
@endphp
@section('script')
    <script type="text/javascript">
        function display_option(key){

        }
        function show_pickup_point(el) {
        	var value = $(el).val();
        	var target = $(el).data('target');

            console.log(value);

        	if(value == 'home_delivery' || value == 'office_delivery'){
                if(!$(target).hasClass('d-none')){
                    $(target).addClass('d-none');
                }
        	}else{
        		$(target).removeClass('d-none');
        	}
        }

        function check_total()
        {
            var min_order_amount = parseInt("{{ env('MIN_ORDER_AMOUNT') }}");
            var free_shipping_amount = parseInt("{{ env('FREE_SHIPPING_AMOUNT') }}");
            var total = $("#total_amount").val();
            if(total<min_order_amount){
                Swal.fire({
                icon: 'info',
                title: 'Minimum order amount Rs. '+min_order_amount,
                // title: 'Orders Accepted above Rs. '+min_order_amount,
                // html:
                //     '<p style="text-align: justify; color:red;"><span>Note*</span><br><b>1- Below Rs. '+min_order_amount+' = Rs. {{ $shipping }} Delivery charge</b> <br /><b>2- Above Rs. '+free_shipping_amount+' Free Delivery<b></p>'
                // })
                html:
                    '<p style="text-align: center; color:red;"><br><b>1- Orders Accepted above Rs '+min_order_amount+' only.</b><br /><b>2- Above Rs. '+free_shipping_amount+' Free Delivery<b></p>'
                })
                //showFrontendAlert('info','Minimum order amount Rs. 250','abcdefgh');
            }
            else{
                $("#delivery_info_form").submit();
            }
        }

        function saveAddress(el){
            let from = $("#from").val();
            let name = $("#name").val();
            let email = $("#email").val();
            let message = $("#message").val();
            let product_id = $("#product_id").val();
            let address_count = $("#address_count").val();
            let total_cards = $("#count_gift_cards").val();
            if(from!="" && name!="" && email!="" && message!="" && product_id!=""){
            $.post("{{ route('gift_card.save-address') }}",{_token:"{{ csrf_token() }}",from:from,name:name,to:email,message:message,product_id:product_id},function(data){
                $('#adress_modal').modal('hide');
                if(data==1)
                {
                    if(address_count!=total_cards){
                        address_count++;
                    }
                    $("#address_count").val(address_count);
                    if(address_count==total_cards){
                        $("#continue").removeAttr('disabled');
                    }
                    $("#add-recipient"+product_id).text('Edit Recipient');
                    $(".invalid-feedback"+product_id).hide();
                    $("#address"+product_id).html("<div class='card float-right'><div class='card-body'>\
                                                                <h5 class='card-title'>Recipient Details</h5>\
                                                                <strong>Message:</strong> &nbsp;<span class='c-gray-light' id='data-rec-message"+product_id+"' data-rec-message='"+message+"'>"+message+"</span>\
                                                                <p></p>\
                                                                <strong>From:</strong> &nbsp;<span class='c-gray-light' id='data-rec-from"+product_id+"' data-rec-from='"+from+"'>"+from+"</span>\
                                                                <p></p>\
                                                                <strong>Send To:</strong> &nbsp;<span class='c-gray-light' id='data-rec-to"+product_id+"' data-rec-to='"+email+"'>"+email+"</span>\
                                                                <p></p>\
                                                                <strong>Name:</strong>&nbsp;<span class='c-gray-light' id='data-rec-name"+product_id+"' data-rec-name='"+name+"'>"+name+"</span>\
                                                                </div></div>");
                        
                }
                else{
                    $("#address"+product_id).append("<span>Something went wrong</span>");
                }
                
            });
            }
            else{
                $(".message").css('display','block');
                
                return false;
            }
        }


       function showModal(product_id){
        $("#product_id").val(product_id);
        $("#name").val($("#data-rec-name"+product_id).attr('data-rec-name'));
        $("#from").val($("#data-rec-from"+product_id).attr('data-rec-from'));
        $("#email").val($("#data-rec-to"+product_id).attr('data-rec-to'));
        $("#message").val($("#data-rec-message"+product_id).attr('data-rec-message'));
       }

       function updateQty(id){
        let qty = parseInt($("#qty"+id).val());
        let key = parseInt($("#key"+id).val());
        let max_purchase_limit = parseInt($("#max_purchase_limit"+id).val());

        $("#message"+id).html("<div class='spinner-border spinner-border-sm text-success' role='status'>\
                <span class='sr-only'>Loading...</span>\
                </div>");

        if(max_purchase_limit < qty){
            $("#message"+id).html("<b style='color:red;'>Maximum purchase limit has been reached</b>");
            return false;
        }
        if(qty<=0){
            $("#message"+id).html("<b style='color:red;'>Quantity can`t be 0</b>");
            return false;
        }

        $.post("{{ route('cart.updateQuantity') }}",{_token:"{{ csrf_token() }}",quantity:qty,key:key,delivery_page:1},function(data){
            $("#cart-summary").html(data);
            $("#message"+id).html("<b style='color:green;'>Quantity updated</b>");
        });
       }

       function updateQtyForm(id){
           $("#updateQty"+id).toggle();
       }

       $( document ).ready(function() {

            var is_fresh = $('#is-fresh').val();
            var is_grocery = $('#is-grocery').val();
            
            if(is_fresh == 1){
                $('#fresh_card').show();
            }else{
                $('#fresh_card').hide();
            }

            if(is_grocery == 1){
                $('#grocery_card').show();
            }else{
                $('#grocery_card').hide();
            }

            $("#normal_delivery").click(function(){
                $('#deliveryDateTime').hide();
                $('#today_avail_slot').hide();
                $('#tommorow_avail_slot').hide();
            });

            $("#slotted_delivery").click(function(){
                $('#deliveryDateTime').show();
                $('#today_avail_slot_grocery').show();
                $('#tommorow_avail_slot_grocery').hide();
                $('#today_avail_slot_fresh').show();
                $('#tommorow_avail_slot_fresh').hide();
                $('.delivery_slot_fresh_tom').prop('checked', false);
                $('.delivery_slot_grocery_tom').prop('checked', false);
                var flag = $('#slot_flag').val();
                if(flag == 0){
                    $('#tommorow_avail_slot_grocery').show();
                    $('#deliveryGrocerySlotTom_0').prop('checked', true);
                }

                var slot_flag_fresh = $('#slot_flag_fresh').val();
                if(slot_flag_fresh == 0){
                    $('#tommorow_avail_slot_fresh').show();
                    $('#deliveryFreshSlot_0').prop('checked', true);
                }
            });

            $('#today_slot_grocery').click(function(){
                $('#today_avail_slot_grocery').show();
                $('#tommorow_avail_slot_grocery').hide();
                $('.delivery_slot_grocery_tom').prop('checked', false);
                $('#deliveryGrocerySlot_0').prop('checked', true);
            });

            $('#tommorow_slot_grocery').click(function(){
                $('#tommorow_avail_slot_grocery').show();
                $('#today_avail_slot_grocery').hide();
                $('.delivery_slot_grocery').prop('checked', false);
                $('#deliveryGrocerySlotTom_0').prop('checked', true);
            });

            $('#today_slot_fresh').click(function(){
                $('#today_avail_slot_fresh').show();
                $('#tommorow_avail_slot_fresh').hide();
                $('.delivery_slot_fresh_tom').prop('checked', false);
                $('#deliveryFreshSlotToday_0').prop('checked', true);
            });

            $('#tommorow_slot_fresh').click(function(){
                $('#tommorow_avail_slot_fresh').show();
                $('#today_avail_slot_fresh').hide();
                $('.delivery_slot_fresh').prop('checked', false);
                $('#deliveryFreshSlot_0').prop('checked', true);
            });
        });
    </script>
@endsection
