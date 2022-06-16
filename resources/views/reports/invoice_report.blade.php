@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<div class="row">
    <div class="col-md-10">
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
     
        <form class="" action="{{ route('invoice_data.index') }}" method="GET">
            
            <div class="box-inline pad-rgt pull-left" style="margin-left: 12px">
                <label for="users-list-role">Start Date</label>
                    <div class="" style="">
                            <input type="date" class="datepicker form-control" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>">
                    </div>
              </div>

              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Date</label>
                    <div class="" style="">
                            <input type="date" class="datepicker form-control" id="end_date" name="end_date" autocomplete="off" value="<?php echo $end_date; ?>">
                    </div>
              </div>

			<div class="box-inline mar-btm pad-rgt">
            <label for="users-list-role">{{ translate('Sorting Hub') }}</label>
                 <div class="">
                     <!-- <select id="demo-ease" class="demo-select2" name="sorting_id" required>

                           @foreach (\App\ShortingHub::where('status', 1)->get() as $keyn => $sh)
                            @php 
                                $names = \App\User::where('id', $sh->user_id)->select('name')->first();
                            @endphp    
                             <option value="{{ $sh->user_id }}" <?php if(isset($sorting_hub_id)){if($sh->user_id == $sorting_hub_id){ echo "selected";}} ?>>{{$names->name}}</option>
                         @endforeach
                     </select> -->
                    
                     @if(Auth::user()->user_type!="admin")
                    <input type="hidden" name="sorting_id" value="{{ Auth::user()->id }}" />
                    @endif
                    @php 
                                        $sortingHubs = \App\ShortingHub::where('status', 1);
                                        if(Auth::user()->user_type!="admin"){
                                            $sortingHubs->where('user_id',$sorting_hub_id);
                                        }
                                        $sortingHubs = $sortingHubs->get();
                                       
                                    @endphp
                                  
                                <select class="form-control" name="sorting_id" @if(Auth::user()->user_type!="admin") disabled @endif>
                                    <option value="">Select</option>
                                   
                                    @foreach($sortingHubs as $key => $sorthub)
                                    <option value="{{ $sorthub->user_id }}" @if($sorthub->user_id==$sorting_hub_id) selected @endif>{{ $sorthub->user->name }}</option>
                                    @endforeach
                                </select>     
                 </div>
            </div>
           
            <button class="btn btn-default btn-success" type="submit">{{ translate('Filter') }}</button>
        </form>
    </div>

        </div>
    <div class="row">
             
    </div>
<div>&nbsp;</div>
    <div class="col-md-12">
        <div class="panel">
            <!--Panel heading-->
            <div class="panel-heading">
                <h3 class="panel-title">{{ translate('Invoice Data') }}</h3>

            </div>



            <!--Panel body-->
            <div class="panel-body">
                <div class="table-responsive">
                    <!-- <table class="table table-striped mar-no demo-dt-basic"> -->
                         <table class="table table-striped" id="examples" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ translate('Sno.') }}</th>
								<th>{{ translate('Start Date') }}</th>
								<th>{{ translate('End Date') }}</th>
                                <th>{{ translate('Invoice') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @if(count($orders))
                            <tr>
                                    <td>1</td>
									<td>{{ (date('d-m-Y',strtotime($start_date))) }}</td>
									<td>{{ (date('d-m-Y',strtotime($end_date))) }}</td>
                                    <td> 
                                        <form class="form-horizontal" action="{{ route('all_invoice.download') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                                            <input type="hidden" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                                            <input type="hidden" name="sorting_id" id="sorting_id" value="<?php if(isset($sorting_hub_id)){ echo $sorting_hub_id;}?>">
                                            <input type="submit" name="submit" id="submit" value="Full Invoice Print" class="btn btn-info">
                                        </form>    
                                    </td>
                                        
                                    
                                </tr>
                            @else
                            <tr>
                                <td colspan="4"><p style="text-align:center;">No Invoice Found</p></td>
                            </tr>   
                            @endif
                               
                        
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
            $('#example').DataTable( {
            //     "bPaginate": false,
            // "bLengthChange": false,
            // "bFilter": true,
            // "bInfo": false,
            // "bAutoWidth": false,
                 // "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 // "scrollX":true,
                 // "paging": false,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                // dom: 'lBfrtip',
                // buttons: [
                //     'excelHtml5', 'csvHtml5'
                // ]
            } );
        } );

    </script>
@endsection