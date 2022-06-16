@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<style>
    .ui-accordion .ui-accordion-content{
        overflow:inherit!important;
    }
</style>
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <!-- <div id="accordion"> -->
        <h3>{{translate('Reccurring Refunds')}}</h3>
        <!-- <div class="pull-right clearfix" style="width:100%;">
            <div class="box-inline pad-rgt pull-left">
                <div class="" style="min-width: 250px;">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code or name & hit Enter') }}">
                </div>
            </div>    
        </div> -->
        <!-- </div> -->
    </div>

    <div class="panel-body">
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
            
            <thead>
                <tr>
                    <th>{{ translate('SR.no.')}}</th>
                    <th>{{ translate('Product')}}</th>
                    <th>{{ translate('customer')}}</th>
                    <th>{{ translate('Start Date')}}</th>
                    <th>{{ translate('End Date')}}</th>
                    <th>{{ translate('Amount')}}</th>
                    <th>{{ translate('Quantity')}}</th>
                    <th>{{ translate('Total Amount')}}</th>
                    <th>{{ translate('Payable Amount')}}</th>
                    <th>{{ translate('Sorting Hub')}}</th>
                    <th>{{ translate('Order Created')}}</th>
                    <th>{{ translate('Refund Amount')}}</th>
                </tr>
            </thead>
            <tbody>

           @foreach ($orders as $key => $order)
            @php
                $sortingname = app\User::where('id', $order->sorting_id)->select('name')->first();
                $username = app\User::where('id', $order->user_id)->select('name','phone')->first();
            @endphp
                <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>
                        
                        <a href="{{ route('product', $order->slug) }}" target="_blank" class="media-block">
                        <div class="media-left">
                            <img loading="lazy"  class="img-md" src="{{ my_asset($order->thumbnail_img)}}" alt="Image" style="width:35px">
                        </div>
                        <div class="media-body">{{ __($order->name) }}</div>
                        <div style="font-weight: bold;color: #09f">{{__($order->sku)}}</div>
                        <div style="font-weight: bold;color: #000">{{ $order->subscribed_code }}</div>
                    </a>
                    </td>
                    <td>{{ @$username->name }}<br>{{ @$username->phone }}</td>
                    <td>{{date('d-m-Y', strtotime($order->start_date))}}</td>
                    <td>{{date('d-m-Y', strtotime($order->end_date))}}</td>
                    <td>{{ $order->price }}</td>
                    <td>{{ $order->quantity }}</td>
                    <td>{{ $order->total }}</td>
                    <td>{{ $order->payable_amount }}</td>
                    <td>{{ $sortingname->name }}<br>{{ $order->pincode }}</td>
                    <td>{{date('d-m-Y h:i:s' , strtotime($order->created_at))}}</td>
                    <td>{{ $order->refund_amount }}<br>
                        @if($order->refund_status==0)

                       


                            <a onclick="confirm_refund('{{route('orders.transfer', $order->r_id)}}');" class="btn btn-primary">{{translate('Transfer Amount')}}</a>
                        @else
                            <b>Refunded</b>
                        @endif    

                    
                </td>
            </tr>    
            @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <div class="pull-right">
                {{ $orders->links() }}
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

 <script type="text/javascript">
        $(document).ready(function() {
           
           
            $('#example').DataTable( {
                 "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 "scrollX":true,
                 "paging": false,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                dom: 'lBfrtip',
                // buttons: [
                //     'excelHtml5', 'csvHtml5'
                // ]
            } );
        } );
    </script>
    
    <script type="text/javascript">

        function sort_orders(el){
            $('#sort_orders').submit();

        }
        $('.assign_order').on('change',function(){
            var order_id = $('option:selected', this).attr('order_id');
            console.log(order_id);
		
			var delivery_boy_id = $(this).val();
			 $.post('{{ route('sorthinghub.assign_order') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,delivery_boy_id:delivery_boy_id}, function(data){
                showAlert('success', 'Order has been assigned.');
            });
		
	});


    
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

    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    lstart = moment($('#reportrange').data('daterangepicker').startDate).format('DD-MM-YYYY'),

    lend = moment($('#reportrange').data('daterangepicker').endDate).format('DD-MM-YYYY');

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

$('#reportrange').on('apply.daterangepicker', (e, picker) => {

    $('#sort_orders').submit();

});

cb(start, end);

});

function get_cities_by_sorting_hub_id(sorting_hub_id,district_id){
    $.post("{{ route('get_cities_by_soroting_hub_id') }}",{_token:"{{ csrf_token() }}",sorting_hub_id:sorting_hub_id},function(data){
            var district = $("#district");
            district.empty();
            district.append("<option value=''>Select District</option>");
            $.map(data,function(item){
                var selected = "";
                if(district_id==item.id){
                    selected = "selected";
                }
                district.append("<option value="+item.id+" "+selected+">"+item.name+"</option>");
            });
            $('.demo-select2').select2();
});
}


    </script>
@endsection
