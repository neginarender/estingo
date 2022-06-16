@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <a href="{{ route('clusterhub.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Cluster')}}</a>
        </div>
    </div>

    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Cluster Hubs')}}</h3>
            <div class="pull-right clearfix">
               
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Cluster Name')}}</th>
                    <th>{{translate('User id')}}</th>
                    {{-- <th>{{translate('Region')}}</th> --}}
                    <th>{{translate('State')}}</th>
                    <th>{{translate('City')}}</th>
                    <!-- <th>{{translate('Status')}}</th> -->
                    <th>{{translate('Created on')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($clusters))
                    @foreach($clusters as $key => $cluster)
                    @php 
                        $city_name = \App\City::whereIn('id', json_decode($cluster->cities))->pluck('name')->toArray();
                        $state_name = \App\State::whereIn('id', json_decode($cluster->state_id))->pluck('name')->toArray();
                      @endphp
                        @if($cluster->user != null)
                            <tr>
                                <td>{{ ($key+1) + ($clusters->currentPage() - 1)*$clusters->perPage() }}</td>
                                <td>{{ ucfirst($cluster->user->name) }}</td>
                                <td>{{ $cluster->user->email }}</td>
                                {{-- <td>{{ $cluster->region->name }}</td> --}}
                                <td>
                                     @php 
                                     
                                    foreach($state_name as $key=> $val){
                                        echo $val.', ';
                                    }
                                    @endphp
                                </td>
                                <td>
                                    @php 
                                    foreach($city_name as $key=> $val){
                                        echo $val.', ';
                                    }
                                    @endphp
                                </td>
                               <!--  <td>
                                    <label class="switch">
                                        <input onchange="update_approved(this)" value="{{ $cluster->id }}" type="checkbox" <?php if($cluster->status == 1) // echo "checked";?> >
                                        <span class="slider round"></span>
                                    </label>
                                </td> -->
                                <td>{{date('d-m-Y h:i:s' , strtotime($cluster->created_at))}}</td>
                                
                                
                                <td>
                                    <div class="btn-group dropdown">
                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                            {{translate('Actions')}} <i class="dropdown-caret"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">                                          
                                            <li><a href="{{route('cluster.login', encrypt($cluster->id))}}">{{translate('Log in as this Cluster Hub')}}</a></li>
                                            <li><a href="{{route('clusterhub.edit', encrypt($cluster->id))}}">{{translate('Edit')}}</a></li>
                                            <!-- <li><a onclick="confirm_modal('{{route('cluster.destroy', $cluster->id)}}');">{{translate('Delete')}}</a></li> -->
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
                    {{ $clusters->appends(request()->input())->links() }}
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

        function update_approved(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('cluster.approved') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'cluster status updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
        }

        function sort_sellers(el){
            $('#sort_sellers').submit();
        }

        function confirm_ban(url)
        {
            $('#confirm-ban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmation').setAttribute('href' , url);
        }

        function confirm_unban(url)
        {
            $('#confirm-unban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmationunban').setAttribute('href' , url);
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