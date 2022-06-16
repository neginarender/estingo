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
                        <select class="form-control demo-select2" name="delivery_status" id="delivery_status" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Deliver Status')}}</option>
                            <option value="pending"   @isset($delivery_status) @if($delivery_status == 'pending') selected @endif @endisset>{{translate('Pending')}}</option>
                            <option value="on_review"   @isset($delivery_status) @if($delivery_status == 'on_review') selected @endif @endisset>{{translate('On review')}}</option>
                            <option value="on_delivery"   @isset($delivery_status) @if($delivery_status == 'on_delivery') selected @endif @endisset>{{translate('On delivery')}}</option>
                            <option value="delivered"   @isset($delivery_status) @if($delivery_status == 'delivered') selected @endif @endisset>{{translate('Delivered')}}</option>
                        </select>
                        <!-- <select class="form-control demo-select2" name="delivery_status" id="delivery_status" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Deliver Status')}}</option>
                            <option value="pending" <?php if($delivery_status != NULL && $delivery_status == 'pending'){
                                echo 'selected';
                            } ?>>{{translate('Pending')}}</option>
                            <option value="on_review"  <?php if($delivery_status != NULL && $delivery_status == 'on_review'){
                                echo 'selected';
                            } ?>>{{translate('On review')}}</option>
                            <option value="on_delivery" <?php if($delivery_status != NULL && $delivery_status == 'on_delivery'){
                                echo 'selected';
                            } ?>>{{translate('On delivery')}}</option>
                            <option value="delivered" <?php if($delivery_status != NULL && $delivery_status == 'delivered'){
                                echo 'selected';
                            } ?>>{{translate('Delivered')}}</option>
                        </select> -->
                        
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & hit Enter') }}">
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form method="GET" action="{{route('delivery_boy.delivery_export')}}" name="delivery_export" id="delivery_export">
                    <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
                    <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">

                    <input type="submit" name="submit" id="submit" value="Export" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
                </form>
            </div> 
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
                    <th>{{translate('Ordered Date')}}</th>
                    <th>{{translate('Amount')}}</th>
                    <th>{{translate('Delivery Status')}}</th>
                    <th>{{translate('Delivered Date')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th>{{translate('Payment Status')}}</th>
                    @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <th>{{translate('Refund')}}</th>
                    @endif
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @if(count($orders) > 0)
                @foreach ($orders as $key => $order_id)
                    @php
                        $order = \App\Order::find($order_id->id);
                        $orderDetail = \App\OrderDetail::where('order_id',$order['id'])->first();
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
                            <td>{{$order->created_at}}</td>
                            <td>
                                {{ single_price($order->orderDetails->where('order_id', $order_id->id)->sum('price') + $order->orderDetails->where('order_id', $order_id->id)->sum('tax')) }}
                            </td>
                            <td>
                                @php
                                    $status = $order->orderDetails->first()['delivery_status'];
                                @endphp
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </td>
                            <td>
                                @php
                                if($orderDetail['delivery_status'] == 'delivered'){
                                    $deliveredDate = $orderDetail['updated_at'];
                                }else{
                                    $deliveredDate = '-';
                                }
                                @endphp
                                {{$deliveredDate}}
                            </td>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                            </td>
                            <td>
                                <span class="badge badge--2 mr-4">
                                <i class="bg-green"></i> {{ $order->payment_status }}
                                    <!-- @if ($order->orderDetails->where('seller_id',  $admin_user_id)->pluck('payment_status') == 'paid')
                                        <i class="bg-green"></i> {{ translate('Paid') }}
                                    @else
                                        <i class="bg-red"></i> {{ translate('Unpaid') }}
                                    @endif -->
                                </span>
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
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
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
    </script>
@endsection
