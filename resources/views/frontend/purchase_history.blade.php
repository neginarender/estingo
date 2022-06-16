@extends('frontend.layouts.app')

@section('content')

    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                @if(Auth::user()->user_type == 'seller')
                        @include('frontend.inc.seller_side_nav')
                    @elseif(Auth::user()->user_type == 'customer' ||Auth::user()->user_type == 'partner' || (Auth::user()->user_type=="staff" && Auth::user()->peer_partner==1))
                        @include('frontend.inc.customer_side_nav')
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-12">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{ translate('Purchase History')}}
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{ translate('Dashboard')}}</a></li>
                                            <li class="active"><a href="{{ route('purchase_history.index') }}">{{ translate('Purchase History')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (count($orders) > 0)
                            <!-- Order history table -->
                            <div class="card no-border mt-4">
                                <div>
                                    <table class="table table-sm table-hover table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th>{{ translate('Code')}}</th>
                                                <th>{{ translate('Date')}}</th>
                                                <th>{{ translate('Amount')}}</th>
                                                <th>{{ translate('Delivery Detail')}}</th>
                                                <th>{{ translate('Delivery Status')}}</th>
                                                <th>{{ translate('Payment Status')}}</th>
                                                <th>{{ translate('Options')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($orders as $key => $order)
                                                @if (count($order->orderDetails) > 0)
                                                    <tr>
                                                        <td>
                                                            <a href="#{{ $order->code }}" onclick="show_purchase_history_details({{ $order->id }})">{{ $order->code }}</a>
                                                        </td>
                                                        <td>{{ date('d-m-Y', $order->date) }}</td>
                                                        @php 
                                                            $grand_total = $order->grand_total;
                                                            if($order->payment_type=="razorpay" && !empty($order->wallet_amount))
                                                            {
                                                                $grand_total = $order->grand_total+$order->wallet_amount;
                                                            }
                                                            @endphp
                                                        <td>
                                                            @if($order->payment_type == 'wallet')
                                                              {{ single_price($order->wallet_amount) }}
                                                            @else
                                                              {{ single_price($grand_total) }}
                                                            @endif   
                                                        </td>
                                                        <td>
                                                            @php
                                                            $schedule = App\SubOrder::where('order_id',$order->id)->where('status',1)->get();
                                                            @endphp
                                                            @foreach($schedule as $key => $value)
                                                                <h6 style="color:#4183c4">{{strtoupper($value->delivery_name)}}</h6>
                                                                <span>Type: <strong>{{ucfirst($value->delivery_type)}}</strong></span><br>
                                                                @if($value->delivery_type == 'scheduled')
                                                                    <span>Date: <strong>{{date('d M, Y',strtotime($value->delivery_date))}}</strong></span><br>
                                                                    <span>Schedule: <strong>{{$value->delivery_time}}</strong></span>
                                                                @endif
                                                                <hr>
                                                            @endforeach
                                                            
                                                        </td>
                                                        <td>
                                                           <span style="text-transform:capitalize;">
                                                           @if($order->order_status=="pending" && $order->payment_status=='paid' && ($order->payemnt_type='razorpay' || $order->payment_type == 'letzpay_payment'))
                                                           Under Processing
                                                           @elseif(($order->order_status=='pending')&&($order->payment_status == 'unpaid')&&($order->payment_type == 'razorpay' || $order->payment_type == 'letzpay_payment'))   
                                                                 Payment Failed

                                                            @elseif(($order->order_status=='pending')&&($order->payment_status == 'unpaid')&&($order->payment_type == 'cash_on_delivery'))   
                                                                 Under Processing
                                                           @else
                                                           {{ str_replace('_',' ',$order->order_status) }}
                                                            @endif
                                                        </span>

                                                                @if($order->delivery_viewed == 0)
                                                                    <span class="ml-2" style="color:green"><strong>*</strong></span>
                                                                @endif
                                                        </td>
                                                        <td>
                                                            @if($order->payment_type != 'cash_on_delivery')
                                                                <span class="badge badge--2 mr-4">
                                                                    @if (($order->payment_status == 'paid') || ($order->payment_status == 'Paid'))
                                                                        <i class="bg-green"></i> {{ translate('Paid')}}
                                                                    @elseif($order->payment_status == 'refunded')
                                                                        <i class="bg-info"></i> {{ translate('Refunded')}}
                                                                    @else
                                                                        <i class="bg-red"></i> {{ translate('Unpaid')}}
                                                                    @endif
                                                                    @if($order->payment_status_viewed == 0)
                                                                        <span class="ml-2" style="color:green"><strong>*</strong></span>
                                                                    @endif
                                                             @else
                                                                 <span class="badge badge--2 mr-4"><i class="bg-green"></i> {{ translate('COD')}}</span>
                                                             @endif       
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn" type="button" id="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <i class="fa fa-ellipsis-v"></i>
                                                                </button>

                                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="">
                                                                    <button onclick="show_purchase_history_details({{ $order->id }})" class="dropdown-item">{{ translate('Order Details')}}</button>
                                                                    <a href="{{ route('customer.invoice.download', $order->id) }}" class="dropdown-item">{{ translate('Download Invoice')}}</a>
                                                                    
                                                                    @if (($order->order_status != 'delivered' && $order->order_status!= 'cancel' && $order->order_status!='partially_delivered'))
                                                                        <a href="{{ url('/') }}/orders/cancel/{{ encrypt($order->id) }}" class="dropdown-item">{{translate('Cancel')}}</a>
                                                                    @endif
                                                                    <a href="{{ url('/') }}/orders/replace/{{ encrypt($order->id) }}" class="dropdown-item">{{"Need Help?"}}</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="pagination-wrapper py-4">
                            <ul class="pagination justify-content-end">
                                {{ $orders->links() }}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <div id="order-details-modal-body">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ translate('Make Payment')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="payment_modal_body"></div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script type="text/javascript">
        $('#order_details').on('hidden.bs.modal', function () {
            location.reload();
        })
    </script>

@endsection