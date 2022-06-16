@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <!-- <a href="{{ route('sellers.create')}}" class="btn btn-info pull-right">{{translate('add_new')}}</a> -->
    </div>
</div>

<br>

<!-- Basic Data Tables -->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('RazorpayX Contacts')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_customers" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type email or name & Enter') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Cust_id')}}</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('active')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $key => $value)
                        <tr>
                            <td>{{($key+1)}}</td>
                            <td>{{$value->id}}</td>
                            <td>{{ucfirst($value->name)}}</td>
                            <td>{{$value->contact}}</td>
                            <td>@if($value->email == null) {{"Not Available"}} @else {{$value->email}} @endif</td>
                            <td>@if($value->active == true) <p style = 'color:green'>{{"Active"}}</p> @else <p style = 'color:red'>{{"Inactive"}}</p> @endif</td>
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{route('razorpayx.addAccount', encrypt($value->id))}}">{{translate('Add Account')}}</a></li>
                                        <li><a href="{{route('razorpayx.transfermoney', encrypt($value->id))}}">{{translate('Transfer Money')}}</a></li>
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

