@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
    <div class="pad-all text-center">
        <form class="" action="{{ route('stock_report.index') }}" method="GET">
            <div class="box-inline mar-btm pad-rgt">
                 {{ translate('Sort by Sorting Hub') }}: 
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="sorting_id" required>
                         @foreach (\App\ShortingHub::where('status', 1)->get() as $keyn => $sh)
                            @php 
                                $names = \App\User::where('id', $sh->user_id)->select('name')->first();
                            @endphp    
                             <option value="{{ $sh->user_id }}" <?php if($sh->user_id == $sortinghub) echo "selected"; ?>>{{$names->name}}</option>
                         }
                         }
                         @endforeach
                     </select>
                 </div>
            </div>

            <div class="box-inline mar-btm pad-rgt">
                 {{ translate('Sort by Category') }}:
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="category_id" required>
                         @foreach (\App\Category::all() as $key => $category)
                             <option value="{{ $category->id }}" <?php if($category->id == $categoryid) echo "selected"; ?>>{{ __($category->name) }}</option>
                         @endforeach
                     </select>
                 </div>
            </div>
            <button class="btn btn-default" type="submit">{{ translate('Filter') }}</button>
        </form>
    </div>


    <div class="col-md-offset-2 col-md-8">
        <div class="panel">
            <!--Panel heading-->
            <div class="panel-heading">
                <h3 class="panel-title">{{ translate('Product wise stock report') }}</h3>
            </div>

            <!--Panel body-->
            <div class="panel-body">
                <div class="table-responsive">
                    <!-- <table class="table table-striped mar-no demo-dt-basic"> -->

                         <table class="table table-striped" id="example" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ translate('Product Name') }}</th>
                                <th>{{ translate('SKU') }}</th>
                                <th>{{ translate('Stock') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $key => $product)
                               
                                <tr>
                                    <td>{{ ($product->name) }}</td>
                                    <td>{{ ($product->sku) }}</td>
                                    <td>{{ ($product->qty) }}</td>
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
