@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<!--===================================================-->

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">

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
            <form method="post" action="{{ route('peer_commissions.showbydate', encrypt($id)) }}">
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
    <div class="panel-body">
       
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
             <!-- <table class="table table-striped res-table mar-no" id="example" cellspacing="0" width="100%"> -->
        <!--  <table class="table table-striped table-bordered demo-dt-basic" id="table_id" cellspacing="0" width="100%"> -->
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
                @foreach ($all_orders as $key => $row)
                    
                    @if($all_orders != null)
                          
                        <tr>
                            <td>{{$key+1}}</td>
                            <td><span style="color: blue; font-weight: bold">{{$master_code}}</span></td>
                            <td><span style="color: green; font-weight: bold">{{$row->refral_code}}</span></td>
                            <td>{{$row->total_orderamount}}</td>
                            <td> {{$row->total_refferaldiscount}}</td>
                            <td> {{$row->total_masterdiscount}}</td> 
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       
                                        <li><a href="{{ route('peers_commissions.show', encrypt($row->partner_id)) }}" target="blank">{{translate('View Peer Report')}}</a></li>                                         

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
