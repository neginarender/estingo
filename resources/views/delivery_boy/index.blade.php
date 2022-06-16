@extends('layouts.app')

@section('content')
@if(Auth::user()->staff->role->name != "Sorting Hub Manager")
    <div class="row">
        <div class="col-sm-12">
            <a href="{{ route('delivery_boy.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Delivery Boy')}}</a>
        </div>
    </div>
    @endif
    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Delivery Boy List')}}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
               
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('Cluster hub')}}</th>
                    <th>{{translate('Sorting hub')}}</th>
                    <th>{{translate('Phone')}}</th>
                    {{-- <th>{{translate('Area')}}</th> --}}
                    @if(Auth::user()->staff->role->name != "Sorting Hub Manager")
                    <th>{{translate('Action')}}</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                
                @foreach($deliveryboy as $key => $delivery)
                
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{ucfirst($delivery->user['name'])}}</td>
                        <td>{{$delivery->user['email']}}</td>
                        <td>{{$delivery->cluster_hub['name']}}</td>
                        <td>{{$delivery->sorting_hub['name']}}</td>
                        <td>{{$delivery->phone}}</td>
                        {{-- <td> --}}
                        <?php 
                        //$area_name = \App\Area::where('area_id',$delivery->area_id)->first(['area_name','pincode']);
                        //echo $area_name['area_name'].'|'.$area_name['pincode'];
                                ?>
                        {{-- </td> --}}
                        @if(Auth::user()->staff->role->name != "Sorting Hub Manager")
                        <td>
                        <div class="btn-group dropdown">
                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                {{translate('Actions')}} <i class="dropdown-caret"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">       
                            <li><a href="{{route('deliveryboy.login', encrypt($delivery->id))}}">{{translate('Log in as this Delivery Boy')}}</a></li>                                   
                                <li><a href="{{route('delivery_boy.edit', $delivery->id)}}">{{translate('Edit')}}</a></li>
                                <li><a onclick="confirm_modal('{{route('deliveryboy.destroy', $delivery->id)}}');">{{translate('Delete')}}</a></li>
                            </ul>
                        </div>
                    </td>
                    @endif
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

@endsection
