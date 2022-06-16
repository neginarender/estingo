@if(count($items))
<li>
<div class="dropdown-cart px-0">

        <div class="dc-header">
            <h3 class="heading heading-6 strong-700">{{translate('Cart Items')}}</h3>
        </div>
        <input type="hidden" name="cart" id="cart" value="{{ count($items) }}" />
        <div class="dropdown-cart-items c-scrollbar">
                @php 
                  $mrp = 0;
                  $shipping_cost = 0;
                  $tax= 0;
                  $total_discount = 0; 
                  $grand_total = 0;
                  $cid = 0;
                @endphp
                @foreach($items as $cart)
                @php 
                   
                    $mrp += $cart->price*$cart->quantity;
                    $shipping_cost+=$cart->shipping_cost;
                    $tax +=$cart->tax;
                    $total_discount +=$cart->discount;
                    $grand_total+=(($cart->peer_discount*$cart->quantity)+$cart->shipping_cost);
                @endphp
                <div class="dc-item">
                    <div class="d-flex align-items-center">
                        <div class="dc-image">
                            <a href="#">
                                <img src="{{ static_asset('frontend/images/placeholder.jpg') }}" data-src="{{ Storage::disk('s3')->url($cart->product->image) }}" class="img-fluid lazyload" alt="Product Name">
                            </a>
                        </div>
                        <div class="dc-content">
                            <span class="d-block dc-product-name text-capitalize strong-600 mb-1">
                                <a href="#">
                                    {{ translate($cart->product->name) }}
                                </a>
                            </span>

                            <span class="dc-quantity">x{{ $cart->quantity }}</span>
                            
                            <span class="dc-price">{{ single_price($cart->peer_discount) }}</span>
                            @if(Cookie::has('peer'))
                            <del class="old-product-price dc-price">{{ single_price($cart->price) }}</</del>
                            @endif
                                                

                        </div>
                        <div class="dc-actions">
                            <button onclick="removeFromCart('{{ $cart->id }}')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
        </div>
        <div class="dc-item py-3">
            <span class="subtotal-text">{{translate('MRP')}}</span>
            <span class="subtotal-amount">{{ single_price($mrp) }}</span>  
            <br />
            <span class="subtotal-text">{{translate('Shipping Chages')}}</span>
            <span class="subtotal-amount">{{ single_price($shipping_cost) }}</span>  
            <br />
            <span class="subtotal-text">{{translate('Tax')}}</span>
            <span class="subtotal-amount">{{ single_price($tax) }}</span>  
            <br />
            <span class="subtotal-text">{{translate('Total discount')}}</span>
            <span class="subtotal-amount">{{ single_price($total_discount) }}</span> 
            <br /> 
            <hr />
            <span class="subtotal-text">{{translate('Grand Total')}}</span>
            <span class="subtotal-amount">{{ single_price($grand_total) }}</span> 
            
        </div>
        
        <div class="py-2 text-center dc-btn">
            <ul class="inline-links inline-links--style-3">
                <li class="px-1">
                    <a href="{{ route('cart.details') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1">
                        <i class="fa fa-shopping-cart"></i> {{translate('View cart')}}
                    </a>
                </li>
                @if(session()->has('user_id'))
                <li class="px-1">
                    <a href="{{ route('phoneapi.shipping_info') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1 light-text">
                        <i class="fa fa-mail-forward"></i> {{translate('Checkout')}}
                    </a>
                </li>
                @endif
                
            </ul>
        </div>
    

</div>
</li>
@else

<li>
    <div class="dropdown-cart px-0">
        <div class="dc-header">
            <h3 class="heading heading-6 strong-700 loaded">Your Cart is empty</h3>
        </div>
    </div>
</li>   
@endif