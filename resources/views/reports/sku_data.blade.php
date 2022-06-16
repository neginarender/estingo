@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
    <div class="row">
        <div class="col-md-10">
        <form class="" action="{{ route('sku_data.index') }}" method="GET">
			<div class="box-inline mar-btm pad-rgt">
                 {{ translate('Sort by Sorting Hub') }}: 
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="sorting_id" required>
                         @foreach (\App\ShortingHub::where('status', 1)->get() as $keyn => $sh)
                            @php 
                                $names = \App\User::where('id', $sh->user_id)->select('name')->first();
                            @endphp    
                             <option value="{{ $sh->user_id }}" <?php if(isset($sorting_hub_id)){if($sh->user_id == $sorting_hub_id){ echo "selected";}} ?>>{{$names->name}}</option>
                         @endforeach
                     </select>
                 </div>
            </div>
            <!-- <div class="box-inline mar-btm pad-rgt">
                 {{ translate('Sort by Category') }}:
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="category_id" required>
                         @foreach (\App\Category::all() as $key => $category)
                             <option value="{{ $category->id }}" <?php //if($category->id == $categoryid) echo "selected"; ?>>{{ __($category->name) }}</option>
                         @endforeach
                     </select>
                 </div>
            </div> -->
            <button class="btn btn-default" type="submit">{{ translate('Filter') }}</button>
        </form>
    </div>
    <div class="col-md-2">
        <form method="GET" action="{{route('report.sku_data_export')}}" name="sku_data_export" id="sku_data_export">
            <input type="hidden" name="sortinghubid" id="sortinghubid" value="<?php if(isset($sortinghubid)){ echo $sortinghubid;}?>">
            <input type="submit" name="submit" id="submit" value="Export" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
        </form>
    </div>
    </div>
    <div class="row">
             
        </div>

    <div class="col-md-12">
        <div class="panel">
            <!--Panel heading-->
            <div class="panel-heading">
                <h3 class="panel-title">{{ translate('SKU data report') }}</h3>
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
								<th>{{ translate('Listing Date') }}</th>
								<th>{{ translate('Sorting Hub') }}</th>
                                <th>{{ translate('Brand') }}</th>
								<th>{{ translate('Category') }}</th>
								<th>{{ translate('SubCategory') }}</th>
								<th>{{ translate('SubSubCategory') }}</th>
								<th>{{ translate('MRP') }}</th>
								<th>{{ translate('Buying Price') }}</th>
								<th>{{ translate('Selling Price') }}</th>
								<th>{{ translate('Weight') }}</th>
								<th>{{ translate('Weight unit') }}</th>
								<th>{{ translate('HSN Code') }}</th>
								<th>{{ translate('GST%') }}</th>
								<th>{{ translate('Published') }}<br>
                                    {{translate('/Unpublished')}}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $key => $product)
                                <tr>
                                    <td>{{ __($product->name) }}</td>
									<td>{{ ($product->sku) }}</td>
									<td>{{ (date('d-m-Y',strtotime($product->created_at))) }}</td>
									<td>{{ (App\User::where('id',$product->sorting_hub_id)->first()['name']) }}</td>
                                    <td>{{ ($product->brand_name) }}</td>
									<td>{{ ($product->category_name) }}</td>
									<td>{{ ($product->subcategory_name) }}</td>
									<td>{{ ($product->subsubcategory_name) }}</td>
									<td>₹ {{ ($product->stock_price) }}</td>
									<td>
                                        @if($product->purchased_price == 0)
                                        ₹ {{ ($product->purchase_price) }}
                                        @else
                                        ₹ {{ ($product->purchased_price) }}
                                        @endif
                                    </td>
									<td>
									@if($product->selling_price == 0)
									₹ {{ ($product->stock_price) }}
									@else
									₹ {{ ($product->selling_price) }}
									@endif
								</td>
									<td>
									@if (@$product->choice_options != null)
										@foreach (json_decode($product->choice_options) as $key => $choice)
											@foreach ($choice->values as $key => $value)
												{{ $value }}
											@endforeach
											{{'/'}}											
										@endforeach
									@endif</td>
									<td>
									<?php if (@$product->choice_options != null){
										$arr = array();
										foreach (json_decode($product->choice_options) as $key => $choice){
											$name = App\Attribute::where('id',$choice->attribute_id)->first()->name;
											array_push($arr,$name);
										}
										echo implode(' /',$arr);
									}
									?>
									</td>
									<td>{{ ($product->hsn_code) }}</td>
									<td><?php if($product->tax_type == 'percent'){ echo $product->tax.' %';}else{
										echo '₹ '.$product->tax;
									}?></td>
									<td><?php if($product->published == '1'){ echo 'Published';}else{ echo 'Unpublished';}?></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="clearfix">
                        <div class="pull-right">
                            {{ $products->appends(request()->input())->links() }}
                        </div>
                    </div>
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
                "bPaginate": false,
                "bLengthChange": false,
                "bFilter": true,
                "bInfo": false,
                "bAutoWidth": false,
                 //"lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 //"scrollX":true,
                 // "paging": false,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                // dom: 'lBfrtip',
                // buttons: [
                //     'excelHtml5', 'csvHtml5'
                // ]
            } );

        $('#sortinghubid').val($("#demo-ease").val());

        $('#demo-ease').change(function() {
            $('#sortinghubid').val((this.value));
        });

        } );

    </script>
@endsection