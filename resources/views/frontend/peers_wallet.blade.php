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
                    @elseif(Auth::user()->user_type == 'customer' ||Auth::user()->user_type == 'partner' || (Auth::user()->user_type=="staff" && Auth::user()->peer_partner==1))
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



                    @if(Auth::check())
                      @if (Auth::user()->id != null)
                          <div class="card no-border mt-5">
                            <div class="card-header py-3">
                                <h4 class="mb-0 h6">{{ translate('Master Wallet History')}}
                                    <?php if(!empty($master_code)){ ?> - <strong style="color: #09f">{{$master_code}}</strong><?php } ?></h4>
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

                                    <form method="post" action="{{ route('wallet_peer_commissions.showbydate', encrypt($id)) }}">
                                  @csrf
                                      <div class="box-inline pad-rgt pull-left">
                                        <label for="users-list-role">Start Date</label>
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
                                     <table class="table table-striped" id="example" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{translate('Master Code')}}</th>
                                        <th>{{translate('Refferal Code')}}</th>
                                        <th>{{translate('Customer Name')}}</th>
                                        <th>{{translate('Order ID')}}</th>
                                        <th>{{translate('Total Order Amount')}}</th>
                                        <th>{{translate('Total Peer Commission')}}</th>
                                        <th>{{translate('Total Master Commission')}}</th>
                                        <th width="10%">{{translate('Date')}}</th>
                                        <th width="10%">{{translate('Options')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                      @if(count($all_orders) > 0)
                                        @foreach ($all_orders as $key => $row)

                                     
                                         @php 
                                         $order_id = \App\Order::where('id', $row->order_id)->select('code','shipping_pin_code','shipping_address','user_id')->first(); 
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
                                                <td>{{$key+1}}</td>
                                                <td><span style="color: blue; font-weight: bold">{{$master_code}}</span><br>{{$master_name}}<br>{{$master_email}}</td>
                                                <td><span style="color: green; font-weight: bold">{{$row->refral_code}}</span><br>{{$peer_name}}<br>{{$peer_email}}</td>
                                                <td><?php if(!empty($userdetail)){?>
                                                    {{ ucfirst($userdetail['name']) }}<br>{{ $userdetail['email'] }}
                                                <?php } else { 
                                                    echo 'Guest';
                                                 }?>
                                                </td>
                                                <td><b>{{$order_id->code}}</b><br> <span style="color: #09f; font-weight: 600">{{$sh_name}}</span><br>{{ json_decode($order_id->shipping_address)->postal_code }}</td>
                                                <td>{{$row->order_amount}}</td>
                                                <td> {{$row->referal_commision_discount}}</td>
                                                <td> {{$row->master_discount}}</td> 
                                                <td> {{date('d-m-Y', strtotime($row->created_at)) }}</td>  
                                                <td>
                                                    <div class="btn-group dropdown">
                                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                                            {{translate('Actions')}} <i class="dropdown-caret"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                          <?php if(!empty($userdetail)){?>
                                                            <li><a href="{{ route('wallet_customerpeer_commissions.show', encrypt($order_id->user_id.'_'.$row->partner_id)) }}" target="blank">{{translate('View Customer Peer Report')}}</a></li> 
                                                          <?php } ?>                                         

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
                    @endif
                     @endif

                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    

@endsection

@section('script')
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> 
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script> 

<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script> 



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
    <script type="text/javascript">
        function show_wallet_modal(){
            $('#wallet_modal').modal('show');
        }

        function show_make_wallet_recharge_modal(){
            $.post('{{ route('offline_wallet_recharge_modal') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#offline_wallet_recharge_modal_body').html(data);
                $('#offline_wallet_recharge_modal').modal('show');
            });
        }
    </script>
@endsection
