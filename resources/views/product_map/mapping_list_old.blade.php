@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<div class="row">
    <div class="col-lg-12 pull-right">
        <a href="{{ route('product-mapping.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Create Product Mapping')}}</a>
    </div>
</div>
<br>

<div class="panel">
    <!--Panel heading-->
    <!-- <div class="panel-heading bord-btm clearfix pad-all h-100">
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
    </div> -->


    <div class="panel-body">
         <table class="table table-striped" id="example" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th width="20%">{{translate('Name')}}</th>
                    <th>{{translate('Distributor')}}</th>
                    <th>{{translate('Added date')}}</th>
                    <th>{{translate('Published')}}</th>
                    <th>{{translate('Mapped status')}}</th>
                    <th>{{translate('Stock')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($mapped_product as $key => $mapProduct)
                    <tr>
                        <td>{{ ($key+1) + ($mapped_product->currentPage() - 1)*$mapped_product->perPage() }}</td>
                        <td>

                            <a href="{{ route('product', @$mapProduct->product->slug) }}" target="_blank" class="media-block">
                                <div class="media-left">
                                    <img loading="lazy"  class="img-md" src="{{ my_asset($mapProduct->product->thumbnail_img)}}" alt="Image" style="width: 50px !important;">
                                </div>
                                <div class="media-body">{{ __($mapProduct->product->name) }}</div>
                            </a>
                        </td>
                        <td>{{ @$mapProduct->distributor->name }}</td>
                        <td>
                            {{ date('d M Y', strtotime($mapProduct->created_at)) }}
                        </td>
                        <td><?php $status_product = \App\Product::where('id', $mapProduct->product_id)->pluck('published')->first();
                            if($status_product=='1'){
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
                        <td>
                        <input type="text" name="product_stock" class = "product_stock" id = {{$mapProduct->id}} value = {{ $mapProduct->qty }} onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" maxlength="4" size="4"/> 
                        </td>
                        <td>
                            <div class="btn-group dropdown">
                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                    {{translate('Actions')}} <i class="dropdown-caret"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                      <li>
                                        <a onclick="confirm_modal('{{route('mapped.product.trash', $mapProduct->id)}}');">{{translate('Delete')}}</a>
                                      </li>
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


@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5', 'csvHtml5'
                ]
            } );
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
        


         $(document).ready(function(){
            $(".product_stock").focusout(function(e){
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
        });



    </script>
@endsection
