@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">

<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">Option Value for {{ $attribute->name }}</h3>
            <input type="hidden" name="attr_name" id="attr_name" value="{{ $attribute->name }}" />
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ URL::to('admin/attributes/storeoptionval')}}/{{$attribute->id}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="name">{{ translate('Value')}}</label>
                    <div class="col-sm-10">
                        <input type="text" placeholder="Please enter option value" id="name" name="name" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="panel-footer text-right">
                <button class="btn btn-purple" type="submit">{{ translate('Save')}}</button>
            </div>
        </form>
        <!--===================================================-->
        <!--End Horizontal Form-->

        

    </div>
</div>


<div class="panel">
    <div class="row">
    <div class="col-sm-12">
        <div class="panel-heading">
         <h3 class="panel-title">{{ translate('Attributes option values')}}</h3>
        </div> 
    </div>
</div>
    <div class="panel-body">
        <table class="table table-striped table-bordered demo-dt-basic" id="attributes" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Option Values')}}</th>
                    <th width="10%">{{ translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($optionattributes as $key => $attribute)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$attribute->attribute_option_value}}</td>
                        <td>
                            <div class="btn-group dropdown">
                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                    {{ translate('Actions')}} <i class="dropdown-caret"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a href="{{ URL::to('admin/attributes/editoptionattribute')}}/{{$attribute->attribute_option_value_id }}" >{{ translate('Edit')}}</a></li>
                                    <li>
                                        <a style="cursor: pointer;" onclick="confirm_modal('{{ URL::to('admin/attributes/distroyoptionattribute')}}/{{$attribute->attribute_option_value_id }}');">{{ translate('Delete')}}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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
            $('#attributes').DataTable( {
                 "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 "scrollX":true,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                dom: 'lBfrtip',

                buttons: [
                    {
                extend: 'excelHtml5',
                title:'attributes_'+$("#attr_name").val(),
                exportOptions: {
                    columns: [0,1]
                }
            },
             {
                extend: 'csvHtml5',
                title:'attributes_'+$("#attr_name").val(),
                exportOptions: {
                    columns: [0,1]
                }
            }
                ],
                bDestroy: true,
            } );
        } );
    </script>
@endsection
