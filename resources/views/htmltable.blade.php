@extends('layouts.app')
@section('content')
	<br><br>


	<div class="container-fluid">
    <form class="" id="sort_orders" action="" method="GET">
                    <div class="">
                        <label>Select Date</label></br>
                        <input type="date" class="form-control"  style = "width:30%;float:left" name="start_date" @isset($start_date) value="{{ $start_date }}" @endisset></br>
                    </div>

                    <div class="">
                    </br>
                        <input type="date" class="form-control" style = "width:30%" name="end_date" @isset($end_date) value="{{ $end_date }}" @endisset>
                    </div>

                    <div class="">
                        <input type="text" class="form-control" id="search" style = "width:30%;float:right;margin-bottom: 20px" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code or name & hit Enter') }}">
                    </div>
                     <div class="">
                        <button type="submit" >Submit</button>
                    </div>
                    
    </form>

    <div class="row" style="margin-top: 10px;">
		<!-- <a href="{{ route('orders.index.adminproduct') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('All products List')}}</a>
		<a href="{{ route('orders.index.exportallproduct') }}" class="btn btn-rounded btn-info pull-right" id="order_export_all" style="float:right;margin-right: 30px;">{{translate('Export All')}}</a>  -->
<progress value="" id="download" max="65">Download</progress>

        <form method="GET" action="{{route('finalorders.exportproductwise')}}" name="order_export" id="order_export_date">
            <input type="hidden" name="date_from_export" id="date_from_export_pro" @isset($start_date) value="{{ $start_date }}" @endisset>
            <input type="hidden"  name="date_to_export" id="date_to_export_pro" @isset($end_date) value="{{ $end_date }}" @endisset>
            <input type="hidden" name="sorting_hub_id" id="sorting_hub_id" value="<?php echo Auth()->user()->id;?>">
            <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">
            <input type="hidden" name="payStatus" id="payStatus" value="<?php if(isset($pay_type)){ echo $pay_type;}?>">
            <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
            <input type="submit" name="submit" id="submit" onClick="showProgress()" value="Productwise Export" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
        </form>

		<form method="GET" action="{{route('finalorders.exportproduct')}}" name="order_export" id="order_export_date">
			 <input type="hidden" name="date_from_export" id="date_from_export" @isset($start_date) value="{{ $start_date }}" @endisset>
			<input type="hidden"  name="date_to_export" id="date_to_export" @isset($end_date) value="{{ $end_date }}" @endisset>

            <!-- <input type="text" name="date_from_export"  value="<?php //if(isset($_GET['dateRangeStart'])){echo $_GET['dateRangeStart'];}?>">
            <input type="text" name="date_to_export"  value="<?php //if(isset($_GET['dateRangeEnd'])){echo $_GET['dateRangeEnd'];}?>"> -->

            <input type="hidden" name="sorting_hub_id" id="sorting_hub_id" value="<?php echo Auth()->user()->id;?>">
            <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">
            <input type="hidden" name="payStatus" id="payStatus" value="<?php if(isset($pay_type)){ echo $pay_type;}?>">
            <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
			<input type="submit" name="submit" id="submit" value="Export With Date" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
		</form>
	</div>
<table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Order Code</th>
                <th>Num. of Products</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Pin Code</th>
                <th>Sorting HUB</th>
                <th>Amount</th>
                <th>Delivery Type/Date-Timing</th>
                <th>Delivery Status</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
                   @foreach ($orders as $key => $order)
      
                <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>{{$order->order_code}} {{$order->order_date}}</td>
                    <td>{{$order->no_of_items}}</td>
                    <td>{{$order->customer_name}}</td>

                    @php 

                    $res = json_decode($order->shipping_address, true);

                     @endphp
                  
                    <td>{{$res['address']}} {{$res['city']}} {{$res['state']}} {{$res['country']}}</td>  
                    <td>{{$order->pincode}}</td>
                    <td>{{$order->sortinghub_id}}</td>
                    <td>₹.{{$order->grand_total}}</td>
                    <td>{{$order->delivery_type}} </td>
                    <td>{{$order->delivery_status}}</td>
                    <td>{{$order->payment_method}}</td>
                    <td>{{$order->payment_status}}</td>
                    <td>  <div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Action
    </button>
    <ul class="dropdown-menu">

         <li><a href="{{ route('orders.showhtml', encrypt($order->order_id)) }}">{{translate('View')}}</a></li>
         <li><a href="{{ route('seller.invoice.download', $order->order_id) }}" target="_blank">{{translate('Full Invoice Print')}}</a></li>
    </ul>
  </div>
</div></td>

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

<script>
var progress=0;
function showProgress() {
  
  

  var max=100;

var downloadProgress= document.getElementById('download'); 


if(progress<=200){

progress+=0.5;

 downloadProgress.value=progress;



  } setTimeout('showProgress()',max);
 document.getElementById("order_export_date").submit();
 window.location.reload();
}


</script>
	{{-- <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>


 <script type="text/javascript">
     
</script> --}}
@endsection
