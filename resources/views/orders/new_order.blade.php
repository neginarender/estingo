@extends('layouts.app')

@section('content')
<!-- Basic Data Tables -->

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<style>
.bootstrap-select {width:150px !important}
#container .table-striped>tbody>tr:nth-child(2n+1) {
    background-color: rgb(0 0 0 / 9%);
}
.ui-accordion .ui-accordion-content{
        overflow:inherit!important;
    }
</style>
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">

        
        <div id="accordion">
        <h3>{{translate('Orders Filter by')}}</h3>
        <div class="pull-right clearfix" style="width:100%;">
            <form class="" id="sort_orders" action="" method="GET">
            <div class="box-inline pad-rgt pull-left">

            <div  style="min-width: 150px;">
            <select class="form-control demo-select2" name="order_type">
                <option value="">Select Order Type</option>
                <option value="fresh" @if($order_type=="fresh") selected @endif>Fresh</option>
                <option value="grocery" @if($order_type=="grocery") selected @endif>Grocery</option>
            </select>
            </div>
            </div>
                {{-- <input  type="text"  name="datetime"> --}}
                <div class="box-inline pad-rgt pull-left">

                    <div  style="min-width: 150px;">

                        <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%" >

                            <i class="fa fa-calendar"></i>&nbsp;

                            <span></span> <i class="fa fa-caret-down"></i>

                        </div>

                    </div>

                    <input type="hidden" class="form-control" name="dateRangeStart">

                     <input type="hidden" class="form-control" name="dateRangeEnd">


                     <input type="hidden" name="endDate" value="{{@$end_date}}">

                <input type="hidden" name="startDate" value="{{@$start_date}}">

                </div>

                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 250px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code or name & hit Enter') }}">
                        
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                        <div class="">
                            <button type="submit" class="btn btn-info">Filter</button>
                        </div>
                    </div>
            </form>
        </div>
</div>
        


    </div>
    <div class="panel-body table-responsive" >
        <table class="table table-striped"  id="example" cellspacing="0" width="100%">
            <thead>
                <tr style="color:#010d1a">
                    <th>#</th>
                    <th>{{translate('Order Code')}}</th>
                    <th>{{translate('Num. of Products')}}</th>
                    <th>{{translate('Customer')}}</th>
                    <th>{{translate('Address')}}</th>
                    {{-- <th>{{translate('Pin Code')}}</th> --}}
                    <th>{{translate('Sorting HUB')}}</th>
                    <th>{{translate('Amount')}}</th>
                    @if($order_status['id'] != 4 && $order_status['id'] != 9 && $order_status['id'] != 8 && $order_status['id'] != 7)
                    <th>{{translate('Change Status')}}</th>
                    @endif
                    <th>{{translate('Delivery Type/Date-Timing')}}</th>
                    <th>{{translate('Delivery Status')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th>{{translate('Payment Status')}}</th>
                    {{-- <th>{{translate('Order Date')}}</th> --}}
                    @if($current_order_status >= 3)
                    <th>{{translate('Assigned To')}}</th>
                    @endif
                    @if($current_order_status == 2)
                    <th>{{translate('Download Invoice')}}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($new_orders as $key => $value)
                        @php
                        $address = json_decode($value->shipping_address); 
                        $sortinghub = getShortinghub($value->shipping_pin_code);
                        $total_price_sum = 0;
                        $total_price = 0;
                        $total_shippimg_cost = 0;
                        $total_peer_discount = 0;
                        $total_item = 0;
                        if($value->no_of_items == 0 && $value->payable_amount == 0){
                            
                            $orderDetail = \App\OrderDetail::where('sub_order_id',$value->sub_order_id)->select('price','shipping_cost','peer_discount','quantity')->get();
                            foreach($orderDetail as $k => $v){
                                $total_price += $v->price;
                                $total_shippimg_cost += $v->shipping_cost;
                                $total_peer_discount += $v->peer_discount;
                                $total_item += $v->quantity;
                            }
                            $value->payable_amount = $total_price - ($total_shippimg_cost + $total_peer_discount);
                            $value->no_of_items = $total_item;
                           
                            
                        }
                        //dd($sortinghub->deliveryBoy);
                        @endphp
                        <tr>
                            <td>{{ ($key+1)}}</td>
                            <td>{{$value->code}} </br> {{ucfirst($value->delivery_name)}} </br> {{$value->created_at}}</td>
                            <td>{{$value->no_of_items}}</td>
                            <td>{{translate($address->name)}}</td>
                            <td>{{translate($address->address)}}</td>
                            {{-- <td>{{translate($value->shipping_pin_code)}}</td> --}}
                            <td>{{translate($sortinghub->user->name)}}</td>
                            <td>{{translate($value->payable_amount)}}</td>
                            @if($order_status['id'] != 4 && $order_status['id'] != 9 && $order_status['id'] != 8 && $order_status['id'] != 7)
                                <td>
                                {{-- @dd($order_status); --}}
                                    <button class="selectpicker change_order_status" sub_order_id = {{$value->sub_order_id}} value={{$order_status['id']}} data-style="btn-primary">{{$order_status['name']}}</button>  
                                {{-- <select class="selectpicker change_order_status" data-style="btn-primary" @if(auth()->user()->user_type == "admin") {{"disabled"}} @endif>
                                    @foreach ($order_status as $k=>$v)
                                        <option style = "" sub_order_id = {{$value->sub_order_id}} value={{$v->id}} @if($value->order_status_id == $v->id) {{"selected"}} @endif>{{$v->name}}</option>    
                                    @endforeach
                                </select> --}}
                                </td>
                            @endif    
                            <td>{{translate(ucfirst($value->delivery_type).'/'.$value->expected_delivery)}}</td>
                            <td>{{translate($value->delivery_status)}}</td>
                            <td>{{translate($value->payment_mode)}}</td>
                            <td>{{translate($value->payment_status)}}</td>
                            {{-- <td>{{translate($value->created_at)}}</td> --}}
                            @if($current_order_status >= 3)
                            <td>
                              <select class="form-control demo-select2 select2-hidden-accessible assign_order" data-style="btn-primary" @if($current_order_status != 3) {{"disabled"}} @endif @if(auth()->user()->user_type == "admin") {{"disabled"}} @endif>
                                   <option val="">Select Delivery Boy</option>
                               @foreach ($sortinghub->deliveryBoy as $k=>$v)
                                   <option style = "" sub_order_id = {{$value->sub_order_id}} value={{$v->id}} @if($value->assign_to == $v->id) {{"selected"}} @endif>{{$v->user->name}}</option>    
                               @endforeach  
                               
                              </select>
                            </td>
                            @endif
                            @if($current_order_status == 2)
                            <td>
                                <a href="{{ route('orders.suborderinvoice.download', encrypt($value->sub_order_id)) }}" target="_blank">
                                <i class="fa fa-download" aria-hidden="true"></i>
                                </a>
                            </td>
                            @endif
                        </tr>
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <div class="pull-right">
                {{ $new_orders->appends(request()->input())->links() }}
            </div>
        </div>
        
    </div>
</div>

@endsection


@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/metro/4.4.3/css/metro-components.min.css"> --}}


 <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                 "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 "scrollX":true,
                 "paging": false,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                dom: 'lBfrtip'
                /*buttons: [
                    'excelHtml5', 'csvHtml5'
                ]*/
            } );
            
        } );
         $(function() {

var returnDays =  $('input[name="start"]').val();

var endDate = $('input[name="endDate"]').val();

var startDate = $('input[name="startDate"]').val();



if(returnDays != "" && startDate == ""){ 

             

    var start =moment().subtract(returnDays, 'days');

}

else if(startDate != "" ){

    var start =moment().subtract(startDate, 'days');

}

else{

    var start = moment().subtract(89, 'days');

}



if(endDate != "" && endDate != startDate){          

   var end =moment().subtract(endDate , 'days');

}else{

    var end = moment();

}

  //var end = moment();



function cb(start, end) {

    $('#reportrange span').html(start.format('MMMM D, YYYY hh:mm:ss A') + ' - ' + end.format('MMMM D, YYYY hh:mm:ss A'));

    lstart = moment($('#reportrange').data('daterangepicker').startDate).format('DD-MM-YYYY hh:mm:ss A'),

    lend = moment($('#reportrange').data('daterangepicker').endDate).format('DD-MM-YYYY hh:mm:ss A');

    $('input[name="dateRangeStart"]').val(lstart);

    $('input[name="dateRangeEnd"]').val(lend);
	
	//---25-09-2021
	// $('#date_from_export').val(lstart);

 //    $('#date_to_export').val(lend);
    

}



$('#reportrange').daterangepicker({

    startDate: start,

    endDate: end,

    maxDate: end,

    timePicker: true,

    ranges: {

       'Today': [moment(), moment()],

       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],

       'Last 7 Days': [moment().subtract(6, 'days'), moment()],

       'Last 30 Days': [moment().subtract(29, 'days'), moment()],

       'Last 90 Days': [moment().subtract(89, 'days'), moment()],

       'This Month': [moment().startOf('month'), moment().endOf('month')],

       //'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]

    }

}, cb);

/*$('#reportrange').on('apply.daterangepicker', (e, picker) => {

    $('#sort_orders').submit();

});*/

cb(start, end);

});

//change order status
$(".change_order_status").on('click',function(){
    var sub_order_id = $(this).attr('sub_order_id');
    var order_status_id = $(this).val(); 
    $.post('{{ route('orders.change-order-status') }}', {_token:'{{ @csrf_token() }}',sub_order_id:sub_order_id,order_status_id:order_status_id}, function(res){
        console.log(res);
                if(res.status == true){
                    showAlert('success', res.message);
                    location.reload();

                }else{
                    showAlert('danger', res.message);

                }
                
            });
});

//assign to delivery boy
$(".assign_order").on('change',function(){
    var sub_order_id = $('option:selected', this).attr('sub_order_id');
    var delivery_boy_id = $(this).val();
    if(delivery_boy_id){
        $.post('{{ route('orders.assign-sub-order') }}', {_token:'{{ @csrf_token() }}',sub_order_id:sub_order_id,delivery_boy_id:delivery_boy_id}, function(res){
        console.log(res);
                if(res.status == true){
                    showAlert('success', res.message);
                    location.reload();

                }else{
                    showAlert('danger', res.message);

                }
                
            });

    } 
    
});
    </script>
@endsection
