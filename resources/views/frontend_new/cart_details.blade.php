
@extends('frontend_new.layouts.app')
@section('content')
<section class="slice-xs sct-color-2 border-bottom">
            <div class="container container-sm">
                <div class="row cols-delimited justify-content-center">
                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon mb-0">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">1. My Cart</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="fa fa-map"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">2. Shipping info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon mb-0 c-gray-light">
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
                    <!-- left section -->
                    <div class="col-xl-8">
                        <div class="bg-white p-4"  >
                            <div class="scroll-bar table-responsive">
                              <table class="table-cart border-bottom">
                                    <thead>
                                        <tr>
                                            <th class="product-select"><input type="checkbox" id="selectall" onclick="selectAll(this)"> Select all</th>
                                            <th class="product-image"></th>
                                            <th class="product-name">Product</th>
                                            <th class="product-price d-lg-table-cell">Price</th>
                                            <th class="product-quanity d-md-table-cell">Quantity</th>
                                            <th class="product-total">Total</th>
                                            <th class="product-remove"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
               
                                        @foreach($items as $key => $cart)
                                        @php 
                                            $cid = $cart->id.$cart->variation.$cart->product_id;
                                            $is_proceed = true;
                                            if($cart->in_stock==false){
                                                $is_proceed = false;
                                            }
                                        @endphp
                                          <tr class="cart-item cart" id="row{{$cart->id}}">
                                                <td class="product-select">
                                                    <input type="checkbox" class="form-control selectedId" onclick="singleCheck()" name="" value=""  >
                                                </td>
                                                <td class="product-image">
                                                    <a href="{{ route('product.details',['id'=>encrypt($cart->product_id)]) }}" class="mr-3">
                                                        
                                                        <img loading="lazy" src="{{ Storage::disk('s3')->url($cart->product->image) }}">
                                                   </a> 
                                                </td>
                                                <td class="product-name">
                                                    <span class="pr-4 d-block">{{ translate($cart->product->name) }}

                                                    <br />
                                                    @if(!$cart->in_stock)
                                                        <span class="badge badge-danger">Out Of Stock</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                @php 
                                                $price = $cart->price;
                                                if(Cookie::has('peer')){
                                                    $price = $cart->peer_discount;
                                                }
                                                
                                                @endphp
                                                <td class="product-price d-lg-table-cell">
                                                        <span class="product-price strong-600" id="p-price{{$cid}}" p-price{{$cid}}="{{$cart->peer_discount}}">
                                                          {{ single_price($price) }}
                                                        </span>
                                                        @if(Cookie::has('peer'))
                                                        <del class="old-product-price strong-400">{{ single_price($cart->price) }}</del>
                                                        @endif
                                                </td>
                                            <input type="hidden" name="max_purchase_qty{{$cid}}" value="{{ $cart->max_purchase_qty }}" id="max_purchase_qty{{$cid}}" />
                                                <td class="product-quantity d-md-table-cell">
                                                   <div class="input-group input-group--style-2 pr-4"  >
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-number minus-button" type="button" data-type="minus" data-field="pqty{{ $cid }}">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            </span>
                                                            <input type="text" name="pqty{{ $cid }}" id="pqty{{ $cid }}" class="form-control h-auto input-number" dcid="{{$cart->id}}" page-cart="{{$cid}}" onchange="updateCart('{{$cid}}')" placeholder="1" value="{{ $cart->quantity }}" min="1" max="{{ $cart->max_purchase_qty }}"   readonly="">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-number plus-button" type="button" data-type="plus" data-field="pqty{{ $cid }}">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                 </td>
                                                
                                                <td class="product-total">
                                                   <span id="total-price{{$cid}}">{{ single_price($cart->peer_discount*$cart->quantity) }}</span>
                                                </td>
                                                <td class="product-remove">
                                                    <a href="javascript:void(0)" id="removebtn{{$cart->id}}" onclick="removeFromCart('{{ $cart->id }}')" page-cart="{{$cid}}" class="pl-4">
                                                        <i class="fa fa-trash" ></i>
                                                    </a>
                                                </td>

                                                
                                         </tr>
                                         
                                         
                                          @endforeach

                                    </tbody>
                                </table>
                             </div>
                                <div class="row align-items-center pt-4 cart_bottom pb-4">
                                    <div class="col-md-6 col-sm-6 col-6">
                                        <a href="{{ route('home') }}" class="link link--style-3">
                                            <i class="fa fa-arrow-left"></i>
                                            Return to shops
                                        </a>
                                    </div>
                                     <div class="col-md-6 text-right col-sm-6 col-6">
                                        <button type="button" class="btn btn-lg remove_btn btn-danger" onclick="removeMultipleProduct()">Remove</button>
                                       
                                        <button class="btn btn-styled btn-base-1" onclick="proceedToShipping()" @if($is_proceed==false) disabled @endif>Continue to Shipping</button>
                                    </div>
                                </div>
                             
                        </div>
                    </div>


                    <!-- right sidebar  -->
                
                    <div class="col-xl-4 ml-lg-auto" id="cart-summary">
                      
                  </div>
                </div>
                 
            </div>
        </section>
        @endsection
        @section('script')
            <script>
                $(document).ready(function(){
                    loadCartSummary();
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
                    window.location.href="{{ route('userapi.login',['checkout']) }}";
                }
            }
            </script>
        @endsection