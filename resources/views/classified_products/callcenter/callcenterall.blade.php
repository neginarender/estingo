@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
           <!-- <a href="{{ URL('admin/operations')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New operations  User')}}</a> -->
           <a href="{{ URL('admin/callcenter')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New User')}}</a>
           <a href="{{ route('callceter.operationsuser')}}" class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('Add New operations  User')}}</a>
        </div>
    </div>

    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
      <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('User List')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_brands" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type  text ') }}">
                    </div>
                </div>
                 <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="submit" class="form-control btn btn-rounded btn-info" value="search">

                    </div>
                </div>
            </form>
        </div>
    
            <div class="pull-right clearfix">
                <!-- <a href="{{ route('peer_partner.export') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('Export')}}</a> -->
                <form name="export_peer" method="GET" action="{{ route('peer_partner.export') }}">
                    <input type="hidden" name="status" id="status" value="<?php 
                    if(isset($approved) ){
                        echo $approved;
                    }
                ?>">
                    <!-- <input type="submit" name="exportpeer" id="exportpeer" value="Export"> -->
                </form>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th>{{translate('Email Address')}}</th>
                    <th>{{translate('Role') }}</th>
                    <th>{{ translate('Date') }}</th>
                    <th>{{translate('Approval')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($callcenterall as $key => $partner)
                        <tr>
                            <td>{{$key+1}}</td>
                     
                            <td>{{$partner->name}}</td>
                            <td>{{$partner->phone}}</td>
                            <td>{{$partner->email}}</td>
                            <td>{{$partner->role}}</td>
                            

                    

                                <script type="text/javascript">
                                    $(document).ready(function(){
                                        $('.wantToChange_{{$partner->id}}').click(function() { 
                                            $('.custom_div_{{$partner->id}}').toggle();
                                            $('.wantToChange_{{$partner->id}}').toggle();
                                            
                                        });

                                     });
                                    </script>
                            
                         

                      

                            <td>{{ date('d M Y', strtotime($partner->created_at)) }}</td>
                              <td>
                                <label class="switch">
                                    <input onchange="update_approved(this)" value="{{ $partner->customercare_id }}" type="checkbox" <?php if($partner->  isactive == 1) echo "checked";?> >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                @endforeach
                </tbody>
            </table>
           <div class="clearfix">
                <div class="pull-right">
                    {{ $callcenterall->appends(request()->input())->links() }}
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

        function show_partner_details(id){
            $.post('{{ route('peer_partner.profile_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
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
            $.post('{{ route('callceter.isactive') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Active User successfully');
                     location.reload();
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
        }

         function update_subapproved(el){
            if(el.checked){
                var status = 1;
                var type = 'master';
            }
            else{
                var status = 0;
                var type = 'sub';
            }
            $.post('{{ route('peer_partner.subapproved') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status, type:type}, function(data){
                if(data == 1){
                    showAlert('success', 'Peer Type Changed.');
                    location.reload();
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
                    <p>{{translate('Do you really want to ban this peer partner?')}}</p>
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
                    <p>{{translate('Do you really want to unban this peer partner?')}}</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <a id="confirmationunban" class="btn btn-success btn-ok">{{translate('Proceed!')}}</a>
                </div>
            </div>
        </div>
    </div>

<style type="text/css">
.tab { font-size: 11px; padding: 5px 7px; color: #fff; }
.success { background: #80ae00; } .pending { background: #b78e41d1; }
</style>
@endsection

@section('script')
    <script type="text/javascript">
        function sort_brands(el){
            $('#sort_brands').submit();
        }
    </script>
@endsection