@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('Orders')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_orders" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 300px;">
                        <select class="form-control demo-select2" name="payment_type" id="payment_type" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Payment Status')}}</option>
                            <option value="paid"  @isset($payment_status) @if($payment_status == 'paid') selected @endif @endisset>{{translate('Paid')}}</option>
                            <option value="unpaid"  @isset($payment_status) @if($payment_status == 'unpaid') selected @endif @endisset>{{translate('Un-Paid')}}</option>
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 300px;">
                        <select class="form-control demo-select2" name="payment_type" id="payment_type" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Edit/View')}}</option>
                            <option value="viewed"  @isset($payment_status) @if($payment_status == 'viewed') selected @endif @endisset>{{translate('Viewed')}}</option>
                            <option value="edited"  @isset($payment_status) @if($payment_status == 'edited') selected @endif @endisset>{{translate('Edited')}}</option>
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 300px;">
                        <select class="form-control demo-select2" name="delivery_status" id="delivery_status" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Deliver Status')}}</option>
                            <option value="pending"   @isset($delivery_status) @if($delivery_status == 'pending') selected @endif @endisset>{{translate('Pending')}}</option>
                            <option value="on_review"   @isset($delivery_status) @if($delivery_status == 'on_review') selected @endif @endisset>{{translate('On review')}}</option>
                            <option value="on_delivery"   @isset($delivery_status) @if($delivery_status == 'on_delivery') selected @endif @endisset>{{translate('On delivery')}}</option>
                            <option value="delivered"   @isset($delivery_status) @if($delivery_status == 'delivered') selected @endif @endisset>{{translate('Delivered')}}</option>
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & hit Enter') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Order Code')}}</th>
                    <th>{{translate('Num. of Products')}}</th>
                    <th>{{translate('Customer')}}</th>
                    <th>{{translate('Address')}}</th>
                    <th>{{translate('Pin Code')}}</th>
                    @if(in_array(Auth::user()->user_type, ['admin']))
                    <th>{{translate('Sorting HUB')}}</th>
                    @endif
                    @if(Auth::user()->staff!='')
                        @if(Auth::user()->staff->role->name == "Sorting Hub")
                        <th>{{translate('Assign Order')}}</th>
                        @endif
                    @endif
                    <th>{{translate('Amount')}}</th>
                    <th>{{translate('Delivery Status')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th>{{translate('Payment Status')}}</th>
                    @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <th>{{translate('Refund')}}</th>
                    @endif
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
            @php 
            $total_today_amount=0;
            $refund_amount = 0;
             @endphp
                @foreach ($orders as $key => $order_id)
                    @php
                        $order = \App\Order::find($order_id->id);
                        $refund_amount += \App\RefundRequest::where(array('refund_status'=>1,'order_id'=>$order_id->id))->sum('refund_amount');
                        //$total_today_amount+=$order->grand_total;
                        $total_today_amount+=$order['grand_total'];
                    @endphp
                    @if($order != null)
                          
                        <tr>
                            <td>
                                {{ ($key+1) + ($orders->currentPage() - 1)*$orders->perPage() }}
                            </td>
                            <td>
                                {{ $order->code }} @if($order->viewed == 0) <span class="pull-right badge badge-info">{{ translate('New') }}</span> @endif
                            </td>
                            <td>
                                <!-- {{ count($order->orderDetails->where('seller_id', $admin_user_id)) }} -->
                                {{ $order->orderDetails->where('order_id', $order_id->id)->sum('quantity') }}
                            </td>
                            <td>
                                @if ($order->user != null)
                                    {{ $order->user->name }}
                                @else
                                    Guest ({{ $order->guest_id }})
                                @endif
                            </td>
                            
                            <td>
                            @php
                            $address = json_decode($order->shipping_address);
                            @endphp
                            {{$address->address}}
                            </td>

                            <td>
                            {{$order->shipping_pin_code}}
                            </td>
                            
                            @if(Auth::user()->staff!='')
                                @if(Auth::user()->staff->role->name == "Sorting Hub")
                                            @php
                                            $getDeliveryBoy = \App\DeliveryBoy::where('sorting_hub_id',Auth::user()->id)->get();
                                            $getAssignedBoy = \App\AssignOrder::where('order_id',$order->id)->first('delivery_boy_id');
                                            @endphp
                                            <input type="hidden" class="order_id" value="{{$order->id}}">
                                            <td>
                                            <select class="form-control demo-select2 assign_order"  data-minimum-results-for-search="Infinity"  @php echo ($delivery_status == 'delivered')?"disabled":"" @endphp>
                                                <option value="">{{translate('Select Delivery')}}</option>
                                                @foreach($getDeliveryBoy as $key=>$value)
                                                <?php
                                                $delivery_boy_name = \App\User::where('id',$value['user_id'])->first('name');
                                                ?>
                                                <option value="<?php echo $value['id']; ?>" @php  echo $getAssignedBoy['delivery_boy_id'] == $value['id']?"selected":"";  @endphp order_id = {{$order->id}}><?php  echo $delivery_boy_name['name']; ?></option>
                                                @endforeach
                                                
                                            </select>
                                            </td>
                                        @endif
                            @endif
                            
                            @if(in_array(Auth::user()->user_type, ['admin']))
                            <td>
                            @php
                            $sortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
                            if(!empty($sortingHub)){
                              echo  @$sortingHub->user->name;

                            }else{
                                echo "Not Available";
                            }
                            @endphp
                            </td>
                            @endif

                            
                            <td>
                                {{ single_price($order->orderDetails->where('order_id', $order_id->id)->sum('price') + $order->orderDetails->where('order_id', $order_id->id)->sum('tax')) }}
                            </td>
                            <td>
                                @if(($order->orderDetails->first()->delivery_status=='pending')&&($order->payment_status == 'paid')&&($order->payment_type == 'letzpay_payment')) 
                                    Under Processing

                                @elseif(($order->orderDetails->first()->delivery_status=='pending')&&($order->payment_status == 'unpaid')&&($order->payment_type == 'letzpay_payment'))   
                                     Payment Failed

                                @elseif(($order->orderDetails->first()->delivery_status=='pending')&&($order->payment_status == 'unpaid')&&($order->payment_type == 'cash_on_delivery'))   
                                     Under Processing

                                @else
                                     {{ ucfirst(str_replace('_', ' ', $order->orderDetails->first()->delivery_status)) }}     
                                @endif

                               
                            </td>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                            </td>
                            <td>
                                 @if($order->payment_type != 'cash_on_delivery')
                                        <span class="badge badge--2 mr-4">
                                        <i class="bg-green"></i> {{ $order->payment_status }}
                                            <!-- @if ($order->orderDetails->where('seller_id',  $admin_user_id)->pluck('payment_status') == 'paid')
                                                <i class="bg-green"></i> {{ translate('Paid') }}
                                            @else
                                                <i class="bg-red"></i> {{ translate('Unpaid') }}
                                            @endif -->
                                        </span>
                                 @else
                                        <span class="badge badge--2 mr-4"><i class="bg-green"></i> {{ translate('COD')}}</span>
                                 @endif       
                            </td>
                            @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                                <td>
                                    @if (count($order->refund_requests) > 0)
                                        {{ count($order->refund_requests) }} {{ translate('Refund') }}
                                    @else
                                        {{ translate('No Refund') }}
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{ route('orders.show', encrypt($order->id)) }}">{{translate('View')}}</a></li>
                                        <li><a href="{{ route('seller.invoice.download', $order->id) }}">{{translate('Download Invoice')}}</a></li>
                                        <li><a onclick="confirm_modal('{{route('orders.destroy', $order->id)}}');">{{translate('Delete')}}</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @if(isset($user_type))
                <span class="pull-right badge badge-success" style="padding:10px;">Total amount = {{ single_price($total_today_amount-$refund_amount) }}</span>
                @endif
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $orders->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection


@section('script')
    <script type="text/javascript">
        function sort_orders(el){
            $('#sort_orders').submit();
        }

         $('.assign_order').on('change',function(){
            var order_id = $('option:selected', this).attr('order_id');
            console.log(order_id);
		
			var delivery_boy_id = $(this).val();
			 $.post('{{ route('sorthinghub.assign_order') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,delivery_boy_id:delivery_boy_id}, function(data){
                showAlert('success', 'Order has been assigned.');
            });
		
	});
    </script>

   
@endsection
