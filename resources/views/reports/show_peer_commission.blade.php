@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<!--===================================================-->

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">

<div class="row">
        <div class="col-sm-12">
           <button class="btn btn-rounded btn-info pull-right">{{translate('Total Order Amount')}}: {{$all_total['total_orderamount']}}</button>
           <button class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('Total Peer Commission')}}: {{$all_total['total_refferaldiscount']}}</button>
           <button class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('Total Master Commission')}}: {{$all_total['total_masterdiscount']}}</button>
        </div>
    </div>

    <br>

<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <!-- <h3 class="panel-title pull-left pad-no">Today's Orders ( <?php echo date("d-m-Y"); ?> )</h3> -->
        <div class="pull-left clearfix">
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
        <!-- <form method="post" action="{{ route('distributororders.show', encrypt($id)) }}"> -->
            <form method="post" action="{{ route('peers_commissions.showbydate', encrypt($id)) }}">
          @csrf
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">Start Date</label>
                    <div class="" style="">
                            <input type="hidden" name="distributor_id" value="{{$id}}">
                            <input type="date" class="datepicker" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>">
                    </div>
              </div>

              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Date</label>
                    <div class="" style="">
                            <input type="hidden" name="distributor_id" value="{{$id}}">
                            <input type="date" class="datepicker" id="end_date" name="end_date" autocomplete="off" value="<?php echo $end_date; ?>">
                    </div>
              </div>
              
              <div class="box-inline pad-rgt pull-left"><br>
                  <button class="btn btn-default" type="submit">Filter</button>
              </div>
          
        </form>
            
        </div>
    </div>
    <div class="panel-body" style="overflow-x:auto;">
       
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
             <!-- <table class="table table-striped res-table mar-no" id="example" cellspacing="0" width="100%"> -->
        <!--  <table class="table table-striped table-bordered demo-dt-basic" id="table_id" cellspacing="0" width="100%"> -->
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
                @foreach ($all_orders as $key => $row)
                    
                    @if($all_orders != null)
                     @php 
                     $order_id = \App\Order::where('id', $row->order_id)->select('code','shipping_pin_code','shipping_address','user_id')->first(); 
                     if(!empty($order_id->shipping_pin_code)){               
                        $shortinghub= \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order_id->shipping_pin_code . '"]\')')->pluck('user_id')->first();
                        $sorting_hub_name = \App\User::where('id', $shortinghub)->select('name')->first();
                        $sh_name = $sorting_hub_name['name'];
                    }else{
                        $sh_name = 'NA';
                    }

                    //if($order_id->user_id!=''){
                        $userdetail = \App\User::where('id', $order_id['user_id'])->select('name','email')->first(); 
                    //}else{
                        //$userdetail = '';
                    //}    

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
                            <td><b>{{$order_id['code']}}</b><br> <span style="color: #09f; font-weight: 600">{{$sh_name}}</span><br>
                                <?php if(!empty($order_id->shipping_address)){?>
                                    {{ json_decode($order_id->shipping_address)->postal_code }}
                                <?php } ?>   
                            </td>
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
                                            <li><a href="{{ route('customerpeer_commissions.show', encrypt($order_id->user_id.'_'.$row->partner_id)) }}" target="blank">{{translate('View Customer Peer Report')}}</a></li>
                                        <?php } ?>                                                

                                    </ul>
                                </div>
                            </td>               
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        
    </div>
</div>

@endsection


@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5', 'csvHtml5'
                ]
            } );
        } );
    </script>
@endsection
