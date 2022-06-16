@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <a href="{{ route('opening.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Vaccancy')}}</a>
        </div>
    </div>

    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Job vaccancies')}}</h3>
            <div class="pull-right clearfix">
               
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Designation')}}</th>
                    <th>{{translate('Role')}}</th>
                    <th>{{translate('Number of Positions')}}</th>
                    <th>{{translate('Location')}}</th>
                    <th>{{translate('Monthly Take Home Salary')}}</th>
                    <th>{{translate('Education Required')}}</th>
                    <th>{{translate('Experience Required')}}</th>
                    <!-- <th>{{translate('Status')}}</th> -->
                    <th>{{translate('Created on')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($openings))
                    @foreach($openings as $key => $opening)

                        @if($opening != null)
                            <tr>
                                <td>{{ ($key+1) + ($openings->currentPage() - 1)*$openings->perPage() }}</td>
                                <td>{{ ucfirst($opening->designation) }}</td>
                                <td>{{ $opening->role }}</td>
                                <td>{{ $opening->num_position }}</td>
                                <td>@php
                                        $name = array();
                                        $arr = explode(',',$opening->location);
                                            $locations = App\JobLocation::whereIn('id',$arr)->select('city')->get();
                                            foreach($locations as $key=>$value){
                                                array_push($name,$value->city);
                                            }
                                            $city_name = implode(',',$name);

                                        @endphp
                                        {{$city_name}}
                                </td>
                                <!-- <td>{{ $opening->location }}</td> -->
                                <td>{{ $opening->salary }}</td>
                                <td>{{ $opening->education_req }}</td>
                                <td>{{ $opening->experience_req }}</td>
                                <!-- <th>{{$opening->status}}</th> -->
                                <td>{{date('d-m-Y h:i:s' , strtotime($opening->created_at))}}</td>
                            
                                <td>
                                    <div class="btn-group dropdown">
                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                            {{translate('Actions')}} <i class="dropdown-caret"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="{{route('opening.edit', $opening->id)}}">{{translate('Edit')}}</a></li>                                          
                                            <li><a onclick="confirm_modal('{{route('opening.delete', $opening->id)}}');">{{translate('Delete')}}</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach 
                    @endif
                </tbody>
            </table>
            <div class="clearfix">
                <div class="pull-right">
                    {{ $openings->appends(request()->input())->links() }}
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

@section('script')
    <script type="text/javascript">
        function show_seller_payment_modal(id){
            $.post('{{ route('sellers.payment_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#payment_modal #modal-content').html(data);
                $('#payment_modal').modal('show', {backdrop: 'static'});
                $('.demo-select2-placeholder').select2();
            });
        }

        function show_seller_profile(id){
            $.post('{{ route('sellers.profile_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#profile_modal #modal-content').html(data);
                $('#profile_modal').modal('show', {backdrop: 'static'});
            });
        }

        

    </script>
@endsection

@section('modal')
    <div class="modal fade" id="confirm-ban" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">{{translate('Confirmation')}}</h4>
                </div>

                <div class="modal-body">
                    <p>{{translate('Do you really want to ban this seller?')}}</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <a id="confirmation" class="btn btn-danger btn-ok">{{translate('Proceed!')}}</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="confirm-unban" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">{{translate('Confirmation')}}</h4>
                </div>

                <div class="modal-body">
                    <p>{{translate('Do you really want to unban this seller?')}}</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <a id="confirmationunban" class="btn btn-success btn-ok">{{translate('Proceed!')}}</a>
                </div>
            </div>
        </div>
    </div>
@endsection