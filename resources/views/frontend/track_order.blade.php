@extends('frontend.layouts.app')

@section('content')

    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-12 mx-auto">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{ translate('Track Order')}}
                                    </h2>
                                </div>
                            </div>
                        </div>
                        <form class="" action="{{ route('orders.track') }}" method="GET" enctype="multipart/form-data">
                            <div class="form-box bg-white mt-4">
                                <div class="form-box-title px-3 py-2">
                                    {{ translate('Order Info')}}
                                </div>
                                <div class="form-box-content p-3">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{ translate('Order Code')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control mb-3" placeholder="{{ translate('Order Code')}}" value="@isset($_GET['order_code']) {{ @$_GET['order_code']}}@endisset" name="order_code" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-styled btn-base-1">{{ translate('Track Order')}}</button>
                                @if(isset($message))
                                <span class="alert alert-danger" style="float:left; font-size: small;">{{ $message }}</span>
                                @endif
                            </div>
                           
                        </form>
                       
                    </div>
                </div>
            </div>

            @isset($order)
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
                                        <td>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, {{ json_decode($order->shipping_address)->country }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <table class="details-table table">
                                    <tr>
                                        <td class="w-50 strong-600">{{ translate('Order date')}}:</td>
                                        <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
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
                                        <td><!-- {{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }} -->
                                            {{ single_price($grand_total) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 strong-600">{{ translate('Shipping method')}}:</td>
                                        <td>{{ translate('Flat shipping rate')}}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 strong-600">{{ translate('Payment method')}}:</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                
                    <div class="card mt-4">
                        {{-- <div class="card-header py-2 px-3 heading-6 strong-600 clearfix">
                            <ul class="process-steps clearfix">
                                <li @if($status == 'pending') class="active" @else class="done" @endif>
                                    <div class="icon">1</div>
                                    <div class="title">{{ translate('Order placed')}}</div>
                                </li>
                                <li @if($status == 'on_review') class="active" @elseif($status == 'on_delivery' || $status == 'delivered') class="done" @endif>
                                    <div class="icon">2</div>
                                    <div class="title">{{ translate('On review')}}</div>
                                </li>
                                <li @if($status == 'on_delivery') class="active" @elseif($status == 'delivered') class="done" @endif>
                                    <div class="icon">3</div>
                                    <div class="title">{{ translate('On delivery')}}</div>
                                </li>
                                <li @if($status == 'delivered') class="done" @endif>
                                    <div class="icon">4</div>
                                    <div class="title">{{ translate('Delivered')}}</div>
                                </li>
                            </ul>
                        </div> --}}
                        <div class="card-body p-4">
                            <div class="col-12 table-responsive">
                                <table class="details-table table">
                                <thead>
                                <th>#</th>
                                <th>{{ translate('Image') }}</th>
                                <th>{{ translate('Product Name') }}</th>
                                <th>{{ translate('Quantity') }} </th>
                                <th>{{ translate('Order status') }}</th>
                                </thead>
                                
                                <tbody>

                                @foreach ($order->orderDetails as $key => $orderDetail)
                            @php
                                $status = $orderDetail->delivery_status;
                                $class="alert-danger";
                                if($status=="delivered"){
                                    $class = "alert-success";
                                }elseif($status=="on_delivery"){
                                    $class = "alert-info";
                                }elseif($status =="on_review"){
                                    $class = "alret-warning";
                                }
                            @endphp
                                <tr>
                                <td>{{ $key+1 }}</td>
                                <td><img src="{{ Storage::disk('s3')->url($orderDetail->product->thumbnail_img)}}" /></td>
                                <td>{{ $orderDetail->product->name }}</td>
                                <td>{{ $orderDetail->quantity }}</td>
                                <td><span class="alert {{ $class }}">{{ $orderDetail->delivery_status }}<span></td>
                                </tr>
                                @endforeach
                                </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>

            @endisset
        </div>
    </section>

@endsection
