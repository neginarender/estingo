<div class="modal-header">
    <h5 class="modal-title strong-600 heading-5">{{ translate('Order id')}}: {{ $order->code }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

@php
    $status = $order->order_status;//$order->orderDetails->first()->delivery_status;
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp

<div class="modal-body gry-bg px-3 pt-0">
    <div class="pt-4">
        <ul class="process-steps clearfix">
            <li @if($status == 'pending') class="active" @else class="done" @endif>
                <div class="icon">1</div>
                <div class="title">{{ translate('Order placed')}}</div>
            </li>
            <li @if($status=='in_process') class="active" @elseif($status == 'on_review') class="done" @elseif($status == 'on_delivery' || $status=='partially_delivered' || $status == 'delivered') class="done" @endif>
                <div class="icon">2</div>
                <div class="title">{{ translate('In Process')}}</div>
            </li>
            <li @if($status == 'on_review') class="active" @elseif($status == 'on_delivery' || $status == 'partially_delivered' || $status == 'delivered') class="done" @endif>
                <div class="icon">3</div>
                <div class="title">{{ translate('On review')}}</div>
            </li>
            <li @if($status == 'on_delivery') class="active" @elseif($status== 'partially_delivered') class="done" @elseif($status == 'delivered') class="done" @endif>
                <div class="icon">4</div>
                <div class="title">{{ translate('On delivery')}}</div>
            </li>
            <li @if($status == 'partially_delivered') class="active" @elseif($status == 'delivered') class="done" @endif>
                <div class="icon">5</div>
                <div class="title">{{ translate('Partially Delivered')}}</div>
            </li>
            <li @if($status == 'delivered') class="done" @endif>
                <div class="icon">6</div>
                <div class="title">{{ translate('Delivered')}}</div>
            </li>
        </ul>
    </div>
    <div class="card mt-4">
        <div class="card-header py-2 px-3 heading-6 strong-600 clearfix">
            <div class="float-left">{{ translate('Order Summary')}}</div>
        </div>
        <div class="card-body pb-0">
            <div class="row">
                <div class="col-lg-6">
                    <table class="details-table table">
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Order Code')}}:</td>
                            <td>{{ $order->code }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Customer')}}:</td>
                            <td>{{ json_decode($order->shipping_address)->name }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Email')}}:</td>
                            @if ($order->user_id != null)
                                <td>{{ $order->user->email }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Shipping address')}}:</td>
                            <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->postal_code }}, {{ json_decode($order->shipping_address)->country }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="details-table table">
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Order date')}}:</td>
                            <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Order status')}}:</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                        </tr>
                        @php 
                                                            $grand_total = $order->grand_total;
                                                            if($order->payment_type=="razorpay" && !empty($order->wallet_amount))
                                                            {
                                                                $grand_total = $order->grand_total+$order->wallet_amount;
                                                               
                                                            }elseif($order->payment_type=="wallet"){
                                                                $grand_total = $order->wallet_amount;

                                                            }

                                                            @endphp
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Total order amount')}}:</td>
                            {{-- <td>{{ single_price($grand_total) }}</td> --}}
                            <td>{{ single_price($grand_total) }}</td>
                        </tr>
                        @if($grand_total < 1500)
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Shipping method')}}:</td>
                            <td>{{ translate('Flat shipping rate')}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="w-50 strong-600">{{ translate('Payment method')}}:</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-9">
            <div class="card mt-4">
                <div class="card-header py-2 px-3 heading-6 strong-600">{{ translate('Order Details')}}</div>
                <div class="card-body pb-0">
                    <table class="details-table table table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th width="30%">{{ translate('Product')}}</th>
                                <th>{{ translate('Variation')}}</th>
                                <th>{{ translate('Quantity')}}</th>
                               <!--  <th>{{ translate('Delivery Type')}}</th> -->
                                <th>{{ translate('Price')}}</th>
                                @if(!empty($order->referal_discount))
                                <th>{{ translate('Discounted Price')}}</th>
                                @endif
                                <th>{{ translate('Order Status')}}</th>
                                @if($order->payment_type == "cash_on_delivery")
                                    <th>{{ translate('Return')}}</th>
                                @elseif($order->payment_type == "razorpay" && $order->payment_status == "paid")
                                    <th>{{ translate('Refund')}}</th>
                                @elseif($order->payment_type == "wallet" && $order->payment_status == "paid")   
                                <th>{{ translate('Refund')}}</th> 
                                @endif
                                
                                 <th>{{ translate('Replacement')}}</th>
                                
                                
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $notReturnCount = $order->orderDetails->where('delivery_status','!=','return')->count();
                        @endphp
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        @if ($orderDetail->product != null)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->name }}</a>
                                        @else
                                            <strong>{{  translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $orderDetail->variation }}
                                    </td>
                                    <td>
                                        {{ $orderDetail->quantity }}
                                    </td>
                                    <!-- <td>
                                        @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                            {{  translate('Home Delivery') }}
                                        @elseif ($orderDetail->shipping_type == 'pickup_point')
                                            @if ($orderDetail->pickup_point != null)
                                                {{ $orderDetail->pickup_point->name }} ({{  translate('Pickip Point') }})
                                            @endif
                                        @endif
                                    </td> -->
                                    <td>{{ single_price($orderDetail->price) }}</td>
                                    @if(!empty($order->referal_discount))
                                    <td>{{ single_price($orderDetail->price - $orderDetail->peer_discount) }}</td>
                                    @endif
                                    <td>{{ ucfirst($orderDetail->delivery_status) }}</td>
                                    @if($order->payment_type == "cash_on_delivery")
                                        <td>
                                            @if($orderDetail->delivery_status != 'delivered')
                                                  @php
                                                    $check_return = \App\RefundRequest::where(['order_detail_id'=>$orderDetail->id])->first();
                                                  @endphp
                                                    @if (empty($check_return))
                                                    @if($notReturnCount >1)
                                                        <a href="{{route('refund_request_send_page', $orderDetail->id)}}" class="btn btn-styled btn-sm btn-base-1">{{  translate('Request') }}</a>
                                                    @else
                                                        <span class="strong-600">{{  translate('Return Not Available') }}</span>
                                                    @endif
                                                    @elseif($check_return["sorting_hub_approval"] != 1 || $check_return["admin_approval"] != 1)
                                                        <span class="strong-600">{{  translate('Return Requested') }}</span>
                                                    @elseif($check_return["sorting_hub_approval"] == 1 || $check_return["admin_approval"] == 1)  
                                                    <span class="strong-600" style = 'color:green'>{{  translate('Return Approved') }}</span>
                                                    @endif  
                                            @else
                                                 {{ ucfirst($orderDetail->delivery_status) }}
                                            @endif                                               
                                        </td>
                                    @elseif($order->payment_type == "razorpay" && $order->payment_status == "paid")
                                    <td>
                                            @if($orderDetail->delivery_status != 'delivered')
                                                  @php
                                                    $check_return = \App\RefundRequest::where(['order_detail_id'=>$orderDetail->id])->first();
                                                  @endphp
                                                    @if (empty($check_return)) 
                                                        @if($notReturnCount >1)
                                                        <a href="{{route('refund_request_send_page', $orderDetail->id)}}" class="btn btn-styled btn-sm btn-base-1">{{  translate('Request') }}</a>
                                                        @else
                                                        <span class="strong-600">{{  translate('Return Not Available') }}</span>
                                                        @endif
                                                    @elseif($check_return["sorting_hub_approval"] != 1 || $check_return["admin_approval"] != 1)
                                                        <span class="strong-600">{{  translate('Return Requested') }}</span>
                                                    @elseif($check_return["sorting_hub_approval"] == 1 || $check_return["admin_approval"] == 1)  
                                                    <span class="strong-600" style = 'color:green'>{{  translate('Return Approved') }}</span>
                                                    @endif  
                                            @else
                                                 {{ ucfirst($orderDetail->delivery_status) }}
                                            @endif                                               
                                        </td>
                                    @elseif($order->payment_type == "wallet" && $order->payment_status == "paid")
                                    <td>
                                            @if($orderDetail->delivery_status != 'delivered')
                                                  @php
                                                    $check_return = \App\RefundRequest::where(['order_detail_id'=>$orderDetail->id])->first();
                                                  @endphp
                                                    @if (empty($check_return))
                                                        @if($notReturnCount >1)
                                                        <a href="{{route('refund_request_send_page', $orderDetail->id)}}" class="btn btn-styled btn-sm btn-base-1">{{  translate('Request') }}</a>
                                                        @else
                                                        <span class="strong-600">{{  translate('Return Not Available') }}</span>
                                                        @endif
                                                    @elseif($check_return["sorting_hub_approval"] != 1 || $check_return["admin_approval"] != 1)
                                                        <span class="strong-600">{{  translate('Return Requested') }}</span>
                                                    @elseif($check_return["sorting_hub_approval"] == 1 || $check_return["admin_approval"] == 1)  
                                                    <span class="strong-600" style = 'color:green'>{{  translate('Return Approved') }}</span>
                                                    @endif  
                                            @else
                                                 {{ ucfirst($orderDetail->delivery_status) }}
                                            @endif                                               
                                        </td>
                                    @endif
                                    @php
                                    $message = "Not requested";
                                    $class = "";
                                    $replacement = \App\ReplacementOrder::where('order_detail_id',$orderDetail->id)->first();

                                    if(!is_null($replacement)){
                                        if($replacement->approve == 1){
                                            $message = "Approve";
                                            $class = "badge badge-success";
                                        }
                                        if($replacement->approve == 2){
                                            $message = "Replaced";
                                            $class = "badge badge-success";
                                        }
                                        if($replacement->approve == 0){
                                            $message = "Requested";
                                            $class = "badge badge-success";
                                        }

                                    }

                                    @endphp
                                     <td><span class="{{ $class }}">{{ $message }}<span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card mt-4">
                <div class="card-header py-2 px-3 heading-6 strong-600">{{ translate('Order Amount')}}</div>
                <div class="card-body pb-0">
                    <table class="table details-table">
                        <tbody>
                            <tr>
                                <th>{{ translate('Total Value')}}</th>
                                <td class="text-right">
                                    <span class="strong-600">{{ single_price($order->orderDetails->where('delivery_status','!=','return')->sum('price')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ translate('Delivery Charges')}}</th>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->where('delivery_status','!=','return')->sum('shipping_cost')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ translate('Total Tax')}}</th>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->where('delivery_status','!=','return')->sum('tax')) }}</span>
                                </td>
                            </tr>
                            
                            @if($order->referal_discount > 0)
                                <tr>
                                    <th><span style="color: green">{{translate('Total Savings')}} :</span></th>
                                    <td class="text-right">
                                        <span class="text-italic">{{ single_price($order->referal_discount) }}</span>
                                    </td>
                                </tr>
                            @endif

                             @php
                                if($order->wallet_amount==0){
                                        $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('price') + $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('shipping_cost')-$order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('peer_discount');
                                        
                                }else{
                                        $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('price') + $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('shipping_cost')-$order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('peer_discount');  

                                      // $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('price') + $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('tax') + $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('shipping_cost')-$order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=', 'return')->sum('peer_discount');  
                                       
                                }
                               

                                if($order->referal_discount > 0){
                                      $referral = $order->referal_discount;
                                      $total_amount = $total_amount;
                                }

                                if($order->wallet_amount > 0){
                                    $wallet = $order->wallet_amount;
                                    //$total_amount = $total_amount - $wallet;
                                }
                                
                            @endphp
                             @if($order->payment_type != 'wallet')
                            <tr>
                                <th><span class="strong-600">{{ translate('Grand Total')}}</span></th>
                                <td class="text-right">
                                    <strong><span>{{ single_price($total_amount) }}</span></strong>
                                </td>
                            </tr>
                             @endif

                             
                             @if($order->payment_type != 'wallet')
                            <tr>
                                <th>{{translate('Amount Paid from Wallet')}} :</th>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->wallet_amount) }}</span>
                                </td>
                            </tr>
                            @endif
                            @if($order->payment_type == 'wallet')

                                <tr>
                                    <th><span class="strong-600">{{ translate('Grand Total')}}</span></th>
                                    <td class="text-right">
                                        <strong><span>{{ single_price($order->wallet_amount) }}</span></strong>
                                    </td>
                                </tr>

                               
                            
                                <tr>
                                    <th>{{ translate('Amount Paid from Wallet')}}</th>
                                    <td class="text-right">
                                        <span class="text-italic">{{ single_price($order->wallet_amount) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="font-weight: bold">{{ translate('Total Amount Payable')}}</th>
                                    <td class="text-right">
                                        <span class="text-italic" style="font-weight: bold">{{ single_price(0) }}</span>
                                    </td>
                                </tr>
                                
                            @endif
                            <tr><th></th><td class="text-right">Inclusive of all Taxes</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($order->manual_payment && $order->manual_payment_data == null)
                <button onclick="show_make_payment_modal({{ $order->id }})" class="btn btn-block btn-base-1">{{ translate('Make Payment')}}</button>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript">
    function show_make_payment_modal(order_id){
        $.post('{{ route('checkout.make_payment') }}', {_token:'{{ csrf_token() }}', order_id : order_id}, function(data){
            $('#payment_modal_body').html(data);
            $('#payment_modal').modal('show');
            $('input[name=order_id]').val(order_id);
        });
    }
</script>
