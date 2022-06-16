<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link name="favicon" type="image/x-icon" href="{{ static_asset(\App\GeneralSetting::first()->favicon) }}" rel="shortcut icon" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!--Bootstrap Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/bootstrap.min.css')}}" rel="stylesheet">

    <!--active-shop Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/active-shop.min.css')}}" rel="stylesheet">

    <!--active-shop Premium Icon [ DEMONSTRATION ]-->
    <link href="{{ static_asset('css/demo/active-shop-demo-icons.min.css')}}" rel="stylesheet">

    <!--Font Awesome [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">

    <!--Switchery [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/switchery/switchery.min.css')}}" rel="stylesheet">

    <!--DataTables [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/datatables/media/css/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ static_asset('plugins/datatables/extensions/Responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">

    <!--Select2 [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/select2/css/select2.min.css')}}" rel="stylesheet">

    <link href="{{ static_asset('css/bootstrap-select.min.css')}}" rel="stylesheet">

    <!--Chosen [ OPTIONAL ]-->
    {{-- <link href="{{ static_asset('plugins/chosen/chosen.min.css')}}" rel="stylesheet"> --}}

    <!--Bootstrap Tags Input [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.css') }}" rel="stylesheet">
    <link href="{{ static_asset('plugins/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <!--Summernote [ OPTIONAL ]-->
    <link href="{{ static_asset('css/jodit.min.css') }}" rel="stylesheet">

    <!--Theme [ DEMONSTRATION ]-->
    <!-- <link href="{{ static_asset('css/themes/type-full/theme-dark-full.min.css') }}" rel="stylesheet"> -->
    <link href="{{ static_asset('css/themes/type-c/theme-navy.min.css') }}" rel="stylesheet">

    <!--Spectrum Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/spectrum.css')}}" rel="stylesheet">

    <!--Custom Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/custom.css')}}" rel="stylesheet">
    
    
    


    <!--JAVASCRIPT-->
    <!--=================================================-->

    <!--jQuery [ REQUIRED ]-->
    <script src=" {{static_asset('js/jquery.min.js') }}"></script>


    <!--BootstrapJS [ RECOMMENDED ]-->
    <script src="{{ static_asset('js/bootstrap.min.js') }}"></script>


    <!--active-shop [ RECOMMENDED ]-->
    <script src="{{ static_asset('js/active-shop.min.js') }}"></script>

    <!--Alerts [ SAMPLE ]-->
    <script src="{{ static_asset('js/demo/ui-alerts.js') }}"></script>

    <!--Switchery [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/switchery/switchery.min.js')}}"></script>

    <!--DataTables [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/datatables/media/js/jquery.dataTables.js')}}"></script>
    <script src="{{ static_asset('plugins/datatables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{ static_asset('plugins/datatables/extensions/Responsive/js/dataTables.responsive.min.js')}}"></script>

    <!--DataTables Sample [ SAMPLE ]-->
    <script src="{{ static_asset('js/demo/tables-datatables.js')}}"></script>

    <!--Select2 [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/select2/js/select2.min.js')}}"></script>
    <script src="{{ static_asset('js/bootstrap-select.min.js')}}"></script>

    <!--Summernote [ OPTIONAL ]-->
    <script src="{{ static_asset('js/jodit.min.js') }}"></script>

    <!--Bootstrap Tags Input [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js')}}"></script>

    <!--Bootstrap Validator [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-validator/bootstrapValidator.min.js') }}"></script>

    <!--Bootstrap Wizard [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>

    <!--Bootstrap Datepicker [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

    <!--Form Component [ SAMPLE ]-->
    <script src="{{static_asset('js/demo/form-wizard.js')}}"></script>

    <!--Spectrum JavaScript [ REQUIRED ]-->
    <script src="{{ static_asset('js/spectrum.js')}}"></script>

    <!--Spartan Image JavaScript [ REQUIRED ]-->
    <script src="{{ static_asset('js/spartan-multi-image-picker-min.js') }}"></script>

    <!--Custom JavaScript [ REQUIRED ]-->
    <script src="{{ static_asset('js/custom.js')}}"></script>

    <script type="text/javascript">

        $( document ).ready(function() {
            //$('div.alert').not('.alert-important').delay(3000).fadeOut(350);
            if($('.active-link').parent().parent().parent().is('ul')){
                $('.active-link').parent().parent().addClass('in');
                $('.active-link').parent().parent().parent().addClass('in');
            }
            if($('.active-link').parent().parent().is('li')){
                $('.active-link').parent().parent().addClass('active-sub');
            }
            if($('.active-link').parent().is('ul')){
                $('.active-link').parent().addClass('in');
            }

            if ($('#lang-change').length > 0) {
                $('#lang-change .dropdown-item a').each(function() {
                    $(this).on('click', function(e){
                        e.preventDefault();
                        var $this = $(this);
                        var locale = $this.data('flag');
                        $.post('{{ route('language.change') }}',{_token:'{{ csrf_token() }}', locale:locale}, function(data){
                            location.reload();
                        });

                    });
                });
            }

        });

    </script>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    @if (\App\BusinessSetting::where('type', 'google_analytics')->first()->value == 1)
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-133955404-1"></script>

        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', @php env('TRACKING_ID') @endphp);
        </script>
    @endif

  <script>
    function preventBack() {
        window.history.forward();
    }

    setTimeout("preventBack()", 0);
    window.onunload = function() {
        null
    };
</script>
</head>
<body>

    @foreach (session('flash_notification', collect())->toArray() as $message)
        <script type="text/javascript">
            $(document).on('nifty.ready', function() {
                showAlert('{{ $message['level'] }}', '{{ $message['message'] }}');
            });
        </script>
    @endforeach


    <div id="container" class="effect aside-float aside-bright mainnav-lg">

        @include('inc.admin_nav')

        <div class="boxed">

            <!--CONTENT CONTAINER-->
            <!--===================================================-->
            <div id="content-container">
                <div id="page-content">

                    <div class="row">
   
    </div>

    <br>

    <br><br>

@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css"> --}}

<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <div class="panel-heading bord-btm clearfix pad-all h-100">

          

            <div class="pull-right clearfix">
                <!-- <a href="{{ route('peer_partner.export') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('Export')}}</a> -->
                   <a href="#" class="btn btn-primary"> {{ auth()->user()->name}} ({{auth()->user()->user_type}})
</a>
            </div>
        </div>
        <h3 class="panel-title pull-left pad-no">{{translate('Orders Filter by')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_orders" action="" method="GET">
                
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="delivery_status" id="delivery_status" onchange="sort_orders()">
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
                        <select class="form-control demo-select2" name="pay_type" id="pay_type" onchange="sort_orders()">
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
                        <select class="form-control demo-select2" name="payment_type" id="payment_type" onchange="sort_orders()">
                            <option value="">{{translate('Payment Status')}}</option>
                            <option value="paid"  @isset($payment_status) @if($payment_status == 'paid') selected @endif @endisset>{{translate('Paid')}}</option>
                            <option value="unpaid"  @isset($payment_status) @if($payment_status == 'unpaid') selected @endif @endisset>{{translate('Un-Paid')}}</option>
                        </select>
                    </div>
                </div>

                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 150px;">
                        <select class="form-control demo-select2" name="edit_view" id="edit_view" onchange="sort_orders()">
                            <option value="">{{translate('Edited/Viewed')}}</option>
                            <option value="viewed"  @isset($edit_view) @if($edit_view == 'viewed') selected @endif @endisset>{{translate('Viewed')}}</option>
                            <option value="edited"  @isset($edit_view) @if($edit_view == 'edited') selected @endif @endisset>{{translate('Edited')}}</option>
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


                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 250px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code or name & hit Enter') }}">
                    </div>
                </div>
                
            </form>

            <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 250px;">
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                        Order Report
                        </button>
                    </div>
            </div>
        </div>


    </div>
    <div class="row" style="margin-top: 10px;">
        <!-- <a href="{{ route('orders.index.adminproduct') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('All products List')}}</a>
        <a href="{{ route('orders.index.exportallproduct') }}" class="btn btn-rounded btn-info pull-right" id="order_export_all" style="float:right;margin-right: 30px;">{{translate('Export All')}}</a>  -->

        <!-- <form method="GET" action="{{route('orders.index.exportproductwise')}}" name="order_export" >
             <input type="hidden" name="date_from_export" id="date_from_export_pro">
            <input type="hidden"  name="date_to_export" id="date_to_export_pro">

            <input type="hidden" name="sorting_hub_id" id="sorting_hub_id" value="<?php echo Auth()->user()->id;?>">
            <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">
            <input type="hidden" name="payStatus" id="payStatus" value="<?php if(isset($pay_type)){ echo $pay_type;}?>">
            <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
            <input type="submit" name="submit" id="submit" value="Productwise Export" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
        </form> -->

        <!-- <form method="GET" action="{{route('orders.index.exportproduct')}}" name="order_export" >
             <input type="hidden" name="date_from_export" id="date_from_export">
            <input type="hidden"  name="date_to_export" id="date_to_export"> -->

            <!-- <input type="text" name="date_from_export"  value="<?php //if(isset($_GET['dateRangeStart'])){echo $_GET['dateRangeStart'];}?>">
            <input type="text" name="date_to_export"  value="<?php //if(isset($_GET['dateRangeEnd'])){echo $_GET['dateRangeEnd'];}?>"> -->
<!-- 
            <input type="hidden" name="sorting_hub_id" id="sorting_hub_id" value="<?php echo Auth()->user()->id;?>">
            <input type="hidden" name="deliveryStatus" id="deliveryStatus" value="<?php if(isset($delivery_status)){ echo $delivery_status;}?>">
            <input type="hidden" name="payStatus" id="payStatus" value="<?php if(isset($pay_type)){ echo $pay_type;}?>">
            <input type="hidden" name="paymentStatus" id="paymentStatus" value="<?php if(isset($payment_status)){ echo $payment_status;}?>">
            <input type="submit" name="submit" id="submit" value="Export With Date" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
        </form> -->
        @php 
          $startDate = date('d-m-Y');
          $endDate = date('d-m-Y');
          if(isset($_GET['dateRangeStart'])){
              $startDate = $_GET['dateRangeStart'];
            }

            if(isset($_GET['dateRangeEnd'])){
                $endDate = $_GET['dateRangeEnd'];
            }
        @endphp
        
        <a style="float:right;margin-right: 30px;" href="{{route('dwp.export')}}?date_from_export={{ $startDate }}&&date_to_export={{ $endDate }}&&sorting_hub_id=<?php echo Auth()->user()->id;?>&&delivery_status=<?php if(isset($delivery_status)){ echo $delivery_status;}?>&&pay_status=<?php if(isset($pay_type)){ echo $pay_type;}?>&&payment_mode=<?php if(isset($payment_status)){ echo $payment_status;}?>" class="btn btn-info btn-sm pull-right">Export Product wise</a>
        <a style="float:right;margin-right: 30px;" href="{{route('dw.export')}}?date_from_export={{ $startDate }}&&date_to_export={{ $endDate }}&&sorting_hub_id=<?php echo Auth()->user()->id;?>&&delivery_status=<?php if(isset($delivery_status)){ echo $delivery_status;}?>&&pay_status=<?php if(isset($pay_type)){ echo $pay_type;}?>&&payment_mode=<?php if(isset($payment_status)){ echo $payment_status;}?>" class="btn btn-info btn-sm pull-right">Export with date</a>
        <a style="float:right;margin-right: 30px;" href="{{route('dw.export_assign_orders')}}?date_from_export={{ $startDate }}&&date_to_export={{ $endDate }}&&sorting_hub_id=<?php echo Auth()->user()->id;?>&&delivery_status=<?php if(isset($delivery_status)){ echo $delivery_status;}?>&&pay_status=<?php if(isset($pay_type)){ echo $pay_type;}?>&&payment_mode=<?php if(isset($payment_status)){ echo $payment_status;}?>" class="btn btn-info btn-sm pull-right">Export Assined Orders</a>
       
        
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
                    <th>{{translate('Sorting HUB')}}</th>
                    <th>{{translate('Amount')}}</th>
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
                @foreach($orders as $key => $order)
                @php 
                 $shipping_address = json_decode($order->shipping_address);
                 $customer_name = $shipping_address->name;
                 $phone = $shipping_address->phone;
                 $address = $shipping_address->city;
                 $pincode = $order->shipping_pin_code;
                 $sorting_hub = \App\User::find($order->sorting_hub_id);
                @endphp
                    <tr>

                    <td>{{ $key + 1}}</td>
                    <td>
                        @if($order->viewed == 1 && $order->edited == 1)
                            <span style = "color:red">&#11044;</span>
                        @elseif($order->viewed == 1 && $order->edited == 0)
                            <span style = "color:green">&#11044;</span>    
                        @endif 
                        {{ $order->code }} @if($order->viewed == 0) <span class="pull-right badge badge-info">{{ translate('New') }}</span> @endif
                        <br>{{date("jS F, Y H:i:s A", $order->date)}}
                    </td>
                    <td> {{ $order->orderDetails->sum('quantity') }}</td>
                    <td>{{ $customer_name }}</br />{{ $phone }} <br /> {{ $order->referal_code }}</td>
                    <td> {{@$shipping_address->address}}
                            </td>
                    <td>{{ $pincode }}</td> 
                    <td>@if(!is_null($sorting_hub)) {{ $sorting_hub->name}} @endif</td>     
                    <td>{{ single_price($order->grand_total+$order->wallet_amount) }}</td>
                    <td>{{ $order->order_status }}</td>
                    <td>{{ $order->payment_type }}</td>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                    <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{ route('callceter.show', encrypt($order->id)) }}">{{translate('View')}}</a></li>
                                        <li><a href="{{ route('seller.invoice.download', $order->id) }}" target="_blank">{{translate('Full Invoice Print')}}</a></li>
                
                                        @if(@Auth::user()->email =="mkumar122043@gmail.com")
                                        <li><a onclick="confirm_modal('{{route('orders.destroy', $order->id)}}');">{{translate('Delete')}}</a></li>
                                        @endif
                                        </ul>
                                </div>
                            </td>
                    
                                                      
                    </tr>                
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <div class="pull-right">
                {{ $orders->appends(request()->input())->links() }}
            </div>
        </div>
        
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Report Download</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-body">
            <a href = "javascript::void(0)" download><i class="fa fa-download" aria-hidden="true"></i></a></br>
            <i class="fa fa-download" aria-hidden="true"></i></br>
            <i class="fa fa-download" aria-hidden="true"></i></br>
            <i class="fa fa-download" aria-hidden="true"></i>
            <form id = "report_download">
                <p id= "msg_report" style = "color:red"></p>
                <label for="birthday">Start Date:</label>
                <input type="date"  name="start_date"></br></br>
                <label for="birthday">End Date:</label>
                <input type="date"  name="end_date"></br> 
            </form>
        </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="" id = "report_down_btn_loader" style = "display:none"><img src="{{asset('public/img/Pinwheel.gif')}}" width="109px" height="50px"></br>Please wait...</button>
        <button type="button" class="btn btn-primary" id = "report_down_btn">Generate Report</button>
      </div>
    </div>
  </div>
</div>



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
                "pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                dom: 'lBfrtip',
                buttons: [
                    // 'excelHtml5', 'csvHtml5'
                ]
            } );

            $('#report_down_btn').on('click',function(){
                
                var start_date = "";
                var end_date = "";
                var formData = $('#report_download').serializeArray();
                formData.map(function(v){
                    if(v.name =='start_date'){
                        start_date = v.value;
                    }else{
                        end_date = v.value;
                    }
                });

                if(start_date == ""){
                    $('#msg_report').text('Please select start date');
                    return false;
                }else{
                    $('#msg_report').text('');
                }

                if(end_date == ""){
                    $('#msg_report').text('Please select end date');
                    return false;
                }else{
                    $('#msg_report').text('');
                }

                $('#report_down_btn').css('display','none');
                $('#report_down_btn_loader').css('display','');

                $.post('{{ route('report.generate') }}',{_token:'{{ @csrf_token() }}',start_date:start_date,end_date:end_date},function(data){


                });

            });

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
    
    $('#date_from_export_pro').val(lstart);
    $('#date_to_export_pro').val(lend);

}



$('#reportrange').daterangepicker({

    startDate: start,

    endDate: end,

    maxDate: end,
    dateLimit: { days: 6 },

    ranges: {

       'Today': [moment(), moment()],

       'Last 2 Days': [moment().subtract(2, 'days'), moment().subtract(2, 'days')],

       'Last 7 Days': [moment().subtract(6, 'days'), moment()],

    //    'Last 30 Days': [moment().subtract(29, 'days'), moment()],

    //    'Last 90 Days': [moment().subtract(89, 'days'), moment()],

    //    'This Month': [moment().startOf('month'), moment().endOf('month')],

       //'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]

    }

}, cb);

$('#reportrange').on('apply.daterangepicker', (e, picker) => {

    $('#sort_orders').submit();

});

cb(start, end);

});



    </script>
        @include('inc.admin_footer')

        @include('partials.modal')

        @yield('modal')

    </div>

    @yield('script')

</body>
</html>
