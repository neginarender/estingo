@extends('layouts.app')

@section('content')
<div class="row">

    <div class="col-sm-12">
        <a href="{{ route('DOFO.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add DOFO')}}</a>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <h1 class="panel-title"><strong>{{translate('Upload DOFO List')}}</strong></h1>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" action="{{ route('DOFO.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" class="form-control" name="bulk_file" required>
                </div>
                <div class="form-group">
                    <div class="col-lg-12">
                        <button class="btn btn-primary" type="submit">{{translate('Upload CSV')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <form class=""  action="" method="GET">
    <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type Email and Hit Enter') }}">
                    </div>
    </div>
                
    </form>
</div>

<br>

<!-- Basic Data Tables -->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">{{translate('DOFO')}}</h3>
    </div>
    <div class="panel-body">
    <table class="table table-striped"  cellspacing="0" width="100%">
        {{-- <table class="table table-striped table-bordered demo-dt-basic" cellspacing="0" width="100%"> --}}
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th>{{translate('Address')}}</th>
                    <th>{{translate('PIN Code')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Created Date')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dofo as $key => $value)
                    @if($value != null)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$value->name}}</td>
                            <td>{{$value->email}}</td>
                            <td>{{$value->phone}}</td>
                            <td>{{$value->address}}</td>
                            <td>{{$value->pincode}}</td>
                            <td><label class="switch">
                                <input onchange="update_status(this)" value="{{ $value->id }}" type="checkbox" <?php if($value->status == 1) echo "checked";?> >
                                <span class="slider round"></span></label></td>
                            <td>{{$value->created_at}}</td>
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a onclick="confirm_modal('{{route('DOFO.destroy', $value->id)}}');">{{translate('Delete')}}</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <div class="pull-right">
                {{ $dofo->appends(request()->input())->links() }}
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">

        $(document).ready(function(){
            //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
        });

        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('DOFO.status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Status has been updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
        }

    </script>

@endsection
