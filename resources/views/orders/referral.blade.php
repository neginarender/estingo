@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">

<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">Referral Code Order List</h3>
    </div>
    <div class="panel-body">
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
             <!-- <table class="table table-striped res-table mar-no" id="example" cellspacing="0" width="100%"> -->
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Order Code')}}</th>
					<th>{{translate('Num. of Products')}}</th>
					<th>{{translate('Customer')}}</th>
					<th>{{translate('Address')}}</th>
                    <th>{{translate('Pin Code')}}</th>
					@if(in_array(Auth::user()->user_type, ['admin']))
                    <th>{{translate('Sorting HUB')}}</th>
                    @endif
					@if(Auth::user()->staff!='')
                        @if(Auth::user()->staff->role->name == "Sorting Hub" || Auth::user()->staff->role->name == "Sorting Hub Manager")
                        <th>{{translate('Assign Order')}}</th>
                        @endif
                    @endif
                    <th>{{translate('Amount')}}</th>
					<th>{{translate('Delivery Status')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th>{{translate('Payment Status')}}</th>
					<th>{{translate('Referral Code Status')}}</th>
                </tr>
            </thead>
            <tbody>
				@php 
				$total_today_amount=0;
				$refund_amount = 0;
				@endphp
                @foreach ($orders as $key => $order_id)
                    @php
                        $order = \App\Order::find($order_id->id);
                        $refund_amount += \App\RefundRequest::where(array('refund_status'=>1,'order_id'=>$order_id->id))->sum('refund_amount');
                        $total_today_amount+=$order_id->grand_total;

                         $delivery_peercode = \App\ReferalUsage::where('order_id',$order_id->id)->first('referal_code');
                        if(!empty($delivery_peercode)){
                            $peercode = $delivery_peercode->referal_code;
                        }
                        else{
                            $peercode = 'NA';
                        }
                    @endphp
                    @if($order != null)
                        <tr>
                            <td>
                                {{ ($key+1)}}
                            </td>
                            <td>
                                {{ $order->code }} @if($order->viewed == 0) <span class="pull-right badge badge-info">{{ translate('New') }}</span> @endif
                                <br>{{date("jS F, Y h:i:s A", $order->date)}}
                            </td>
							<td>
                                {{ $order->orderDetails->where('order_id', $order_id->id)->sum('quantity') }}
                            </td>
							<td>
                             @php
                            $address = json_decode($order->shipping_address);
                            @endphp
                                @if ($order->user != null)
                                    {{ $order->user->name }}<br>
                                    {{$address->phone}}
                                @else
                                Guest-
                                    {{@$address->name}}<br>
                                    {{@$address->phone}}

                                @endif
                                <br>
                                 <?php echo $peercode;
                                  ?>
                            </td>
							<td>{{@$address->address}}</td>
                            <td>{{$order->shipping_pin_code}}</td>
							@if(Auth::user()->staff!='')
                                @if(Auth::user()->staff->role->name == "Sorting Hub" || Auth::user()->staff->role->name == "Sorting Hub Manager")
                                            @php
                                            $sortinghubid = (Auth::user()->staff->role->name == "Sorting Hub Manager") ? Auth::user()->sortinghubmanager->sorting_hub_id : Auth::user()->id;
                                            $getDeliveryBoy = \App\DeliveryBoy::where('sorting_hub_id',$sortinghubid)->get();
                                            $getAssignedBoy = \App\AssignOrder::where('order_id',$order->id)->first('delivery_boy_id');
                                            @endphp
                                            <input type="hidden" class="order_id" value="{{$order->id}}">
                                            <td>
                                            <select class="form-control demo-select2 assign_order"  data-minimum-results-for-search="Infinity"  
                                            <?php if(isset($delivery_status) && $delivery_status == 'delivered'){
                                                    echo 'disabled';
                                                }else{
                                                    echo '';
                                                } ?>>
                                                <option value="">{{translate('Select Delivery')}}</option>
                                                <?php foreach($getDeliveryBoy as $key=>$value){
                                                $delivery_boy_name = \App\User::where('id',$value['user_id'])->first('name');
                                                ?>
                                                <option value="<?php echo $value['id']; ?>"<?php echo $getAssignedBoy['delivery_boy_id'] == $value['id']?"selected":"";?> order_id = {{$order->id}}><?php  echo $delivery_boy_name['name']; ?></option>
                                                <?php } ?>
                                                
                                            </select>
                                            </td>
                                        @endif
                            @endif
							@if(in_array(Auth::user()->user_type, ['admin']))
                            <td>
                            @php
                            $sortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
                            if(!empty($sortingHub)){
                              echo  @$sortingHub->user->name;

                            }else{
                                echo "Not Available";
                            }
                            @endphp
                            </td>
                            @endif
							@php
								if($order->wallet_amount==0){
									$total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('shipping_cost') - $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('peer_discount');
								}else{
									$total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost'); 
								}

								if($order->referal_discount > 0){
									$referral = $order->referal_discount;
								}
								if($order->wallet_amount > 0){
									$wallet = $order->wallet_amount;
									$total_amount = $total_amount - $wallet;
								}
                            @endphp
                            <td>{{ single_price($total_amount) }}</td>
							<td>
                                 {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                 <br>{{ date('d M,Y H:i:s', strtotime($order->updated_at)) }}
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}</td>
                            <td>
                                <span class="badge badge--2 mr-4"><i class="bg-green"></i> {{ $order->payment_status }}</span>
                            </td>
							<td><span class="badge badge--2 mr-4"><i class="bg-green"></i> {{ translate('Applied') }}</span></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <div class="pull-right">
                {{-- $orders->appends(request()->input())->links() --}}
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
                buttons: [
                    'excelHtml5', 'csvHtml5'
                ]
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
	$('#date_from_export').val(lstart);

    $('#date_to_export').val(lend);
    

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



    </script>
@endsection
