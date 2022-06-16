@extends('layouts.ccapp')
@section('content')

    <div class="panel">
        <div class="panel-body">
            <div class="invoice-masthead">
            @if(Auth::user()->staff!='')
                @if(Auth::user()->staff->role->name == "Sorting Hub")
                    @if($order->order_status!='delivered' && $order->order_status!='cancel')
                    @if(Auth::user()->email!='cc_raebareli@rozana.in')
                    <a href="{{ route('admin.add_to_order',['id'=>$order->id]) }}" class="btn btn-success">+ Add products</a>
                    @endif
                    @endif
                @endif
            @endif
                <div class="invoice-text">
                    <h3 class="h1 text-thin mar-no text-primary">{{ translate('Order Details') }}</h3>
                </div>
            </div>
            <div class="row">
                @php
                    $delivery_status = $order->orderDetails->first()->delivery_status;
                    $payment_status = $order->payment_status;
                @endphp
                
                


                <div class="col-lg-offset-6 col-lg-3">
                    <label for="update_payment_status">{{translate('Payment Status')}}</label>
                    @if(@Auth::user()->staff->role->name!="Call Center Department")
                    @if ($delivery_status != 'delivered')
                        <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="update_payment_status" @php echo Auth::user()->staff['role_id'] == 6?"disabled":""; @endphp >
                            <option value="paid" @if ($payment_status == 'paid') selected @endif>{{translate('Paid')}}</option>
                            <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>{{translate('Unpaid')}}</option>
                        </select>
                    @else
                        <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="update_payment_status" @php echo Auth::user()->staff['role_id'] == 6?"disabled":""; @endphp >
                            <option value="paid">Paid</option>
                        </select>

                    @endif 
                    @else
                        <br />
                        <span><strong>{{ ($payment_status=="paid") ? "Paid" : "Unpaid" }}</strong></span>
                        @endif

                </div>
                
                @if(Auth::user()['user_type'] != "admin")
                    @if(Auth::user()->user_type== "operations")
                    <div class="col-lg-3">
                        <label for=update_delivery_status"">{{translate('Delivery Status')}}</label>
                        <input type="hidden" id="role_id" value={{Auth::user()->staff['role_id']}}">

                        @if($order->dofo_status == 0)

                            @if(@Auth::user()->staff->role->name!="Call Center Department" && $delivery_status!='delivered')
                            <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="send_otp">
                                <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                                <option value="on_review" @if ($delivery_status == 'on_review') selected @endif>{{translate('On review')}}</option>

                                <option value="on_delivery" @if ($delivery_status == 'on_delivery') selected @endif>{{translate('On delivery')}}</option>
                                <option value="in_transit" @if ($delivery_status == 'in_transit') selected @endif>{{translate('In Transit')}}</option>
                                <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                                <option value="cancel" @if ($delivery_status == 'cancel') selected @endif>{{translate('Cancel')}}</option>
                            </select>
                            @else
                            <br />
                            <span><strong style="text-transform: capitalize;">{{$delivery_status}}</strong></span>
                            @endif
                        @else
                            <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="send_otp">
                                <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                                <option value="on_review" @if ($delivery_status == 'on_review') selected @endif>{{translate('On review')}}</option>
                                <option value="on_delivery" @if ($delivery_status == 'on_delivery') selected @endif>{{translate('On delivery')}}</option>
                                <option value="in_transit" @if ($delivery_status == 'in_transit') selected @endif>{{translate('In Transit')}}</option>
                                <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                                <option value="cancel" @if ($delivery_status == 'cancel') selected @endif>{{translate('Cancel')}}</option>
                            </select>
                        @endif
                    </div>
                </div>
                @else
                <div class="col-lg-3">
                        <label for=update_delivery_status"">{{ Auth::user()['user_type'] }}{{translate('Delivery Status')}}</label>
                        <input type="hidden" id="order_status" value="{{$delivery_status}}">
                        
                        <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="send_otp">
                            <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                            <option value="on_review" @if ($delivery_status == 'on_review') selected @endif>{{translate('On review')}}</option>
                            <option value="on_delivery" @if ($delivery_status == 'on_delivery') selected @endif>{{translate('On delivery')}}</option>
                            <option value="in_transit" @if ($delivery_status == 'in_transit') selected @endif>{{translate('In Transit')}}</option>
                            <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                            <option value="cancel" @if ($delivery_status == 'cancel') selected @endif>{{translate('Cancel')}}</option>
                        </select>
                        
                    </div>
                </div>
                @endif
                @else
                 <div class="col-lg-3">
                        <label for=update_delivery_status"">{{translate('Delivery Status')}}</label>
                        <input type="hidden" id="order_status" value="{{$delivery_status}}">
                        <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="send_otp">
                            <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{translate('Pending')}}</option>
                            <option value="on_review" @if ($delivery_status == 'on_review') selected @endif>{{translate('On review')}}</option>
                            <option value="on_delivery" @if ($delivery_status == 'on_delivery') selected @endif>{{translate('On delivery')}}</option>
                            <option value="in_transit" @if ($delivery_status == 'in_transit') selected @endif>{{translate('In Transit')}}</option>
                            <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{translate('Delivered')}}</option>
                            <option value="cancel" @if ($delivery_status == 'cancel') selected @endif>{{translate('Cancel')}}</option>
                        </select>
                    </div>
                </div>

                @endif

                

            

            @if(Auth::user()->user_type!='')


            @if(Auth::user()->user_type == "operations")
                <div class="col-lg-offset-6 col-lg-3">
                    <label for=assign_order"">{{translate('Assign Order')}}</label>
                    @php

                    $sorting_hub_id = Auth::user()->sorting_hub_id;
                    $getDeliveryBoy = \App\DeliveryBoy::on('mysql')->where('sorting_hub_id',$sorting_hub_id)->get();
                    $getAssignedBoy = \App\AssignOrder::on('mysql')->where('order_id',$order->id)->first('delivery_boy_id');
                    
                    @endphp
                    <input type="hidden" id="order_id" value="{{$order['id']}}">
                    <select class="form-control demo-select2"  data-minimum-results-for-search="Infinity" id="assign_order" @if($delivery_status === 'delivered' || !is_null($getAssignedBoy)) disabled @endif>
                        <option value="">{{translate('Select Delivery')}}</option>
                        @foreach($getDeliveryBoy as $key=>$value)
                        <?php
                          $delivery_boy_name = \App\User::where('id',$value['user_id'])->first('name');
                        ?>
                        <option value="<?php echo $value['id']; ?>" @php  echo $getAssignedBoy['delivery_boy_id'] == $value['id']?"selected":"";  @endphp><?php  echo $delivery_boy_name['name']; ?></option>
                        @endforeach
                        
                    </select>
                </div>
                @endif
@endif
            <hr>
            
            <div class="invoice-bill row">
                <div class="col-sm-6 text-xs-center">
                    <address>
                        <strong class="text-main">{{ json_decode($order->shipping_address)->name }}</strong><br>
                         {{ json_decode($order->shipping_address)->email }}<br>
                         {{ json_decode($order->shipping_address)->phone }}<br>
                         {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->postal_code }}<br>
                         {{ json_decode($order->shipping_address)->country }}
                    </address>
                    @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                        <br>
                        <strong class="text-main">{{ translate('Payment Information') }}</strong><br>
                        Name: {{ json_decode($order->manual_payment_data)->name }}, Amount: {{ single_price(json_decode($order->manual_payment_data)->amount) }}, TRX ID: {{ json_decode($order->manual_payment_data)->trx_id }}
                        <br>
                        <a href="{{ my_asset(json_decode($order->manual_payment_data)->photo) }}" target="_blank"><img src="{{ my_asset(json_decode($order->manual_payment_data)->photo) }}" alt="" height="100"></a>
                    @endif
                </div>
                <div class="col-sm-6 text-xs-center">
                    <table class="invoice-details">
                    <tbody>
                    <tr>
                        <td class="text-main text-bold">
                            {{translate('Order #')}}
                        </td>
                        <td class="text-right text-info text-bold">
                            {{ $order->code }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-main text-bold">
                            {{translate('Order Status')}}
                        </td>
                        @php
                            $status = $order->orderDetails->first()->delivery_status;
                        @endphp
                        <td class="text-right">
                            @if($status == 'delivered')
                                <span class="badge badge-success">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            @else
                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-main text-bold">
                            {{translate('Order Date')}}
                        </td>
                        <td class="text-right">
                            {{ date('d-m-Y h:i A', $order->date) }}
                        </td>
                    </tr>
                    
                    @php 
                    $grand_total =  ($order->orderDetails->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->sum('price')+ $order->orderDetails->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->sum('shipping_cost'))- $order->orderDetails->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->sum('peer_discount');
                       
                        @endphp
                        
                    <tr>
                        <td class="text-main text-bold">
                            {{translate('Total amount')}}
                        </td>
                        <td class="text-right">
                           {{ single_price($grand_total) }}
                        </td>
                        <!-- <td class="text-right">
                            {{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}
                        </td> -->
                    </tr>
                    <tr>
                        <td class="text-main text-bold">
                            {{translate('Payment method')}}
                        </td>
                        <td class="text-right">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                        </td>
                    </tr>
                    </tbody>
                    </table>
                </div>
            </div>
            @if(count($order->sub_orders))
           @foreach($order->sub_orders as $skey => $schedule)
                <div class="row">
                    <div class="col-md-4">
                    <span style="background-color:@if($schedule->delivery_name=='fresh') #33b75c @else #337ab7 @endif;padding:10px;color:#fff;border-radius:4px;" class="col-md-12">{{ ucfirst($schedule->delivery_name) }} Order
                    @if($schedule->delivery_type=='scheduled')
                    Schedule at {{ date('d-m-Y',strtotime($schedule->delivery_date)) }} {{ $schedule->delivery_time }}
                    @else
                    will be delivered with in 24 Hrs
                    @endif
                </span>
                </div>   
                </div>
                    <br /> 
            @endforeach 
            @endif 
            <hr class="new-section-sm bord-no">
            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-bordered invoice-summary">
                        <thead>
                            <tr class="bg-trans-dark">
                                <th class="min-col">#</th>
                                <th width="10%">
                                    {{translate('Photo')}}
                                </th>
                                <th class="text-uppercase">
                                    {{translate('Description')}}
                                </th>
                                <th class="text-uppercase">
                                    {{translate('Delivery Type')}}
                                </th>
                                <th class="min-col text-center text-uppercase">
                                    {{translate('Qty')}}
                                </th>
                                <th class="min-col text-center text-uppercase">
                                    {{translate('Unit Price')}}
                                </th>
                                @if(!empty($order->referal_discount))
                                <th class="min-col text-right text-uppercase">
                                    {{translate('Discounted Unit Price')}}
                                </th>
                                @endif
                                <th class="min-col text-right text-uppercase">
                                    {{translate('Total')}}
                                </th>
                                
                                @if(Auth::user()['user_type'] != "admin")
                                    @if(Auth::user()->staff['role_id'] != 6)
                                <th class="min-col text-right text-uppercase">
                                    {{translate('Available Distributor')}}
                                </th>
                                    @endif
                                @endif
                                <th>{{ translate('Order Status')}}</th>
                                @if(Auth::user()['user_type'] != "admin")
                                    @if(Auth::user()->staff['role_id'] != 6)
                                <th>{{ translate('Return')}}</th>
                                     @endif
                                @endif
                                @if(Auth::user()['user_type'] != "admin")
                                    @if(Auth::user()->staff['role_id'] != 6)
                                    @if(count($order->orderDetails)>1)
                                    @if(Auth::user()->email!='cc_raebareli@rozana.in')
                                        <th>{{ translate('Remove Product')}}</th>
                                    @endif    
                                    @endif
                                     @endif
                                @endif
                            </tr>
                            
                        </thead>
                        <tbody>
                            @php
                                $admin_user_id = \App\User::on('mysql')->where('user_type', 'admin')->first()->id;
                            @endphp
                            @foreach ($order->orderDetails as $key => $orderDetail)
                            @php
                            $sh_id = Auth::user()['id'];
                            if(Auth::user()['user_type'] != "admin"){
                                $distributor = \App\MappingProduct::on('mysql')->where('product_id',$orderDetail['product_id'])->where('sorting_hub_id', $sh_id)->first();  
                            }
                            
                            @endphp
                            
                                <tr>
                                    <td>{{ $key+1 }}
                                    @if($orderDetail->add_by_admin==1)
                                        <span class="badge badge-success">Added</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"><img height="50" src="{{ my_asset($orderDetail->product->thumbnail_img) }}"></a>
                                        @else
                                            <strong>{{ translate('N/A') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null)
                                            <strong><a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">{{ $orderDetail->product->name }}</a></strong>
                                            <small>{{ $orderDetail->variation }}</small>
                                        @else
                                            <strong>{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
                                            {{ translate('Home Delivery') }}
                                        @elseif ($orderDetail->shipping_type == 'pickup_point')
                                            @if ($orderDetail->pickup_point != null)
                                                {{ $orderDetail->pickup_point->name }} ({{ translate('Pickup Point') }})
                                            @else
                                                {{ translate('Pickup Point') }}
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $orderDetail->quantity }}
                                    </td>
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price/$orderDetail->quantity) }}
                                    </td>
                                    @if(!empty($order->referal_discount))
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price/$orderDetail->quantity - $orderDetail->peer_discount/$orderDetail->quantity) }}
                                    </td>
                                    @endif
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price - $orderDetail->peer_discount) }}
                                    </td>
                                    
                                    @if(Auth::user()['user_type'] != "admin")
                                        @if(Auth::user()->staff['role_id'] != 6)
                                        <td class="text-center">
                                            <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary" onclick="getProductDistributors({{$orderDetail->product_id}})" data-toggle="modal" data-target="#exampleModal">
                                            View Distributor
                                        </button>

                                        </td>
                                        @endif
                                    @endif
                                    <td>{{ ucfirst($orderDetail->delivery_status) }}</td>
                                    
                                    <td>
                                    @if(Auth::user()['user_type'] != "admin")
                                        @if(Auth::user()->staff['role_id'] != 6)
                                    @php
                                    $check_return_request = \App\RefundRequest::on('mysql')->where('order_detail_id',$orderDetail->id)->first();
                                    @endphp
                                        @if (empty($check_return_request))
                                        <span class="strong-600">{{  translate('Not Requested') }}</span> 
                                        @if($orderDetail->payment_status=='paid' && $orderDetail->delivery_status=='pending')
                                        @if(Auth::user()->email!='cc_raebareli@rozana.in')
                                        <a href="{{ route('purchase_history.sorting_hub_refund_back',[$orderDetail->id]) }}" onclick="return confirm('Are you sure?')" class="btn btn-success btn-sm">Send Refund</a>      
                                        @endif     
                                        @endif           
                                        @elseif($check_return_request['sorting_hub_approval'] == 0 || $check_return_request['admin_approval']==0)
                                        
                                        
                                        <div class="btn-group dropdown">
                                        @if($check_return_request['refund_by']!='sorting_hub')
                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                            {{translate('Return Requested')}} <i class="dropdown-caret"></i>
                                        </button>
                                            @else
                                            <span>Refunded</span>
                                            @endif
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{route('purchase_history.refund_request_send_back', $orderDetail->id)}}">{{  translate('Approve') }}</a></li>
                                        <li><a onclick="" >{{translate('Deny')}}</a></li>
                                    </ul>
                                  </div>
                                        
                                        @elseif($check_return_request['sorting_hub_approval'] ==1  || $check_return_request['admin_approval']==1)
                                            @if($check_return_request['refund_by']!='sorting_hub')
                                            <span class="strong-600">{{  translate('Return Accepted') }}</span> 
                                            @else
                                            <span  class="strong-600">{{  translate('Refunded') }}</span>
                                            @endif
                                        @endif
                                        @endif
                                    @endif

                                    </td>
                                    

                                    @if(Auth::user()['user_type'] != "admin")
                                            @if(Auth::user()->staff['role_id'] != 6)
                                                @if($orderDetail->delivery_status != 'return' && $order->order_status !='delivered' && $orderDetail->delivery_status != 'delivered')
                                                    @if(count($order->orderDetails)>1)
                                                    @if(Auth::user()->email!='cc_raebareli@rozana.in')
                                                        <td><a href="{{route('callceter.removeorderproduct',$orderDetail->id)}}" onclick="disablePointer()">{{translate('Remove')}}</a></td>
                                                        @endif
                                                    @endif
                                                @else
                                                    <td>
                                                    <strong>{{ translate('N/A') }}</strong>
                                                    </td>
                                                @endif  
                                            @endif
                                        @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
            </div>
            <div class="clearfix">
                <table class="table invoice-total">
                <tbody>
                <tr>
                    <td>
                        <strong>{{translate('Sub Total')}} :</strong>
                    </td>
                    <td>
                    {{ single_price($order->orderDetails->where('delivery_status','!=','return')->sum('price')) }}
                        <!-- {{ single_price($order->orderDetails->sum('price')) }} -->
                        <!-- {{ single_price($order->orderDetails->where('seller_id', $admin_user_id)->sum('price')) }} -->
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <strong>{{translate('Tax')}} :</strong>
                    </td>
                    <td>
                        {{ single_price($order->orderDetails->where('delivery_status','!=','return')->sum('tax')) }}
                    </td>
                    <!-- <td>
                        {{ single_price($order->orderDetails->where('seller_id', $admin_user_id)->sum('tax')) }}
                    </td> -->
                </tr>
                
                <tr>
                    <td>
                        <strong>{{translate('Delivery Charges')}} :</strong>
                    </td>
                    <td>
                        <!-- {{ single_price($order->orderDetails->where('seller_id', $admin_user_id)->sum('shipping_cost')) }} -->
                        {{ single_price($order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('shipping_cost')) }}
                    </td>
                </tr>

                

                @php 
                    $check_refund = \App\RefundRequest::on('mysql')->where('order_id',$order->id)->first();
                @endphp
                
                @if(!empty($check_refund))
                @php 
                    $refunded_amount = \App\RefundRequest::on('mysql')->where(['order_id'=>$order->id,'refund_status'=>1])->sum('refund_amount');
                @endphp
                    @if(!empty($refunded_amount))
                    <tr>
                        <td>
                            <strong>{{translate('Refunded Amount')}} :</strong>
                        </td>
                        <td>
                            <!-- {{ single_price($order->orderDetails->where('seller_id', $admin_user_id)->sum('shipping_cost')) }} -->
                            {{ single_price($refunded_amount) }}
                        </td>
                    </tr>
                    @endif
                @endif
                
                
                 
                  @if($order->coupon_discount > 0)
                <tr>
                    <td>
                        <strong>{{translate('Coupon Discount')}} :</strong>
                    </td>
                    <td>
                        {{ single_price($order->coupon_discount) }}
                    </td>
                </tr>
                @endif

                

                @php
                    if($order->wallet_amount==0){

                            $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->sum('price') + $order->orderDetails->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->where('order_id', $order->id)->sum('shipping_cost') - $order->orderDetails->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->where('order_id', $order->id)->sum('peer_discount');
                    }else{
                           // $total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price')->where('delivery_status','!=','return')->where('delivery_status','!=','refund') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost')->where('delivery_status','!=','return')->where('delivery_status','!=','refund'); 
                          $total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost');  
                    }
                    

                    $total_discount = 0;
                    if($order->referal_discount > 0){
                          $referral = $order->referal_discount;
                          $total_discount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->sum('peer_discount');
                    }

                    if($order->wallet_amount > 0){
                        $wallet = $order->wallet_amount;
                        $total_amount = $total_amount - $total_discount;
                    }
                    
                @endphp
                
                @if($order->payment_type != 'wallet')
                <tr>
                    <td>
                        <strong>{{translate('Total Amount Payable')}} :</strong>
                    </td>
                    <td class="text-bold h4">
                          {{ single_price($total_amount) }}
                    </td>
                </tr>
                @endif
                @if($order->payment_type == 'wallet')
                        <tr>
                            <td><strong>{{translate('TOTAL')}} :</strong></td>
                            <td class="text-bold h4">{{ single_price($order->wallet_amount) }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Total Amount Payable')}}</td>
                            <td class="text-bold">{{ single_price(0) }}</td>
                        </tr>

                      @if($order->referal_discount > 0)
                        <tr>
                            <td>
                                <span style="color: green;"><strong>{{translate('Total Savings')}} :</strong></span>
                            </td>
                            <td>
                                {{ single_price($order->referal_discount) }}
                            </td>
                        </tr>
                        @endif


                        <tr>
                            <td><strong>{{ translate('Amount Paid from Wallet')}}</td>
                            <td class="text-bold">{{ single_price($order->wallet_amount) }}</td>
                        </tr>    
                    @endif


                    @if($order->referal_discount > 0)
                        <tr>
                            <td>
                                <span style="color: green;"><strong>{{translate('Total Savings')}} :</strong></span>
                            </td>
                            <td>
                                {{ single_price($order->referal_discount) }}
                            </td>
                        </tr>
                        @endif

                @if($order->payment_type != 'wallet')
                <tr>
                    <td>
                        <strong>{{translate('Amount Paid from Wallet')}} :</strong>
                    </td>
                    <td>
                        {{ single_price($order->wallet_amount) }}
                    </td>
                </tr>
                @endif

                    
                </tbody>
                </table>
            </div>
            @php
            $vegetable = array();
            $other = array();
            foreach($order->orderDetails->where('delivery_status','!=','return')->where('delivery_status','!=','refund')->whereNull('deleted_at') as $key=>$orderdetail){
                if($orderdetail->product['category_id'] == '18'|| $orderdetail->product['category_id']=='26' || $orderdetail->product['category_id']=='34' || $orderdetail->product['subcategory_id'] == '129' || $orderdetail->product['subcategory_id']==67 || $orderdetail->product['category_id']=='33' || $orderdetail->product['category_id']=='38' || $orderdetail->product['category_id']=='39' || $orderdetail->product['category_id']=='40'){
                    array_push($vegetable,$order->orderDetails[$key]['id']);
                    
                }else{
                    array_push($other,$order->orderDetails[$key]['id']);
                }
            }
            $query = http_build_query(array('aParam' => $vegetable,'type'=>'fresh_invoice'));
            $query1 = http_build_query(array('aParam' => $other,'type'=>'other_invoice'));
            @endphp
            <!-- <div class="row">
             <div class="col-md-3 pull-right">
                    <select name="invoice_type" class="form-control" onchange="set_invoice_size(this.value)">
                        <option value="reel">Reel</option>
                        <option value="A4">A4</option>
                    </select>
                </div>
                </div> -->
                <br />
            @if(!empty($vegetable))
                <div class="text-right no-print">
                    <a href="{{ route('breakUpInvoice.invoice.download', $query) }}" class="btn btn-default" target="_blank"><i class="demo-pli-printer icon-lg">Vegetables & Fruits Invoice</i></a>
                </div>
                @if(!empty($other))
                <div class="text-right no-print">
                    <a href="{{ route('breakUpInvoice.invoice.download', $query1) }}" class="btn btn-default" target="_blank"><i class="demo-pli-printer icon-lg">Other Invoice</i></a>
                </div>
                @endif
            @else   
                @if(count(\App\OrderDetail::where('order_id',$order->id)->where('delivery_status','!=','refund')->get())>0)
                <div class="text-right no-print">
                    <a href="{{ route('seller.invoice.download', $order->id) }}" class="btn btn-default" target="_blank"><i class="demo-pli-printer icon-lg">Download Invoice</i></a>
                </div>
                @endif

            @endif
           
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header" style="border-bottom: 1px solid #e5e5e5;background: #6d7074;">
            <h5 class="modal-title" id="exampleModalLabel" style="color: #fff;">Distributor Detail</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true" style="color: #fff;">X</span>
            </button>
         </div>
         <div class="modal-body">
             <div class="row">
                <div class="col-sm-12 text-xs-center">
                    <div id="load_distributors"></div>
                </div>
             </div>
            
         </div>
         <br />
         <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
        <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Verify OTP</h4>
        </div>
        <div class="modal-body">
        <form class="form form-horizontal mar-top" action="{{route('deliveryboy.verifyOTP')}}" method="POST" id="otp_form">
        @csrf
        <input type="hidden" name="order_id" value="{{$order->id}}">
        <input type="hidden" name="phone" value="{{json_decode($order->shipping_address)->phone}}">
            <div class="form-group">
                <label class="col-lg-2 control-label">OTP</label>
                <div class="col-lg-7">
                <input type="text" placeholder="OTP" name="otp" class="form-control" autocomplete="off" required>
                </div>
                <div class="mar-all text-right">
                <button type="submit" class="btn btn-info">{{ translate('Verify') }}</button>
                </div>
            </div>
        </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
        </div>

    </div>
    </div>
<!-- Modal cancel -->
    <div id="myModalCancel" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Verify OTP</h4>
        </div>
        <div class="modal-body">
        <form class="form form-horizontal mar-top" action="" method="POST" id="otp_form">
        @csrf
        
            <div class="form-group">
                <label class="col-lg-2 control-label">Mobile*</label>
                <div class="col-lg-7">
                    <input type="hidden" name="order_id" class="orderid" value="{{$order->id}}">
                    <input type="number" placeholder="Mobile" name="mobilenum" class="form-control get_mobile" autocomplete="off" id="getmobile" required>
                    <span style="color: green; display: none" class="show_verified">Verified</span>
                </div>
            </div>
             <div class="form-group otp_module" style="display: none;">
                <label class="col-lg-2 control-label">OTP*</label>
                <div class="col-lg-7">
                    <input type="hidden" value="" name="check_code" class="check_code"> 
                    <input type="text" placeholder="OTP" name="otp" class="form-control get_otp" autocomplete="off" required>
                    <span style="color: green; display: none" class="show_verifiedotp">Verified</span>
                </div>
            </div>

            <!-- <div class="form-group description_v" style="display: none;">

                <div class="mar-all text-right">
                <button type="button" class="btn btn-info verifyotp" disabled="disabled">{{ translate('Verify') }}</button>
                <button type="button" class="btn btn-info verifiedotp" style="display: none">{{ translate('Verify') }}</button>
                </div>
            </div> -->


        </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-default close_modal" data-dismiss="modal">Close</button>
        </div>
        </div>

    </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">

        $(document).ready(function(){
            set_invoice_size('reel');
        });
        
        $('#update_delivery_status').on('change', function(){
            console.log(role_id);
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            $.post('{{ route('orders.update_delivery_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                showAlert('success', 'Delivery status has been updated');
            });
        });

        $('#update_payment_status').on('change', function(){
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                showAlert('success', 'Payment status has been updated');
            });
        });


        $('#assign_order').on('change',function(){
            var order_id = $('#order_id').val();
            var delivery_boy_id = $(this).val();
             $.post('{{ route('sorthinghub.assign_order') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,delivery_boy_id:delivery_boy_id}, function(data){
                showAlert('success', 'Order has been assigned.');
            });
        
        });

        $('#send_otp').on('change',function(){
            var order_status = $('#send_otp').val();
            // alert(order_status);
            var order_id = {{ $order->id }};
            if(order_status == 'delivered'){
                    $.post('{{ route('deliveryboy.sendotpverification') }}', {_token:'{{ @csrf_token() }}',order_id:order_id}, function(data){
                        console.log(data);
                        if(data.status == 1){
                            $('#myModal').modal('show');
                        }
                        showAlert('success', data.message);
                    });

            }else if(order_status == 'cancel'){
                $('#myModalCancel').modal('show');

            }else{
                    var status = $('#send_otp').val();
                    $.post('{{ route('orders.update_delivery_status') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,status:status}, function(data){
                        showAlert('success', 'Delivery status has been updated');
                    });

            }
        

        });

        $("#getmobile").blur(function(){
    var mobile = $('.get_mobile').val();
    $.post('{{ route('order.cancelorder') }}',{_token:'{{ csrf_token() }}', mobile:mobile}, function(data){
       // console.log(data);
       if(data==1){
            $('.show_verified').show();
            $('.otp_module').show();
            $('.get_otp').val('');
            $('.verifiedotp').hide();
            $('.verifyotp').show();
            $('.description_v').show();
       }else{
            alert('The Mobile number you entered is incorrect.');
            $('.otp_module').hide();
            $('.show_verified').hide();
            $('.show_verifiedotp').hide();
            $('.description_v').hide();
       }
        
  });
});

$(".get_otp").blur(function(){
    var mobile = $('.get_mobile').val();
    var otp = $('.get_otp').val();
    var order_id = $('.orderid').val();
    $.post('{{ route('order.getcancel_otp') }}',{_token:'{{ csrf_token() }}', mobile:mobile, otp:otp, order_id:order_id}, function(data){
       if(data==1){
            $('.show_verifiedotp').show();
            $('.verifiedotp').show();
            $('.verifyotp').hide();
            $('.close_modal').trigger("click");
       }else{
            alert('The OTP you entered is incorrect.');
            $('.verifiedotp').hide();
            $('.verifyotp').show();
       }
        
  });
});

        function set_invoice_size(value)
        {
            $.post('{{ route('admin.set_invoice_size') }}',{size:value,_token:'{{ csrf_token()}}'},function(data){
                console.log(data);
            });
        }
        function getProductDistributors(product_id){
        $.post("{{ route('order-products.distributors') }}",{_token:"{{ csrf_token() }}",product_id:product_id},function(data){
            $("#load_distributors").html(data);
        });
        }

        function disablePointer(){
            $(".panel-body").css('pointer-events','none');
        }

    </script>
@endsection
