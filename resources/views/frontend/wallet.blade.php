@extends('frontend.layouts.app')

@section('content')

 <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">
    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                @if(Auth::user()->user_type == 'seller')
                        @include('frontend.inc.seller_side_nav')
                    @elseif(Auth::user()->user_type == 'customer' ||Auth::user()->user_type == 'partner' || (Auth::user()->user_type=='staff' && Auth::user()->peer_partner==1))
                        @include('frontend.inc.customer_side_nav')
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-12 d-flex align-items-center">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{ translate('My Wallet')}}
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{ translate('Dashboard')}}</a></li>
                                            <li class="active"><a href="{{ route('wallet.index') }}">{{ translate('My Wallet')}}</a></li>
                                        </ul>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center green-widget text-white mt-4 c-pointer">
                                    <i class="fa fa-inr"></i>
                                    <span class="d-block title heading-3 strong-400">{{ single_price(Auth::user()->balance) }}</span>
                                    <span class="d-block sub-title">{{  translate('Wallet Balance') }}</span>

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center plus-widget mt-4 c-pointer" onclick="show_wallet_modal()">
                                    <i class="la la-plus"></i>
                                    <span class="d-block title heading-6 strong-400 c-base-1">{{  translate('Recharge Wallet') }}</span>
                                </div>
                            </div>

                            @if (\App\Addon::where('unique_identifier', 'offline_payment')->first() != null && \App\Addon::where('unique_identifier', 'offline_payment')->first()->activated)
                               <!--  <div class="col-md-4">
                                    <div class="dashboard-widget text-center plus-widget mt-4 c-pointer" onclick="show_make_wallet_recharge_modal()">
                                        <i class="la la-plus"></i>
                                        <span class="d-block title heading-6 strong-400 c-base-1">{{  translate('Offline Recharge Wallet') }}</span>
                                    </div>
                                </div> -->
                            @endif

                        </div>

                        <div class="card no-border mt-5">
                            <div class="card-header py-3">
                                <h4 class="mb-0 h6">{{ translate('Wallet recharge history')}}</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-responsive-md mb-0">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{  translate('Date') }}</th>
                                        <th>{{ translate('Amount')}}</th>
                                        <th>{{ translate('Payment Method')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($wallet_recharge) > 0)
                                        @foreach ($wallet_recharge as $key => $rowwallet)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ date('d-m-Y', strtotime($rowwallet->created_at)) }}</td>
                                                <td>{{ single_price($rowwallet->amount) }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $rowwallet ->payment_method)) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="text-center pt-5 h4" colspan="100%">
                                                <i class="la la-meh-o d-block heading-1 alpha-5"></i>
                                                <span class="d-block">{{  translate('No history found.') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <div class="pagination-wrapper py-4">
                            <ul class="pagination justify-content-end">
                                {{ $wallet_recharge->links() }}
                            </ul>
                        </div>

                        
              @if(Auth::check())
                      @if (Auth::user()->id != null)
                     <?php $ids = Auth::user()->id;
                     $peer_codes = \App\PeerPartner::where('user_id', $ids)->where('verification_status', 1)->where('peertype_approval', 1)->select('id','code')->first(); 
                         if(!empty($peer_codes)){
                           ?> 
                          <div class="card no-border mt-5">
                            <div class="card-header py-3">
                                <h4 class="mb-0 h6">{{ translate('Master Wallet History')}}
                                    <?php if(!empty(@$master_code)){ ?> - <strong style="color: #09f">{{@$master_code}}</strong><?php } ?></h4>
                            </div>

                            <div class="panel-heading bord-btm clearfix pad-all h-100">       
                                <div class="pull-left clearfix" style="margin-left: 10px; margin-top: 15px">
                                <?php 

                                     $newdate = date('Y-m-d', strtotime('-7 days'));
                                    $from_date = date('Y-m-d'); 
                                    if(empty($start_date))
                                    {
                                       $start_date = $newdate;
                                    }else{
                                       $start_date = $start_date;
                                    }

                                    $to_date = date('Y-m-d'); 
                                    if(empty($end_date))
                                    {
                                       $end_date = $from_date;
                                    }else{
                                       $end_date = $end_date;
                                    }

                                    
                                ?>

                                    <form method="post" action="{{ route('subpeers_commissions.showbydate', encrypt($id)) }}">
                                  @csrf
                                      <div class="box-inline pad-rgt pull-left">
                                        <label for="users-list-role">{{ translate("Start Date") }}</label>
                                            <div class="" style="">
                                                   
                                                    <input type="date" class="datepicker" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>">
                                            </div>
                                      </div>

                                      <div class="box-inline pad-rgt pull-left" style="margin-left: 12px">
                                        <label for="users-list-role">{{ translate("End Date") }}</label>
                                            <div class="" style="">
                                                    
                                                    <input type="date" class="datepicker" id="end_date" name="end_date" autocomplete="off" value="<?php echo $end_date; ?>">
                                            </div>
                                      </div>
                                      
                                      <div class="box-inline pad-rgt pull-left" style="margin-top: 25px;margin-left: 12px">
                                          <button class="btn btn-default" style="background-color: #2d50a5!important; color: #fff" type="submit">{{ translate("Filter") }}</button>
                                      </div>
                                  
                                </form>
                                    
                                </div>
                            </div>


                            <div class="card-body" style="overflow-x:auto;">
                                     <table class="table table-striped" id="example" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{translate('Master Code')}}</th>
                                        <th>{{translate('Refferal Code')}}</th>
                                        <th>{{translate('Total Order Amount')}}</th>
                                        <th>{{translate('Total Peer Commission')}}</th>
                                        <th>{{translate('Total Master Commission')}}</th>
                                        <th width="10%">{{translate('Options')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                      @if(count($all_orders) > 0)
                                        @foreach ($all_orders as $key => $row)
                                            @php
                                             $peer_refferal_code = \App\User::where('id', $row->partner_id)->select('name','email')->first();

                                             if(!empty($peer_refferal_code)){
                                                $peer_name = $peer_refferal_code['name'];
                                                $peer_email = $peer_refferal_code['email'];
                                             } else {
                                                $peer_name = 'NA';
                                                $peer_email ='NA';
                                             }
                                        @endphp 
                                        
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td><span style="color: blue; font-weight: bold">{{@$master_code}}</span></td>
                                                <td><span style="color: green; font-weight: bold">{{$row->refral_code}}</span><br>{{$peer_name}}<br>{{$peer_email}}</td>
                                                <td>{{$row->total_orderamount}}</td>
                                                <td> {{$row->total_refferaldiscount}}</td>
                                                <td> {{$row->total_masterdiscount}}</td> 
                                                <td>
                                                    <div class="btn-group dropdown">
                                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                                            {{translate('Actions')}} <i class="dropdown-caret"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                           
                                                            <li><a href="{{ route('wallet_peer_commissions.show', encrypt($row->partner_id)) }}" target="blank">{{translate('View Peer Report')}}</a></li>                                         

                                                        </ul>
                                                    </div>
                            </td> 
                                            </tr>
                                        @endforeach
                                     @else
                                        <tr>
                                            <td class="text-center pt-5 h4" colspan="100%">
                                                <i class="la la-meh-o d-block heading-1 alpha-5"></i>
                                                <span class="d-block">{{  translate('No history found.') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                    <?php } ?>
                    @endif
                     @endif 




                     <?php if(!empty($all_total)) { ?>

                         <div class="col-sm-12">
                               <button class="btn btn-rounded btn-info pull-right">{{translate('Total Order Amount')}}: {{$all_total['total_orderamount']}}</button>
                               <button class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('Total Peer Commission')}}: {{$all_total['total_refferaldiscount']}}</button>
                            </div>
                        </div>
                     
                        <!-- <div class="col-sm-12">
                               <button class="btn btn-rounded btn-info pull-right">{{translate('Total Order Amount')}}: 0</button>
                               <button class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('Total Peer Commission')}}: 0</button>
                            </div>
                        </div> -->
                     <?php } ?>  

                    <br>


                     @if(Auth::check())
                      @if (Auth::user()->id != null)
                     <?php $ids = Auth::user()->id;
                     $sub_peer_codes = \App\PeerPartner::where('user_id', $ids)->where('verification_status', 1)->select('peer_type')->first(); 
                     if(!empty($sub_peer_codes)){
                         if(!empty($sub_peer_codes['peer_type']!='master')){
                           ?> 

                        <div class="card no-border mt-5">
                            <div class="card-header py-3">
                                <h4 class="mb-0 h6">{{ translate('Peer History')}}</h4>
                            </div>
                            <div class="panel-heading bord-btm clearfix pad-all h-100">       
                                <div class="pull-left clearfix" style="margin-left: 10px; margin-top: 15px">
                                <?php 

                                     $newdate = date('Y-m-d', strtotime('-7 days'));
                                    $from_date = date('Y-m-d'); 
                                    if(empty($start_date))
                                    {
                                       $start_date = $newdate;
                                    }else{
                                       $start_date = $start_date;
                                    }

                                    $to_date = date('Y-m-d'); 
                                    if(empty($end_date))
                                    {
                                       $end_date = $from_date;
                                    }else{
                                       $end_date = $end_date;
                                    }

                                    
                                ?>

                                    <form method="post" action="{{ route('wallet_subpeer_commissions.showbydate', encrypt($id)) }}">
                                  @csrf
                                      <div class="box-inline pad-rgt pull-left">
                                        <label for="users-list-role">{{ translate("Start Date") }}</label>
                                            <div class="" style="">
                                                   
                                                    <input type="date" class="datepicker" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>">
                                            </div>
                                      </div>

                                      <div class="box-inline pad-rgt pull-left" style="margin-left: 12px">
                                        <label for="users-list-role">End Date</label>
                                            <div class="" style="">
                                                    
                                                    <input type="date" class="datepicker" id="end_date" name="end_date" autocomplete="off" value="<?php echo $end_date; ?>">
                                            </div>
                                      </div>
                                      
                                      <div class="box-inline pad-rgt pull-left" style="margin-top: 25px;margin-left: 12px">
                                          <button class="btn btn-default" style="background-color: #2d50a5!important; color: #fff" type="submit">Filter</button>
                                      </div>
                                  
                                </form>
                                    
                                </div>
                            </div>


                            <div class="card-body" style="overflow-x:auto;">
                                <table class="table table-sm table-responsive-md mb-0">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{translate('Refferal Code')}}</th>
                                        <th>{{translate('Customer Name')}}</th>
                                        <th>{{translate('Order ID')}}</th>
                                        <th>{{ translate('Total Order Amount')}}</th>
                                        <th>{{  translate('Total Peer Commission') }}</th>
                                        <th>{{translate('Date')}}</th>
                                       <!--  <th width="10%">{{translate('Options')}}</th> -->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($wallets) > 0)
                                        @foreach ($wallets as $key => $wallet)
                                        @php 
                                         $order_id = \App\Order::where('id', $wallet->order_id)->select('code','shipping_pin_code','shipping_address','user_id')->first(); 
                                         if(!empty($order_id->shipping_pin_code)){               
                                            $shortinghub= \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order_id->shipping_pin_code . '"]\')')->pluck('user_id')->first();
                                            $sorting_hub_name = \App\User::where('id', $shortinghub)->select('name')->first();
                                            $sh_name = $sorting_hub_name['name'];
                                        }else{
                                            $sh_name = 'NA';
                                        }

                                        $userdetail = \App\User::where('id', $order_id->user_id)->select('name','email')->first(); 

                                         @endphp 
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td><span style="color: green; font-weight: bold">{{$wallet->refral_code}}</span><br>{{$peer_name}}<br>{{$peer_email}}</td>
                                                <td>{{ ucfirst($userdetail['name']) }}<br>{{ $userdetail['email'] }}</td>
                                                <td><b>{{$order_id->code}}</b><br> <span style="color: #09f; font-weight: 600">{{$sh_name}}</span><br>{{ json_decode($order_id->shipping_address)->postal_code }}</td>
                                                <td>{{$wallet->order_amount}}</td>
                                                <td> {{$wallet->referal_commision_discount}}</td>
                                                <td>{{ date('d-m-Y', strtotime($wallet->created_at)) }}</td>
                                                <td>
                                                    <div class="btn-group dropdown">
                                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                                            {{translate('Actions')}} <i class="dropdown-caret"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                           
                                                            <li><a href="{{ route('wallet_customer_subpeer_commissions.show', encrypt($order_id->user_id.'_'.$wallet->partner_id)) }}" target="blank">{{translate('View Customer Peer Report')}}</a></li>                                         

                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="text-center pt-5 h4" colspan="100%">
                                                <i class="la la-meh-o d-block heading-1 alpha-5"></i>
                                                <span class="d-block">{{  translate('No history found.') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                      <?php } } ?>
                        @endif
                        @endif



                        
                       


                       
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="wallet_modal_new" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabels" aria-hidden="true">
      
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ translate('Recharge Wallet')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('wallet.recharge') }}" method="post">
                    @csrf
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Amount')}} <span class="required-star">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <input type="number" class="form-control mb-3" name="amount" placeholder="{{ translate('Amount')}}" min="1" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Payment Method')}} <span class="required-star">*</span></label>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-3">
                                    <select class="form-control selectpicker" data-minimum-results-for-search="Infinity" name="payment_option" readonly>
                                        @if (\App\BusinessSetting::where('type', 'paypal_payment')->first()->value == 1)
                                            <option value="paypal">{{ translate('Paypal')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'stripe_payment')->first()->value == 1)
                                            <option value="stripe">{{ translate('Stripe')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'sslcommerz_payment')->first()->value == 1)
                                            <option value="sslcommerz">{{ translate('SSLCommerz')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'instamojo_payment')->first()->value == 1)
                                            <option value="instamojo">{{ translate('Instamojo')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'paystack')->first()->value == 1)
                                            <option value="paystack">{{ translate('Paystack')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'voguepay')->first()->value == 1)
                                            <option value="voguepay">{{ translate('VoguePay')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'payhere')->first()->value == 1)
                                            <option value="payhere">{{ translate('Payhere')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'ngenius')->first()->value == 1)
                                            <option value="ngenius">{{ translate('Ngenius')}}</option>
                                        @endif
                                        @if (\App\BusinessSetting::where('type', 'razorpay')->first()->value == 1)
                                            <option value="razorpay">{{ translate('Razorpay')}}</option>
                                        @endif
                                        @if(\App\Addon::where('unique_identifier', 'african_pg')->first() != null && \App\Addon::where('unique_identifier', 'african_pg')->first()->activated)
                                            @if (\App\BusinessSetting::where('type', 'mpesa')->first()->value == 1)
                                                <option value="mpesa">{{ translate('Mpesa')}}</option>
                                            @endif
                                            @if (\App\BusinessSetting::where('type', 'flutterwave')->first()->value == 1)
                                                <option value="flutterwave">{{ translate('Flutterwave')}}</option>
                                            @endif
                                        @endif
                                        @if (\App\Addon::where('unique_identifier', 'paytm')->first() != null && \App\Addon::where('unique_identifier', 'paytm')->first()->activated)
                                            <option value="paytm">{{ translate('Paytm')}}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-base-1">{{ translate('Confirm')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="offline_wallet_recharge_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ translate('Offline Recharge Wallet')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="offline_wallet_recharge_modal_body"></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script> -->

<!-- jQuery Modal -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>  -->
<!-- <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script> 

<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script> 

 -->

    <script type="text/javascript">
        function show_wallet_modal(){
            $('#wallet_modal_new').modal('show');
        }

        function show_make_wallet_recharge_modal(){
            $.post('{{ route('offline_wallet_recharge_modal') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#offline_wallet_recharge_modal_body').html(data);
                $('#offline_wallet_recharge_modal').modal('show');
            });
        }
    </script>

     <script type="text/javascript">
       $(document).ready(function() {
            $('#example').DataTable( {
                "paging": false,
                dom: 'Bfrtip',
                buttons: [
                    'excel'
                ]
            } );
        } );
    </script>
@endsection
