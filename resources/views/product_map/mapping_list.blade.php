@extends('layouts.app')

@section('content')

<!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css"> -->
<div class="row">
<div class="col-lg-8 pull-left">
<form action="{{ route('sorting_hub.download_file_product_by_category') }}" method="post">
<input type="hidden" name="_token" value="{{ csrf_token() }}" />
<div class="box-inline pad-rgt pull-left">
<select name="category" class="form-control demo-select2" style="width:200px!important;" required>
         <option value="">Select Category</option>
         @foreach(\App\Category::all() as $key => $category)
			                     <option value="{{$category->id}}">{{$category->name}}</option>
			                 @endforeach
         </select>
</div>
<div class="box-inline pad-rgt pull-left">
         <select name="file" class="form-control" style="width:200px;">
         <option value="excel">Excel</option>
         <option value="pdf">PDF</option>
         </select>
         </div>
         <div class="box-inline pad-rgt pull-left">
         <button type="submit" class="btn btn-success">Download</button>
         </div>
         </div>
    <div class="col-lg-4 pull-right">
        <a href="{{ route('product-mapping.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Create Product Mapping')}}</a>
    </div>
   
</div>
</form>
<br>

<div class="panel">
    <!--Panel heading-->
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{ translate('Mapped Products') }}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_products" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="panel-body">
         @if(!empty($sort_search))
                    <form action="{{ route('sorting_hub.download_file_product_by_sku') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="searchs" id="searchs" value="<?php if(isset($sort_search)){ echo $sort_search;}?>">    
                        <!-- <div class="box-inline pad-rgt pull-left"> -->
                            <button type="submit" class="btn btn-info btn-sm">Export all sku searched products</button>
                        <!-- </div>     -->
                    </form><br>
                
                @endif
                
    <form action="{{ route('mapped_product.delete') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
         <table class="table table-striped" id="example" cellspacing="0" width="100%">
             <!-- <table class="table table-striped res-table mar-no" cellspacing="0" width="100%"> -->
            <thead>
          <tr>
              <td colspan="6">
                  <!-- <button type="button" onclick="DeleteCheck()" class="btn btn-danger btn-sm">Delete</button> -->
                </td> 
                <td colspan="2" style="text-align: right">Showing <?php echo count($count_product); ?> Products</td>
                <td colspan="2" style="text-align: right">Published:  <?php echo count($count_published); ?> Products</td>
                <td colspan="2" style="text-align: right">Unpublished:  <?php echo count($count_unpublished); ?> Products</td>
            </tr>
               
                <tr>
                <th><!--<input type="checkbox" name="allCheck" id="allCheck" /> --></th>
                    <th>#</th>
                    <th width="20%">{{translate('Name')}}</th>
                    <th>{{translate('Quantity')}}</th>
                    <!-- <th>{{translate('Distributor')}}</th> -->
                    <th>{{translate('Purchased Price')}}</th>
                    <!-- Note - Selling Price = MRP -->
                    <th>{{translate('MRP')}}</th>
                    <th>{{translate('Added date')}}</th>
                    <th>{{translate('Max Purchase Limit')}}</th>
                    <th>{{translate('Published')}}</th>                    
                    <th>{{translate('Mapped status')}}</th>
                    <th>{{translate('Top Products')}}</th>
                    <th>{{translate('Recurring status')}}</th>
                    <th>{{translate('Stock')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>

            <tbody>
            
                @foreach($mapped_product as $key => $mapProduct)
                    <tr id="{{ $mapProduct->id }}">
                     <td>
                        <!-- <input type="checkbox" name="check[]" class="check" value="{{ $mapProduct->id }}" /> -->
                    </td>
                        <td>{{ ($key+1)}}</td>
                        <td>
                        @php 
                            $variant = "";
                            $product_name = @$mapProduct->product->name;
                            @endphp
                           
                                <div class="media-left">
                                    <img loading="lazy"  class="img-md" src="{{ my_asset(@$mapProduct->product->thumbnail_img)}}" alt="Image" style="width: 50px !important;">
                                </div>
                                <div class="media-body">{{$product_name}}<br>
                                    <b><?php $product_sku = \App\ProductStock::where('product_id', @$mapProduct->product_id)->pluck('sku')->first();
                                        echo $product_sku;
                                    ?> </b>
                                </div>  

                                
                        </td>
                        <td>
                            
                        @if (@$mapProduct->product->choice_options != null)
                                                        @foreach (json_decode($mapProduct->product->choice_options) as $key => $choice)
                                                            @foreach ($choice->values as $key => $value)
                                                             @php 
                                                             $variant = $value;
                                                             @endphp
                                                                {{ $value }}
                                                            @endforeach   
                                                        @endforeach
                        @endif
                        </td>
                        <!-- <td></td>
                        -->
                        <td>
                        
                        <input type="number" name="purchased_price" class = "purchased_price" purchase-id = {{$mapProduct->id}} value = {{ $mapProduct->purchased_price }} style="width:65px;" maxlength="4" size="4"/>  
                        </td>
                        <td>
                        <input type="number" name="selling_price" class = "selling_price" selling-id = {{$mapProduct->id}} value = {{ $mapProduct->selling_price }} style="width:65px;" maxlength="4" size="4"/> 
                        </td>
                        <td>
                            {{ date('d M Y', strtotime(@$mapProduct->created_at)) }}
                        </td>

                        <td>
                        <input type="text" name="max_purchase_limit" class = "max_purchase_limit" id = "{{$mapProduct->id}}" value = "{{ $mapProduct->max_purchaseprice }}" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" maxlength="4" size="4"/> 
                        </td>

                        <td><?php $status_product = \App\Product::where('id', @$mapProduct->product_id)->pluck('published')->first();
                            if($mapProduct->published=='1'){
                                echo '<span style="color:green">Published</span>';
                            }else{
                                echo '<span style="color:red">Unpublished</span>';
                            }
                            ?>
                            
                        </td>
                        <td><label class="switch">
                                <input onchange="update_published(this)" value="{{ $mapProduct->id }}" type="checkbox" <?php if($mapProduct->published == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                        </td>
                        
                        <td><label class="switch">
                                <input onchange="update_topproductsbyhub(this)" value="{{ $mapProduct->id }}" type="checkbox" <?php if($mapProduct->top_product == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                        </td>
                        <td><label class="switch">
                                <input onchange="update_recurring(this)" value="{{ $mapProduct->id }}" type="checkbox" <?php if($mapProduct->recurring_status == 1) echo "checked";?> >
                                <span class="slider round"></span></label></td>
                        <!-- <td><label class="switch">
                                <input onchange="update_reccurringproductsbyhub(this)" value="{{ $mapProduct->id }}" type="checkbox" <?php if($mapProduct->recurring_status == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                        </td> -->

                        <td>
                        <input type="text" name="product_stock" class = "product_stock" id = {{$mapProduct->id}} value = {{ $mapProduct->qty }} onkeypress='return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))' maxlength="4" size="4"/> 
                        </td>
                        <td>
                            <div class="btn-group dropdown">
                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                    {{translate('Actions')}} <i class="dropdown-caret"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <input type="hidden" id="product_name" value="{{ $product_name }}" />
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#exampleModal" onclick="mapDistributor('{{ $mapProduct->id }}','{{ my_asset(@$mapProduct->product->thumbnail_img) }}','{{ $variant }}')">{{translate('Map Distributors')}}</a>
                                    </li> 
                                    <!-- <li>
                                        <a onclick="confirm_modal('{{route('mapped.product.trash', $mapProduct->id)}}');">{{translate('Delete')}}</a>
                                    </li> -->
                                    <!-- <li>
                                        <a onclick="getMapModal('{{$mapProduct->id}}');" href="javascript:;">{{translate('View')}}</a>
                                    </li> -->
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
       </form>
        <div class="products-pagination bg-white p-3">
                            <nav aria-label="Center aligned pagination">
                                <ul class="pagination justify-content-center">
                                    {{ $mapped_product->links() }}
                                </ul>
                            </nav>
        </div>
        <div class="clearfix">
            <div class="pull-right">
           
              
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profile_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-content">

        </div>
    </div>
</div>

@endsection

@section('modal')
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom: 1px solid #e5e5e5;background: #6d7074;">
        <h5 class="modal-title" id="exampleModalLabel" style="color: #fff;">Map Distributors</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="color:#fff;">X</span>
        </button>
      </div>
      <form action="{{ route('store.mapped-distributors') }}" id="distributor_mapping_form" method="post">
    <div class="modal-body">
       <input type="hidden" name="_token" value="{{ csrf_token() }}" />
       <input type="hidden" name="id" value="" id="mapping_id"/>
       <div id="load_distributors"></div>
       <div class="row" style="display:none;" id="invalid-feedback">
           <div class="col-md-10">
           <span class="alert alert-danger" style="display:block;">Please select distributors</span>
           </div>
       </div>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="button" onclick="submitDistributorMapForm()" class="btn btn-primary">Save changes</button>
      </div>
</form>
    </div>
  </div>
</div>
<style type="text/css">
.tab { font-size: 11px; padding: 5px 7px; color: #fff; }
.success { background: #80ae00; } .pending { background: #b78e41d1; }
</style>
@endsection

@section('script')
<!-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script> -->

    <script type="text/javascript">
        $(document).ready(function() {
            // var table = $('#example').DataTable( {
            //      "sScrollX": "100%",
            //     "sScrollXInner": "110%",
            //     "bScrollCollapse": true,
            //      "paging": false
            // } );
 
            var table = $('#example');
             table.on("focusout",".product_stock",function(e){
                var stock = $(this).val();
                var product_id = $(this).attr('id');
                $.post('{{route('mapping.stock')}}',{_token:'{{csrf_token()}}',id:product_id,stock:stock},function(data){
                    if(data == 1){
                        showAlert('success', 'Product stock has been updated successfully');
                    }
                    else{
                        showAlert('danger', 'Something went wrong');
                    }

                });

            });

            table.on("focusout",".purchased_price",function(e){
                var purchase_price = $(this).val();
                var product_id = $(this).attr('purchase-id');
                $.post('{{route('mapping.purchased_price')}}',{_token:'{{csrf_token()}}',purchase_price:purchase_price,product_id:product_id},function(data){
                    if(data == 1){
                        showAlert('success', 'Purchase price has been updated successfully');
                    }
                    else{
                        showAlert('danger', 'Something went wrong');
                    }

                });

            });

             table.on("focusout",".selling_price",function(e){
                var selling_price = $(this).val();
                var product_id = $(this).attr('selling-id');
                $.post('{{route('mapping.selling_price')}}',{_token:'{{csrf_token()}}',selling_price:selling_price,product_id:product_id},function(data){
                    if(data == 1){
                        showAlert('success', 'Selling price has been updated successfully');
                    }
                    else{
                        showAlert('danger', 'Something went wrong');
                    }

                });

            });

        } );
    </script>
    <script type="text/javascript">

        function getMapModal(id){
        $.post('{{ route('mapped.product.modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#profile_modal #modal-content').html(data);
                $('#profile_modal').modal('show', {backdrop: 'static'});
            });
        }


         function update_published(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }

            $.post('{{ route('mapping.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Published products updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
           
        }

        function update_recurring(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            

            $.post('{{ route('mapping.recurring') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Recurring products updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
           
        }

        function update_topproductsbyhub(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }

            $.post('{{ route('mapping.productsbyhub') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Published products updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
           
        }

        function update_reccurringproductsbyhub(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }

            $.post('{{ route('mapping.recurproductsbyhub') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Recurring status updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
           
        }
        


        $("#allCheck").click(function(){
    $('.check').not(this).prop('checked', this.checked);
});

function DeleteCheck()
{
    var val = [];
    $("input:checkbox[class=check]:checked").each(function(i){
          val[i] = $(this).val();
        });
        var ids = val.join()
        $.ajax({
            url: "{{ route('mapped_product.delete') }}",
            type: "POST",
            data: {check : ids,_token:"{{ csrf_token() }}"},
            success:function(data){
                if(data==1){
                    alert("Record has deleted successfully");
                    // val.each(function(id){
                    //     $("#"+id).remove();
                    // });
                    window.location.reload();
                    
                }
                else{
                    alert('Something went wrong');
                }
            }
        });
}

function mapDistributor(id,image,variant){
$("#mapping_id").val(id);
var name = $("#product_name").val();
$.post("{{ route('map.distributors') }}",{_token:'{{ csrf_token() }}',id:id,image:image,variant:variant,name:name},function(data){
    $("#load_distributors").html(data);
    $(".demo-select2").select2();
});
}

function submitDistributorMapForm(){
    const distributors = $("#distributors").val();
    console.log(distributors.length);
    if(distributors.length>0){
        console.log('here');
        $("#distributor_mapping_form").submit();

    }else{
        $("#invalid-feedback").removeAttr('style');
        $("#invalid-feedback").css('margin-top','30px');
    }
}

$(document).ready(function(){
            $(".max_purchase_limit").focusout(function(e){
                // alert('pp');
                var max_purchase_qty = $(this).val();
                var product_id = $(this).attr('id');
                $.post('{{route('product.min_purchaselimit')}}',{_token:'{{csrf_token()}}',id:product_id,max_purchase_qty:max_purchase_qty},function(data){
                    if(data == 1){
                        showAlert('success', 'Product purchase limit has been set successfully');
                    }
                    else{
                        showAlert('danger', 'Something went wrong');
                    }

                });

            });
        });

    </script>
@endsection
