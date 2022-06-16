@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
    <div class="pad-all">
        <div class="row">
            <div class="col-md-12">
            <?php 
                $six_months_back = date("F 1, Y", strtotime("-5 months"));
                $newdate = date('Y-m-d', strtotime($six_months_back));
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
        <form class="" action="{{ route('no_of_order.index') }}" method="GET">

            <div class="box-inline pad-rgt pull-left" style="margin-left: 12px">
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

            <div class="box-inline mar-btm pad-rgt" style="float:left;">
                 {{ translate('Sort by Zone') }}: 
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="zone" onchange="get_cities_by_zone(this.value)" required >
                        <option value=" ">{{'Select Zone'}}</option>
                        <?php $zone = \App\Area::where('status',1)->whereNotNull('zone')->distinct('zone')->groupBy('zone')->get();

                            foreach($zone as $value){?>
                                <option value="<?php echo $value->zone;?>"><?php echo $value->zone;?></option>
                            <?php } ?>
                     </select>
                 </div>
            </div>
            <div class="box-inline pad-rgt pull-left">
                <div class="select" style="min-width: 150px;">
                    <select class="form-control demo-select2 district" name="district" id="district">
                        <!-- <option value="">Select District</option> -->
                    </select>
                </div>
            </div>
            <div class="box-inline pad-rgt pull-left">
                <div class="select" style="min-width: 150px;">
                    <select class="form-control demo-select2 pincode" name="pincode" id="pincode">
                        <!-- <option value="">Select District</option> -->
                    </select>
                </div>
            </div>
            <button class="btn btn-success" type="submit">{{ translate('Filter') }}</button>
            <button class="btn btn-default" type="button"><a href="{{ route('no_of_order.index') }}">{{ translate('Reset') }}</a></button>
        </form> 
    </div>
    </div>
    </div>


    <div class="col-md-12">
        <div class="panel">
            <!--Panel heading-->
            <div class="panel-heading">
                <h3 class="panel-title">{{ translate('Numbers of Orders Report') }}</h3>
            </div>
            @php
            
                $start = new DateTime($start_date);
                $end = new DateTime($end_date);
                //echo $nn = date('F Y', strtotime($d));
                
                //$start    = new DateTime('2022-01-01');
                $start->modify('first day of this month');
                //$end      = new DateTime('2022-03-04');
                $end->modify('first day of next month');
                $interval = DateInterval::createFromDateString('1 month');
                $period   = new DatePeriod($start, $interval, $end);

                //foreach ($period as $dt) {
                     //$dt->format("F Y") . "<br>\n";
                      //echo $dt->format("F Y") . "<br>\n";
               // }
               //dd($orderCount_app);
            @endphp
            <!--Panel body-->
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="example" cellspacing="0" width="100%">
                        <thead>
                            <th></th>
                            
                            <?php
                                foreach ($period as $dt) { ?>
                                  <th><?php echo $dt->format("F Y");?></th>
                            <?php } ?>
                        </thead>
                        <tbody>
                            <tr>
                                <th>From App</th>
                                <?php
                                foreach($orderCount_app as $value){?>
                                    <td>{{$value}}</td>
                                <?php
                                }
                                ?>
                            </tr>
                            <tr>
                                <th>From Website</th>
                                <?php
                                foreach($orderCount_web as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>





                            <tr>
                                <th>Total number of signed up users</th>
                                <?php
                                foreach($total_user as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <tr>
                                <th>Number of transacting users</th>
                                <?php
                                foreach($transacting_user as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <tr>
                                <th>Transacting users - %</th>
                                <?php
                                foreach($transacting_user_percent as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <tr>
                                <th>% of first time users in current month</th>
                                <?php
                                foreach($first_user_percent as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <tr>
                                <th>Number of Peers</th>
                                <?php
                                foreach($num_of_peer as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <tr>
                                <th>Average order value</th>
                                <?php
                                foreach($average_order_val as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <tr>
                                <th>Average order value per peer</th>
                                <?php
                                foreach($average_order_peerwise as $value){?>
                                    <td>{{$value}}</td>
                                <?php 
                                    }
                                ?>
                            </tr>
                            <!-- <tr>
                                <th>From Call Center</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr> -->
                        </tbody>
                    </table>
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
 <script type="text/javascript">
        $(document).ready(function() {
            get_cities_by_zone("{{ $zone }}");
            $('#example').DataTable( {
                 "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 "scrollX":true,
                 // "paging": false,
                "pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                dom: 'lBfrtip',
                buttons: [
                    'excelHtml5', 'csvHtml5'
                ]
            } );

            $('#district').change(function(){
                var city = $('#district').val();
                $.post("{{ route('get_pincode_by_city') }}",{_token:"{{ csrf_token() }}",district:city},function(data){
                        var pincode = $("#pincode");
                        pincode.empty();
                        pincode.append("<option value=''>Select Pincode</option>");
                        $.map(data,function(item){
                            console.log();
                            var selected = "";
                            // if(district_id == item.id){
                            //     selected = "selected";
                            // }
                            pincode.append("<option value="+item.pincode+" "+selected+">"+item.pincode+"</option>");
                        });
                    $('.demo-select2').select2();
                });
            });
            
});

        function get_cities_by_zone(zone){
    $.post("{{ route('get_cities_by_zone') }}",{_token:"{{ csrf_token() }}",zone:zone},function(data){
            var district = $("#district");
            district.empty();
            district.append("<option value=''>Select District</option>");
            $.map(data,function(item){
                console.log();
                var selected = "";
                // if(district_id == item.id){
                //     selected = "selected";
                // }
                district.append("<option value="+item.id+" "+selected+">"+item.name+"</option>");
            });
            $('.demo-select2').select2();
});
}
    </script>
@endsection
