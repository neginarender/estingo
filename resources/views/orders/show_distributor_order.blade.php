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

            $from_time = '00:00';
            if(empty($s_time))
            {
               $s_time = $from_time;
            }else{
               $s_time = $s_time;
            }

            $to_time = '23:59';
            if(empty($e_time))
            {
               $e_time = $to_time;
            }else{
               $e_time = $e_time;
            }
        ?>
        <form method="post" action="{{ route('distributororders.show', encrypt($id)) }}">
          @csrf
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">Select Date</label>
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
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">Start Time</label>
                    <div class="" style="">
                           <input type="time" id="start_time" name="start_time" value="<?php echo $s_time; ?>">
                    </div>
              </div>
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Time</label>
                    <div class="" style="">
                           <input type="time" id="end_time" name="end_time" value="<?php echo $e_time; ?>">
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
                    <th>{{translate('Product ID')}}</th>
                    <th>{{translate('Distributor Name')}}</th>
                    <th>{{translate('Product Name')}}</th>
                    <th>{{translate('Quantity')}}</th>
                    <th>{{translate('Weight')}}</th>
                    <th>{{translate('Date')}}</th>
                    <!-- 
                    <th>{{translate('Price')}}</th>
                    <th>{{translate('Total Price')}}</th> -->
                    
                    
                </tr>
            </thead>
            <tbody>
                
                @foreach ($all_orders as $key => $row)
                    
                    @if($all_orders != null)
                          
                        <tr>
                        
                            <td>{{$key+1}}</td>
                            <td>{{$row->product_id}}</td>
                             <td>{{App\Distributor::where('id', $distributorids)->pluck('name')->first()}}</td>
                            <td>{{App\Product::where('id', $row->product_id)->pluck('name')->first()}}</td>
                            <td> {{$row->total_quantity}}</td>
                            <!-- <td> {{$row->variation}}</td> -->
                            <td>
                            @php
                                if($row->variation != NULL){
                                    echo $row->variation;
                                }else{
                                    $productDetail = App\product::where('id',$row->product_id)->first()->choice_options;
                                    $data = json_decode($productDetail);
                                    
                                    foreach($data[0]->values as $key => $value){
                                        echo $value;
                                    }
                                }
                            @endphp
                            </td>
                            <td> {{$row->created_at}}</td>
                            <!-- <td>{{App\ProductStock::where('product_id', $row->product_id)->pluck('price')->first()}}</td>
                            <td>{{$row->price}}</td>   -->                          
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
