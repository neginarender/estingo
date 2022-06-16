@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <a href="{{ route('deliveryslot.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add Delivery Slot')}}</a>
        </div>
    </div>
    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Delivery Slot Detail')}}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
               
                <tr>
                    <th>#</th>
                    <th>{{translate('Category Name')}}</th>
                    <th>{{translate('Category Detail')}}</th>
                    @if(auth()->user()->user_type == "admin")
                    <th>{{translate('Shorting Hub Name')}}</th>
                    @endif
                    <th>{{translate('Cut Off Time')}}</th>
                    <th>{{translate('Delivery Time')}}</th>
                    <th>{{translate('Delivery Shift')}}</th>
                    <th>{{translate('Action')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($delievrySlot as $key => $delivery)
                
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{ucfirst($delivery->category_name)}}</td>
                        <td>
                        @php
                        $category_id = explode(',',$delivery->category_id);
                            $catname = array();
                            foreach($category_id as $k=>$v){
                                $category  = \App\Category::where('id',$v)->first('name');
                                $catname[$k] = $category['name'];
                            }
                        @endphp 
                        {{ implode(',',$catname)}}

                        </td>
                        @if(auth()->user()->user_type == "admin")
                        <td>{{$delivery->shortingHub->user->name}}</td>
                        @endif
                        <td>{{$delivery->cut_off}}</td>
                        <td>{{$delivery->delivery_time}}</td>
                        <td>{{$delivery->delivery_shift}}</td>
                        <td>
                        <div class="btn-group dropdown">
                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                {{translate('Actions')}} <i class="dropdown-caret"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">                                   
                                <li><a href="{{route('deliveryslot.edit', encrypt($delivery->id))}}">{{translate('Edit')}}</a></li>
                                <li><a onclick="confirm_modal('{{route('deliveryslot.delete', encrypt($delivery->id))}}');">{{translate('Delete')}}</a></li>
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
