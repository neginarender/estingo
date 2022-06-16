
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
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">4. Payment</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active">
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

         <section class="py-4  ">
            <div class="container">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-8 mx-auto">
                        <div class="  py-4 border-bottom mb-4">
                            <div class="card p-2 p-md-5">
                                   <div class="text-center"> 
                                    <i class="fa fa-check-circle fa-3x text-success mb-0"></i>
                                    <h1 class="h3 mb-3">Thank You for Your Order!</h1>
                                    <h2 class="h5 strong-700">Order Code: {{ $order->code }}</h2>
                                      <p class="text-muted text-italic">A copy of your order summary has been sent to {{ $order->shipping_address->email}}</p>
                                  </div>

                                  <div class="mb-4 mt-4">
                                    <h5 class="strong-600 mb-3 border-bottom pb-2 text-left">Order Summary</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                                <table class="details-table table text-left">
                                                    <tbody>
                                                        <tr>
                                                        <td class="w-50 strong-600">Order Code:</td>
                                                        <td>{{ $order->code }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="w-50 strong-600">Name:</td>
                                                            <td>{{ $order->shipping_address->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="w-50 strong-600">Email:</td>
                                                            <td>{{ $order->shipping_address->email }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="w-50 strong-600">Shipping address:</td>
                                                            <td>{{ $order->shipping_address->address }}</td>
                                                        </tr>
                                                        <tr>
                                                          <td class="w-50 strong-600">Delivery Timing:</td>
                                                        </tr>
                                                        <tr>
                                                           
                                                            <td colspan="2"><h6><span class="d-block"><strong>Order will be delivered with in 24 Hrs</strong></span></h6></td>
                                                           
                                                        </tr>
                                                 </tbody>
                                              </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="details-table table text-left">
                                                <tbody><tr>
                                                    <td class="w-50 strong-600">Order date:</td>
                                                    <td>{{ $order->date }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">Order status:</td>
                                                    <td>{{ $order->order_status }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">Total order amount:</td>
                                                    <td>{{ single_price($order->grand_total) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">Payment method:</td>
                                                    <td>{{ str_replace('_',' ',$order->payment_type) }}</td>
                                                </tr>
                                            </tbody></table>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <h5 class="strong-600 mb-3 border-bottom pb-2">Order Details</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="details-table table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th width="30%">Product</th>
                                                    <th style="text-align: center;">Variation</th>
                                                    <th style="text-align: center;">Quantity</th>
                                                    <th>Delivery Type</th>
                                                    <th class="text-right">Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $tax = 0; 
                                                $mrp=0; 
                                                @endphp
                                                @foreach($order->details as $key => $order_details)
                                                @php 
                                                $tax = $order_details->tax;
                                                $mrp+=$order_details->price;
                                                @endphp
                                                <tr>
                                                  <td>{{ $key+1 }}</td>
                                                  <td>
                                                       <a href="{{ route('product.details',['id'=>encrypt($order_details->product_id)]) }}" target="_blank"> {{ $order_details->name }}</a>
                                                  </td>
                                                  <td style="text-align: center;">
                                                       {{ $order_details->variation }}
                                                   </td>
                                                    <td style="text-align: center;">
                                                        {{ $order_details->quantity }}
                                                    </td>
                                                    <td>Home Delivery</td>
                                                    <td class="text-right">{{ single_price($order_details->price) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                               </div>
                               <div class="row">
                                        <div class="col-xl-5 col-md-6 ml-auto">
                                            <table class="table details-table">
                                                <tbody>
                                                    <tr>
                                                        <th>Total Value</th>
                                                        <td class="text-right">
                                                            <span class="strong-600">{{ single_price($mrp) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Delivery Charges</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($order->shipping_cost) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Tax</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($tax) }}</span>
                                                        </td>
                                                    </tr>
                                                    
                                                     <tr>
                                                        <th><span style="color: green;font-weight: bold">Total Savings</span></th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($order->discount) }}</span>
                                                        </td>
                                                    </tr>
                                                   
                                                       <tr>
                                                            <th>Amount Paid from Wallet</th>
                                                            <td class="text-right"><span class="text-italic">{{ single_price($order->wallet_amount) }}</span></td>
                                                        </tr>
                                                   
                                                    <tr>
                                                        <th><span class="strong-600" style="font-weight: bold">Total Amount Payable</span></th>
                                                        <td class="text-right">
                                                            <strong><span>{{ single_price($order->grand_total) }}</span></strong>
                                                        </td>
                                                    </tr>
                                                      
                                                                                                        
                                                    <tr><th></th><td class="text-right"> Inclusive of all Taxes</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                             
                        </div>
                    </div>
                   
                </div>
            </div>
        </section>      
@endsection

@section('script')
    
@endsection