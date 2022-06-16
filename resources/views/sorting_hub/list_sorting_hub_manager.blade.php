@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <a href="{{ route('sortingmanager.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Sorting Manager')}}</a>
        </div>
    </div>
    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Sorting Manager List')}}</h3>
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
                    <th>{{translate('Action')}}</th>
                </tr>
                </thead>
                <tbody>
                
                @foreach($sorting_hub_manager as $key => $sortingmanager)
                
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{ucfirst($sortingmanager->user['name'])}}</td>
                        <td>{{$sortingmanager->user['email']}}</td>
                        <td>{{$sortingmanager->cluster_hub['name']}}</td>
                        <td>{{$sortingmanager->sorting_hub['name']}}</td>
                        <td>{{$sortingmanager->phone}}</td>
                        {{-- <td> --}}
                        <?php 
                        //$area_name = \App\Area::where('area_id',$delivery->area_id)->first(['area_name','pincode']);
                        //echo $area_name['area_name'].'|'.$area_name['pincode'];
                                ?>
                        {{-- </td> --}}
                        <td>
                        <div class="btn-group dropdown">
                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                {{translate('Actions')}} <i class="dropdown-caret"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">       
                            <li><a href="{{route('sortingmanager.login', encrypt($sortingmanager->id))}}">{{translate('Log in as this Sorting Manager')}}</a></li>                                   
                                <li><a href="{{route('sortingmanager.edit', $sortingmanager->id)}}">{{translate('Edit')}}</a></li>
                                <li><a onclick="confirm_modal('{{route('sortingmanager.destroy', $sortingmanager->id)}}');">{{translate('Delete')}}</a></li>
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

@endsection
