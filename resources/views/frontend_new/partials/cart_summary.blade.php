<div class="card sticky-top">
                        <div class="card-title py-3">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <h3 class="heading heading-3 strong-400 mb-0">
                                        <span>Summary</span>
                                    </h3>
                                </div>
                                  <div class="col-6 text-right">
                                    <span class="badge badge-md badge-danger">{{ count($items) }} Items</span>
                                 </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table-cart table-cart-review">
                                <thead>
                                    <tr>
                                        <th class="product-name">Product</th>
                                        <th class="product-total text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php 
                                    $mrp = 0;
                                    $shipping_cost = 0;
                                    $tax= 0;
                                    $total_discount = 0; 
                                    $grand_total = 0; 
                                    $is_fresh = 0;
                                    $is_grocery = 0;
                                    $total_amount = 0;
                                    $total_discount = 0;
                                    $total_shipping = 0;
                                @endphp
                                @foreach($items as $cart)
                                @php 
                                    
                                    $mrp += $cart->price*$cart->quantity;
                                    $shipping_cost+=$cart->shipping_cost;
                                    $tax +=$cart->tax;
                                    $total_discount +=$cart->discount;
                                    $grand_total+=(($cart->peer_discount*$cart->quantity)+$cart->shipping_cost);
                                    $product = \App\Product::find($cart->product_id);
                                    if(isFreshInCategories($product->category_id) || isFreshInSubCategories($product->subcategory_id)){
                                        $is_fresh++;
                                    }
                                    else{
                                        $is_grocery++;
                                    }
                                @endphp
                                    <tr class="cart_item">
                                        <td class="product-name">
                                            {{ translate($cart->product->name) }} - 
                                            <span class="product-quantity">{{ $cart->variation }}× {{ $cart->quantity }}</span>
                                        </td>
                                        <td class="product-total text-right">
                                            <span class="pl-4">{{ single_price($cart->peer_discount) }}</span>
                                                                
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                    @php 
                                    session()->put('total_saving',$total_discount); 
                                    session()->put('grand_total',$grand_total);
                                    @endphp
                                    <input type="hidden" name="is_fresh" value="{{ $is_fresh }}" id="is_fresh" />
                                    <input type="hidden" name="is_grocery" value="{{ $is_grocery }}" id="is_grocery" />
                                    <input type="hidden" name="total_amount" id="total_amount" value="{{ $grand_total }}" />
                                </tbody>
                                <tfoot>
                                    <tr class="cart-subtotal">
                                        <th>MRP</th>
                                        <td class="text-right">
                                           <span class="strong-600">{{ single_price($mrp) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="cart-shipping">
                                        <th>Delivery Charges</th>
                                        <td class="text-right">
                                            <span class="text-italic">{{ single_price($shipping_cost) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="cart-shipping">
                                        <th>Tax</th>
                                        <td class="text-right">
                                            <span class="text-italic">{{ single_price($tax) }}</span>
                                        </td>
                                    </tr>

                                    <tr class="cart-shipping">
                                        <th>Total Saving</th>
                                        <td class="text-right">
                                            <span class="text-italic">{{ single_price($total_discount) }}</span>
                                        </td>
                                    </tr>

                                    
                                       <tr class="cart-total">
                                           <th><span class="strong-600">Total Amount Payable</span></th>
                                            <td class="text-right">
                                                 <span class="strong-700"><strong>{{ single_price($grand_total) }}</strong></span>
                                           </td>
                                        </tr>
                                        <tr> <td colspan="2" class="text-right">Inclusive of all Taxes</td></tr>
                                </tfoot>
                            </table>

                            <!-- <p  class="p-coupon"> Enter your peer code here to avail amazing discounts on your order </p>
                            <p  class="p-coupon mt-0">Don't have referral code? <a id="click_here" href="javascript:void(0)" class="apply"  > Click Here </a> </p>
                            <div class="coupon_box" id="code_box">
                                <span id="code" class="code_box" value="ROZANA7">ROZANA7</span>
                                <button id="copy_code" name="copy_code"  class="btn btn-styled btn-base-1">APPLY CODE</button>
                            </div> -->
                        </div>
                    </div>