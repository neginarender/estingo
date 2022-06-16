@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<!--===================================================-->

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
    <div class="row">
        <?php
            $all_total = 0; 
            foreach($all_orders as $key=> $row){
                $all_total += $row->total_refferaldiscount;
            }
        ?>
        <div class="col-sm-12">
           <a href="{{ route('peer_partner.createpeer')}}" class="btn btn-rounded btn-info pull-right">{{translate('Total Amount :')}} {{$all_total}}</a>
          
        </div>
    </div>

    <br>
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <!-- <h3 class="panel-title pull-left pad-no">Today's Orders ( <?php echo date("d-m-Y"); ?> )</h3> -->
        <div class="pull-left clearfix">
        <?php 

            $from_date = date('Y-m-d'); 
            if(empty($start_date))
            {
               $start_date = $from_date;
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
            <form method="post" action="{{ route('peer_partner.peer_commisionbydate') }}">
          @csrf
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">Start Date</label>
                    <div class="" style="">
                            <input type="date" class="datepicker" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>">
                    </div>
              </div>

              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Date</label>
                    <div class="" style="">
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
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Refferal Code')}}</th>
                    <th>{{translate('Total Amount')}}</th>
                    <th>{{translate('Total Commission')}}</th>
                    <th>{{translate('Date')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_orders as $key => $row)
                    
                    @if($all_orders != null)
                          
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$row->refral_code}}</td>
                            <td>{{$row->total_orderamount}}</td>
                            <td> {{$row->total_refferaldiscount}}</td>
                            <td> {{date('d-m-Y', strtotime($row->created_at)) }}</td>                       
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
