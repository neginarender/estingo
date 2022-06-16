@extends('frontend.layouts.app')
@section('robots'){{  translate('index') }}@stop
@section('content')
    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                    @if(Auth::user()->user_type == 'partner' && Auth::user()->peer_partner == 1)
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
                                        {{ translate('Referral orders')}}
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{ translate('Dashboard')}}</a></li>
                                            <li class="active"><a href="{{ route('digital_purchase_history.index') }}">{{ translate('Referrals')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card no-border mt-4">
                            <div>
                                <table class="table table-sm table-hover table-responsive-md">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Order id')}}</th>
                                            @if($peer_type=="master-peer")
                                            <th>{{ translate('Code')}}</th>
                                            @endif
                                            <th>{{ translate('City')}}</th>
                                            <th>{{ translate('Order Amount')}}</th>
                                            <th>{{ translate('Shipping')}}</th>
                                            <th>{{ translate('Total Amount')}}</th>
                                            <th>{{ translate('Discount') }}</th>
                                            <th>{{ translate('Commision') }}</th>
                                            <th>{{ translate('Order Status') }}</th>
                                            <th>{{ translate('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // echo '<pre>';
                                            // print_r($ReferralOrders);
                                            // die;
                                        ?>

                                        @forelse($ReferralOrders as $key => $referral)
                                        

                                         <?php
                                                $grand_total = $referral->grand_total+$referral->wallet_amount;
                                                $ref_discount = $referral->referal_discount;
                                                $order_code = $referral->code;
                                                $commission = ($peer_type=="master-peer") ? $referral->master_discount: $referral->referal_commision_discount;
                                                $city = "";
                                                if(!is_null($referral->shipping_address)){
                                                    $city = json_decode($referral->shipping_address)->city;
                                                }
                                          ?>            
                                       
                                            <tr>
                                                <td>{{$order_code }}</td>
                                                @if($peer_type=="master-peer")
                                                <td>{{$referral->refral_code}}</td>
                                                @endif
                                                 <td>@if($city!='') {{ ucfirst($city) }} @else NA @endif</td>
                                                <td>&nbsp;₹{{round($grand_total, 2)}}</td>
                                                
                                                <td>&nbsp;&nbsp;₹{{$referral->total_shipping_cost}}</td>
                                               <td>&nbsp;₹{{$grand_total}}</td>
                                                    <!-- Csutomer Discount on order-->
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;₹{{$referral->referal_discount}}</td>
                                                <!-- Sub Peer Or Master Peer Commission on order-->
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;₹{{$commission}}</td>
                                                <td>{{ $referral->order_status }}</td>
                                                <td>
                                                    @if($referral->wallet_status != 0) 
                                                        <span class="tab success">{{ 'Transfered' }} </span>
                                                    @else
                                                        <span class="tab pending">{{ 'Pending' }} </span>
                                                    @endif
                                                </td>
                                            </tr>
                                             @empty
                                            <tr><td colspan="6"><center>No Referral Orders Found</center></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="pagination-wrapper py-4">
                            <ul class="pagination justify-content-end">
                                {{ $ReferralOrders->links() }}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
<style type="text/css">
.tab { font-size: 11px; padding: 5px 7px; color: #fff; }
.success { background: #80ae00; } .pending { background: #b78e41d1; }
</style>
