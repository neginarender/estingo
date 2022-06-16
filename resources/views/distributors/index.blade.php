@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
             @if(Auth::user()->staff!='')
                @if(Auth::user()->staff->role->name == "Sorting Hub")
            <a href="{{ route('distributor.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Distributor')}}</a>
                @endif
            @endif
        </div>
    </div>
    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Distributors List')}}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Cluster hub')}}</th>
                    <th>{{translate('Sorting hub')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th>{{translate('Address')}}</th>
                    <th>{{translate('Pincode')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Action')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($distributors as $key => $distributor)
                 <?php $all_id = $distributor->id.'_'.$distributor->sorting_hub->id; ?>
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{$distributor->name}}</td>
                        <td>{{@$distributor->cluster_hub->name}}</td>
                        <td>{{@$distributor->sorting_hub->name}}</td>
                        <td>{{$distributor->phone}}</td>
                        <td> {{ \Illuminate\Support\Str::limit($distributor->address, 30, $end='...') }}</td>
                        <td>
                        @foreach(json_decode($distributor->pincode) as $v=>$k)
                        {{$k}},
                        @endforeach
                        </td>
                        <td><label class="switch">
                                <input onchange="change_status(this)" value="{{ $distributor->id }}" type="checkbox" <?php if($distributor->status == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                        </td>
                        <td>
                        <div class="btn-group dropdown">
                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                {{translate('Actions')}} <i class="dropdown-caret"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right"> 
                                <li><a href="{{ route('distributororders.show', encrypt($all_id)) }}">{{translate('View Orders')}}</a></li>
                                 @if(Auth::user()->staff!='')
                                    @if(Auth::user()->staff->role->name == "Sorting Hub")              
                                <li><a href="{{route('distributor.edit', $distributor->id)}}">{{translate('Edit')}}</a></li>
                                    @endif
                                @endif
                                <!-- <li><a onclick="confirm_modal('{{route('distributor.destroy', $distributor->id)}}');">{{translate('Delete')}}</a></li> -->
                            </ul>
                        </div>
                    </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="clearfix">
                <div class="pull-right">
                    
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-content">

            </div>
        </div>
    </div>

    <div class="modal fade" id="profile_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-content">

            </div>
        </div>
    </div>

    <script type="text/javascript">

 function change_status(el){
    if(el.checked){
        var status = 1;
    }
    else{
        var status = 0;
    }

    $.post('{{ route('distributor.status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
        if(data == 1){
            showAlert('success', 'Distributor status has been changed successfully.');
        }
        else{
            showAlert('danger', 'Something went wrong');
        }
    });
   
}

</script>

@endsection
