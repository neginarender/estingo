@extends('frontend.layouts.app')

@section('content')


    @php
        //$status = $order->orderDetails->first()->delivery_status;
        $status = $order->order_status;
    @endphp
    <style>
        @media(max-width: 640px){ .search-box-mob .search .nav-search-box {margin-top: -5px}
        .referral {margin-top: 5px;}}
       
    /*.referral{margin-top:10px}*/
    /*.sm-fixed-top {
            top: -117px !important;
        }*/
    /*.navbar-light .navbar-nav .btn, .referral .btn{bottom:20px} .nav-search-box{margin-top:-15px}*/
    /*@media(max-width:640px){
        .search-box-mob {
       margin-top: 41px;
            }
            .sm-fixed-top .search-box-mob {
                margin-top: 41px;
            }
            .sm-fixed-top {
                top: -160px !important;
            }
    }
    @media (max-width: 800px)
    {
        .search-box-mob {
    margin-top: 41px;
}
.sm-fixed-top .search-box-mob {
    margin-top: 41px;
}
    }*/

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
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">1. {{ translate('My Cart')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="la la-map-o"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">2. {{ translate('Shipping info')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="la la-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">3. {{ translate('Delivery info')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="la la-credit-card"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">4. {{ translate('Payment')}}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon mb-0">
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
        <section class="py-4">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center py-4 border-bottom mb-4">
                                    <i class="la la-check-circle la-3x text-success mb-3"></i>
                                    <h1 class="h3 mb-3">{{ translate('Thank You for Your Order!')}}</h1>
                                    <h2 class="h5 strong-700" >{{ translate('Order Code:')}} {{ $order->code }}</h2>

                                    <input type="hidden" id = "order_code" name = "ord_code" value = {{$order->code}}>
                                    
                                    @if(json_decode($order->shipping_address)->email!='')
                                        <p class="text-muted text-italic">{{  translate('A copy of your order summary has been sent to') }} {{ json_decode($order->shipping_address)->email }}</p>
                                    @endif 
                                </div>
                                <div class="mb-4">
                                    <h5 class="strong-600 mb-3 border-bottom pb-2">{{ translate('Order Summary')}}</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="details-table table">
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Order Code')}}:</td>
                                                    <td>{{ $order->code }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Name')}}:</td>
                                                    <td>{{ json_decode($order->shipping_address)->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Email')}}:</td>
                                                    <td>{{ json_decode($order->shipping_address)->email }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Shipping address')}}:</td>
                                                    <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->country }}</td>
                                                </tr>
                                                <!-- 18-10-2021 -->
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Delivery Timing')}}:</td>
                                                    <tr>
                                                    
                                                    @php
                                                    $schedule = App\SubOrder::where('order_id',$order->id)->where('status',1)->get();
                                                    @endphp
                                                    @foreach($schedule as $key => $value)
                                                        <td><h6 style="color:#4183c4">{{strtoupper($value->delivery_name)}}</h6>
                                                        <span>Type: <strong>{{ucfirst($value->delivery_type)}}</strong></span><br>
                                                        @if($value->delivery_type == 'scheduled')
                                                            <span>Date: <strong>{{date('d M, Y',strtotime($value->delivery_date))}}</strong></span><br>
                                                            <span>Schedule: <strong>{{$value->delivery_time}}</strong></span>
                                                        @endif
                                                    </td>
                                                    @endforeach
                                                    </tr>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="details-table table">
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Order date')}}:</td>
                                                    <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Order status')}}:</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', @$status)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Total order amount')}}:</td>
                                                    @php 
                                                            $grand_total = $order->grand_total;
                                                            if($order->payment_type=="razorpay" && !empty($order->wallet_amount))
                                                            {
                                                                $grand_total = $order->grand_total+$order->wallet_amount;
                                                            }
                                                            @endphp
                                                      @if($order->payment_type == 'wallet')
                                                        <td>{{ single_price($order->wallet_amount) }}</td>

                                                      @else
                                                        <td>{{ single_price($grand_total) }}</td>
                                                      @endif        
                                                    
                                                </tr>
                                                <!-- <tr>
                                                    <td class="w-50 strong-600">{{ translate('Shipping')}}:</td>
                                                    <td>{{ translate('Flat shipping rate')}}</td>
                                                </tr> -->
                                                <tr>
                                                    <td class="w-50 strong-600">{{ translate('Payment method')}}:</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="strong-600 mb-3 border-bottom pb-2">{{ translate('Order Details')}}</h5>
                                    <div>
                                        <table class="details-table table table-sm table-hover table-responsive-md">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th width="30%">{{ translate('Product')}}</th>
                                                    <th style="text-align: center;">{{ translate('Variation')}}</th>
                                                    <th style="text-align: center;">{{ translate('Quantity')}}</th>
                                                    <th>{{ translate('Delivery Type')}}</th>
                                                    <th class="text-right">{{ translate('Price')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->orderDetails as $key => $orderDetail)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>
                                                            @if ($orderDetail->product != null)
                                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">
                                                                    {{ $orderDetail->product->name }}
                                                                </a>
                                                            @else
                                                                <strong>{{  translate('Product Unavailable') }}</strong>
                                                            @endif
                                                        </td>
                                                        <td style="text-align: center;">
                                                            {{ $orderDetail->variation }}
                                                        </td>
                                                        <td style="text-align: center;">
                                                            {{ $orderDetail->quantity }}
                                                        </td>
                                                        <td>
                                                            @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                                                {{  translate('Home Delivery') }}
                                                            @elseif ($orderDetail->shipping_type == 'pickup_point')
                                                                @if ($orderDetail->pickup_point != null)
                                                                    {{ $orderDetail->pickup_point->name }} ({{ translate('Pickip Point') }})
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="text-right">{{ single_price($orderDetail->price) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-5 col-md-6 ml-auto">
                                            <table class="table details-table">
                                                <tbody>
                                                    <tr>
                                                        <th>{{ translate('Total Value')}}</th>
                                                        <td class="text-right">
                                                            <span class="strong-600">{{ single_price($order->orderDetails->sum('price')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ translate('Delivery Charges')}}</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>{{ translate('Total Tax')}}</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <th>{{ translate('Coupon Discount')}}</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($order->coupon_discount) }}</span>
                                                        </td>
                                                    </tr>-->

                                                     <tr>
                                                        <th><span style="color: green;font-weight: bold">{{translate('Total Savings')}}</span></th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{single_price($order->referal_discount) }}</span>
                                                        </td>
                                                    </tr>
                                                
                                                   

                                                    @if($order->payment_type != 'wallet')
                                                        <tr>
                                                            <th>{{ translate('Amount Paid from Wallet') }}</th>
                                                            <td class="text-right"><span class="text-italic">{{ single_price($order->wallet_amount) }}</span></td>
                                                        </tr>
                                                   

                                                    <tr>
                                                        <th><span class="strong-600" style="font-weight: bold">{{ translate('Total Amount Payable')}}</span></th>
                                                        <td class="text-right">
                                                            <strong><span>{{ single_price($order->grand_total) }}</span></strong>
                                                            <input type="hidden" id = "order_value" name = "ord_value" value = {{$order->grand_total}}>
                                                        </td>
                                                    </tr>
                                                     @endif
                                                    @if($order->payment_type == 'wallet')
                                                    <tr>
                                                        <th>{{ translate('Amount Paid from Wallet')}}</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price($order->wallet_amount) }}</span>
                                                        </td>
                                                    </tr>
                                                     <tr>
                                                        <th style="font-weight: bold">{{ translate('Total Amount Payable')}}</th>
                                                        <td class="text-right">
                                                            <span class="text-italic" style="font-weight: bold">{{ single_price($order->wallet_amount) }}</span>
                                                        </td>
                                                    </tr>

                                                     <tr>
                                                        <th>{{ translate('Payable Amount')}}</th>
                                                        <td class="text-right">
                                                            <span class="text-italic">{{ single_price(0) }}</span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    
                                                    <tr><th></th><td class="text-right">Inclusive of all Taxes</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

<script>
  window.addEventListener('load',function(){
    var code = $('#order_code').val();
    var value = $('#order_value').val();
    
    
if(window.location.pathname.indexOf('/checkout/order-confirmed')!=-1){
  gtag('event', 'conversion', {
      'send_to': 'AW-583827208/08_XCIW_09gCEIj-sZYC',
      'value': value,
      'currency': 'INR',
      'transaction_id':code
  });
}
  });
  
</script>

<script type = "text/javascript" >
  function changeHashOnLoad() {
 window.location.href += "#";
 setTimeout("changeHashAgain()", "50"); 
 }

function changeHashAgain() {
window.location.href += "1";
}

var storedHash = window.location.hash;
 window.setInterval(function () {
 if (window.location.hash != storedHash) {
     window.location.hash = storedHash;
}
}, 50);


</script>
