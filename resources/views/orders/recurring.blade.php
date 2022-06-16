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

        
        <div id="accordion">
        <h3>{{translate('Orders Filter by')}}</h3>
        <div class="pull-right clearfix" style="width:100%;">
            <form class="" id="sort_orders" action="" method="GET">
                
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="delivery_status" id="delivery_status">
                            <option value="">{{translate('Delivery Status')}}</option>
                            <option value="pending"   @isset($delivery_status) @if($delivery_status == 'pending') selected @endif @endisset>{{translate('Pending')}}</option>
                            <option value="on_review"   @isset($delivery_status) @if($delivery_status == 'on_review') selected @endif @endisset>{{translate('On review')}}</option>
                            <option value="on_delivery"   @isset($delivery_status) @if($delivery_status == 'on_delivery') selected @endif @endisset>{{translate('On delivery')}}</option>
                            <option value="delivered"   @isset($delivery_status) @if($delivery_status == 'delivered') selected @endif @endisset>{{translate('Delivered')}}</option>
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="pay_type" id="pay_type">
                            <option value="">{{translate('Payment Method')}}</option>
                            <option value="cash_on_delivery"   @isset($pay_type) @if($pay_type == 'cash_on_delivery') selected @endif @endisset>{{translate('Cash On Delivery')}}</option>
                            <option value="letzpay_payment"   @isset($pay_type) @if($pay_type == 'letzpay_payment') selected @endif @endisset>{{translate('Letzpay Payment')}}</option>
                             <option value="razorpay"   @isset($pay_type) @if($pay_type == 'razorpay') selected @endif @endisset>{{translate('Razorpay Payment')}}</option>
                            <option value="wallet"   @isset($pay_type) @if($pay_type == 'wallet') selected @endif @endisset>{{translate('Wallet')}}</option>
                            
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="payment_type" id="payment_type">
                            <option value="">{{translate('Payment Status')}}</option>
                            <option value="paid"  @isset($payment_status) @if($payment_status == 'paid') selected @endif @endisset>{{translate('Paid')}}</option>
                            <option value="unpaid"  @isset($payment_status) @if($payment_status == 'unpaid') selected @endif @endisset>{{translate('Un-Paid')}}</option>
                        </select>
                    </div>
                </div>

                <div class="box-inline pad-rgt pull-left">

                    <div  style="min-width: 150px;">

                        <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%" >

                            <i class="fa fa-calendar"></i>&nbsp;

                            <span></span> <i class="fa fa-caret-down"></i>

                        </div>

                    </div>

                </div>

                <input type="hidden" class="form-control" name="dateRangeStart">

                <input type="hidden" class="form-control" name="dateRangeEnd">

                

                @if(@$days_between != null && @$endDayFromCurrentDate == null)

                <input type="hidden" name="start" value="{{$days_between}}">

                @endif

               

                <input type="hidden" name="endDate" value="{{@$endDayFromCurrentDate}}">

                <input type="hidden" name="startDate" value="{{@$startDayFromCurrentDate}}">
                <br/>
                <br />
                <br />
                @if(@Auth::user()->staff->role->name=="Sorting Hub Manager" || @Auth::user()->staff->role->name=="Sorting Hub")
                    @php
                       $sorting_hub_id = 0;
                       if(@Auth::user()->staff->role->name=="Sorting Hub Manager"){
                            $sManager = \App\SortingHubManager::where('user_id',Auth::user()->id)->first();
                            if(!is_null($sManager)){
                                $sorting_hub_id = $sManager->sorting_hub_id;
                            }
                            
                       } else{
                        $sorting_hub_id =Auth::user()->id;
                       }
                    @endphp

                    @endif
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="sorting_hub_id" id="sorting_hub_id" onchange="get_cities_by_sorting_hub_id(this.value)">
                            
                            @if(Auth::user()->user_type=='admin')
                            <option value="">Select Sorting Hub</option>
                            @foreach($sorting_hubs as $key => $sorting_hub)
                                <option value="{{ $sorting_hub->user_id }}" @if($sorting_hub_id==$sorting_hub->user_id) selected @endif>{{ $sorting_hub->user->name }}</option>
                            @endforeach
                            @else
                            @foreach($sorting_hubs->where('user_id',$sorting_hub_id) as $key => $sorting_hub)
                                <option value="{{ $sorting_hub->user_id }}" @if($sorting_hub_id==$sorting_hub->user_id) selected @endif>{{ $sorting_hub->user->name }}</option>
                            @endforeach
                            @endif

                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="district" id="district">
                            <option value="">Select District</option>
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                        <div class="" style="min-width: 250px;">
                            <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code or name & hit Enter') }}">
                        </div>
                    </div>
                    <div class="box-inline pad-rgt pull-left">
                        <div class="">
                            <input type="submit" class="btn btn-success btn-sm" value="Apply Filters" onclick="sort_orders()">
                        </div>
                    </div>
                    <div class="box-inline pad-rgt pull-left">
                        <div class="">
                            <a href="{{ route('orders.recurring.admin') }}" class="btn btn-danger btn-sm">Clear Filters</a>
                        </div>
                    </div>
                
                    
                <!-- <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="customersearch" name="customersearch"@isset($customer_search) value="{{ $customer_search }}" @endisset placeholder="{{ translate('Type Name & hit Enter') }}">
                    </div>
                </div> -->
                
            </form>
        </div>
</div>
 
        


    </div>

    <div class="row" style="margin-top: 10px;">
		<!-- <a href="{{ route('orders.index.adminproduct') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('All products List')}}</a> -->
		<!-- <a href="{{ route('orders.index.exportallproduct') }}" class="btn btn-rounded btn-info pull-right" id="order_export_all" style="float:right;margin-right: 30px;">{{translate('Export All')}}</a>  -->
        <?php 
        if(isset($_GET['dateRangeStart']) || isset($_GET['dateRangeEnd'])){
            $from = $_GET['dateRangeStart'];
            $to = $_GET['dateRangeEnd'];
        }
        ?>

        <!-- <form method="GET" action="{{route('orders.index.exportproductwise')}}" name="order_export" id="order_export_date">
             <input type="hidden" name="date_from_export" id="date_from_export_pro">
            <input type="hidden"  name="date_to_export" id="date_to_export_pro">

            <input type="hidden" name="sorting_hub_id" id="sorting_hub_id" value="<?php echo Auth()->user()->id;?>">
            <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">
            <input type="hidden" name="payStatus" id="payStatus" value="<?php if(isset($pay_type)){ echo $pay_type;}?>">
            <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
            <input type="submit" name="submit" id="submit" value="Productwise Export" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
        </form> -->
        
		<form method="GET" action="{{route('orders.index.recurring_exportproduct')}}" name="order_export" id="order_export_date">
			<!-- <input type="text" name="date_from_export" id="date_from_export" value="<?php if(isset($from)){echo $from;}?>">
			<input type="text" name="date_to_export" id="date_to_export" value="<?php if(isset($to)){echo $to;}?>"> -->
            
            <input type="hidden" name="date_from_export" id="date_from_export" value="<?php if(isset($from)){echo $from;}?>">
            <input type="hidden" name="date_to_export" id="date_to_export" value="<?php if(isset($to)){echo $to;}?>">
            <input type="hidden" name="sorting_hub_id" id="sorting_hub_id" value="<?php echo Auth()->user()->id;?>">
            <input type="hidden" name="district" id="district" value="<?php if(isset($district)){ echo $district;}?>">

            <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">
            <input type="hidden" name="payStatus" id="payStatus" value="<?php if(isset($pay_type)){ echo $pay_type;}?>">
            <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
			<input type="submit" name="submit" id="submit" value="Export With Date" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
		</form>
	</div>
    <div class="panel-body">
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
             <!-- <table class="table table-striped res-table mar-no" id="example" cellspacing="0" width="100%"> -->
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Subscription Code')}}</th>
                    <th>{{translate('Order Code')}}</th>
                    <th>{{translate('Num. of Products')}}</th>
                    <th>{{translate('Customer')}}</th>
                    <th>{{translate('Address')}}</th>
                    <th>{{translate('Pin Code')}}</th>
                    @if(in_array(Auth::user()->user_type, ['admin']))
                    <th>{{translate('Sorting HUB')}}</th>
                    @endif
                    
                    <th>{{translate('Amount')}}</th>
                    <th>{{translate('Delivery Type/Date-Timing')}}</th>
                    <th>{{translate('Delivery Status')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th>{{translate('Payment Status')}}</th>
                    @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <th>{{translate('Refund')}}</th>
                    @endif
                    <th width="10%">{{translate('Options')}}</th>
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

                      $subscribed_code = \App\SubscribedOrder::where('id',$order->subscribed_id)->first('subscribed_code');

                    @endphp
                    @if($order != null)
                          
                        <tr>
                            <td>
                                {{ ($key+1)}}
                            </td>
                            <td>{{ $subscribed_code->subscribed_code }}</td>
                            <td>
                                {{ $order->code }} @if($order->viewed == 0) <span class="pull-right badge badge-info">{{ translate('New') }}</span> @endif
                                <br>{{date("jS F, Y H:i:s A", $order->date)}}
                            </td>
                            <td>
                                <!-- {{ count($order->orderDetails->where('seller_id', $admin_user_id)) }} -->
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
                                 <?php
                                    echo $peercode;
                                  ?>
                            </td>
                            
                            <td>
                           
                            {{@$address->address}}
                            </td>

                            <td>
                            {{$order->shipping_pin_code}}
                            </td>
                            

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
                            if($order_id->payment_type=='wallet'){
                                $total_amount = $order->wallet_amount;
                            }
                            
                        @endphp
                            <td>{{ single_price($total_amount) }}
                            
                                <!-- {{ single_price($order->orderDetails->where('order_id', $order_id->id)->sum('price') + $order->orderDetails->where('order_id', $order_id->id)->sum('tax')) }} -->
                                <!-- {{ single_price($order->orderDetails->where('order_id', $order_id->id)->where('delivery_status','!=', 'return')->sum('price') + $order->orderDetails->where('order_id', $order_id->id)->sum('tax') - $order->orderDetails->where('order_id', $order_id->id)->where('delivery_status','!=', 'return')->sum('peer_discount'))}} -->
                                
                            </td>
                            <td>{{ ucfirst($order->delivery_type) }}<br>
                                @if($order->delivery_type != 'normal')
                                {{ date('d M,Y',strtotime($order->delivery_date)) }}
                                <br>
                                {{ $order->delivery_slot }}
                                @endif
                            </td>
                            <td>
                                 {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                 <br>{{ date('d M,Y H:i:s', strtotime($order->updated_at)) }}
                               

                               
                            </td>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                            </td>
                            <td>
                                <span class="badge badge--2 mr-4"><i class="bg-green"></i> {{ $order->payment_status }}</span>
                            </td>
                            @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                                <td>
                                    @if (count($order->refund_requests) > 0)
                                        {{ count($order->refund_requests) }} {{ translate('Refund') }}
                                    @else
                                        {{ translate('No Refund') }}
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{ route('orders.show', encrypt($order->id)) }}">{{translate('View')}}</a></li>
                                        <li><a href="{{ route('seller.invoice.download', $order->id) }}" target="_blank">{{translate('Full Invoice Print')}}</a></li>
                                        @if(@Auth::user()->staff->role->name!="Sorting Hub Manager" && @Auth::user()->staff->role->name!="Call Center Department")
                                        <!-- <li><a onclick="confirm_modal('{{route('orders.destroy', $order->id)}}');">{{translate('Delete')}}</a></li> -->
                                        @endif
                                        </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @if(@Auth::user()->staff->role->name=="Sorting Hub")
                <span class="pull-right badge badge-success" style="padding:10px;">Total amount = {{ single_price($total_today_amount-$refund_amount) }}</span>
                @endif
            </tbody>
        </table>

        <div class="clearfix">
            <div class="pull-right">
                {{ $orders->appends(request()->input())->links() }}
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
            
            get_cities_by_sorting_hub_id("{{ $sorting_hub_id }}","{{ $district }}");
            $('#example').DataTable({
                "sScrollX": "100%",
                "sScrollXInner": "110%",
                "bScrollCollapse": true,
                 "paging": false
               
               //   "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
               //   "scrollX":true,
               //   "paging": false,
               //   "sScrollX": "100%",
               // "bScrollCollapse": true,
               //  //"pageLength": 50,
               // // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
               //  // "pagingType": "full_numbers"
               //  dom: 'lBfrtip',
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
