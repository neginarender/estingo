@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
    <div class="pad-all text-center">

    </div>


    <div class="col-md-12">
        <div class="panel">
            <!--Panel heading-->
            <div class="panel-heading">
                <h3 class="panel-title">{{ translate('Returns/Cancellations Report') }}</h3>
            </div>

            <!--Panel body-->
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="example" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ translate('Order ID') }}</th>
                                <th>{{ translate('Order Code') }}</th>
                                <th>{{ translate('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    <td>{{ __($order->id) }}</td>
                                    <td>{{ ($order->code) }}</td>
                                    <td>{{ $order->order_status }}</td>
                                </tr>
                            @endforeach
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
        } );

    </script>
@endsection