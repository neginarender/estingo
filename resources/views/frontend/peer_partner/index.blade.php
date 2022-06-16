@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
           <a href="{{ route('peer_partner.createpeer')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Peer Discount')}}</a>
           <a href="{{ route('peer_partner.peer_commision')}}" class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('View All Peer Commission')}}</a>
        </div>
    </div>

    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Peer Partner')}}</h3>
            <div class="pull-right clearfix">
                <form class="" id="sort_sellers" action="" method="GET">
                    <div class="box-inline pad-rgt pull-left">
                        <div class="select" style="min-width: 300px;">
                            <select class="form-control demo-select2" name="approved_status" id="approved_status" onchange="sort_sellers()">
                                <option value="">{{translate('Filter by Approval')}}</option>
                                <option value="1"  @isset($approved) @if($approved == '1') selected @endif @endisset>{{translate('Approved')}}</option>
                                <option value="0"  @isset($approved) @if($approved == '0') selected @endif @endisset>{{translate('Non-Approved')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="box-inline pad-rgt pull-left">
                        <div class="" style="min-width: 200px;">
                            <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name or email & Enter') }}">
                        </div>
                    </div>

                      <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="submit" class="form-control btn btn-rounded btn-info" value="search">

                    </div>
                </div>
                </form>
            </div>
        </div>
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <div class="pull-right clearfix">
                <!-- <a href="{{ route('peer_partner.export') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('Export')}}</a> -->
                <form name="export_peer" method="GET" action="{{ route('peer_partner.export') }}">
                    <input type="hidden" name="status" id="status" value="<?php 
                    if(isset($approved) ){
                        echo $approved;
                    }
                ?>">
                    <input type="submit" name="exportpeer" id="exportpeer" value="Export">
                </form>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Type')}}</th>
                    <th>{{translate('Parent')}}</th>
                    <th>{{translate('Phone')}}</th>
                    <th>{{translate('Email Address')}}</th>
                    <th>{{translate('Referral Code') }}</th>
                    <th>{{ translate('Added By') }}</th>
                    <th>{{translate('Approval')}}</th>
                    <th>{{translate('Master')}}</th>
                    <th>{{translate('Created Date')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($peer_partner as $key => $partner)
                    @if($partner->user != null)
                    @php $user = \App\User::find($partner->added_by); @endphp
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>@if($partner->user->banned == 1) <i class="fa fa-ban text-danger" aria-hidden="true"></i> @endif {{$partner->user->name}}</td>
                            <td><?php if($partner->peer_type == 'master'){?> <span>Master</span> <?php } else { ?> <span>Sub Peer</span> <?php }?></td>
                            <td>
                            <?php $master_name = App\PeerPartner::where('id', $partner->parent)->first('name');
                                if(!empty($master_name)){
                                    echo $master_name->name;
                                }else{
                                    echo 'NA';
                                }
                            ?>

                            </td>
                            <td>{{$partner->user->phone}}</td>
                            <td>{{$partner->user->email}}</td>
                            <td>{{$partner->code}}<br>
                                @php
                                if($partner->code != NULL){
                                    $used_code = Count(App\User::where('used_referral_code',$partner->code)->get());
                                @endphp
                                @if($used_code == 0)
                                <a title="Want to Change? Click Here!"   href="javascript:;" class="btn btn-info wantToChange_{{$partner->id}}">Change</a>
                                <div  style="display:none;" class="parent custom_div_{{$partner->id}}">
                                    <form name="custom_code" id="custom_code_{{$partner->id}}" method="POST" action="{{route('peer_partner.custom_code')}}">
                                        @csrf
                                        <input type="text" name="custom_code" id="custom_code_{{$partner->id}}" value="{{$partner->code}}"  class="form-control" style="margin-bottom: 10px;">
                                        <input type="hidden" name="partner_id" value="{{$partner->id}}">
                                        <input type="submit" name="submit" value="Submit" class="btn btn-success form-control">
                                    </form>
                                </div>

                                <script type="text/javascript">
                                    $(document).ready(function(){
                                        $('.wantToChange_{{$partner->id}}').click(function() { 
                                            $('.custom_div_{{$partner->id}}').toggle();
                                            $('.wantToChange_{{$partner->id}}').toggle();
                                            
                                        });

                                     });
                                    </script>
                                @endif
                                @php
                                    }
                                @endphp
                            </td>
                            <td>@if(@$user->user_type == 'admin')  <span class="tab success">{{'Admin'}}</span>@else  <span class="tab pending">{{ 'Customer' }}</span> @endif</td>
                            <td>
                                <label class="switch">
                                    <input onchange="update_approved(this)" value="{{ $partner->id }}" type="checkbox" <?php if($partner->verification_status == 1) echo "checked";?> >
                                    <span class="slider round"></span>
                                </label>
                            </td>

                            <td>
                                <label class="switch">
                                    @if($partner->email != 'defaultpeer@rozana.in')
                                        <input onchange="update_subapproved(this)" value="{{ $partner->id }}" type="checkbox" <?php if($partner->peertype_approval == 1) echo "checked";?> >
                                        <span class="slider round"></span>
                                    @else
                                        <span>Default Master Peer</span>
                                    @endif
                                </label>
                            </td>

                            <td>{{ date('d M Y', strtotime($partner->created_at)) }}</td>
                         
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                    @if(is_null($partner->user->rzp_contact_id) && $partner->verification_status == 1)
                                        <li><a href="{{ route('create.contact',['id'=>$partner->id]) }}">Create Contact</a></li>
                                        @endif
                                        <li><a href="{{ route('peer_commission.show', encrypt($partner->id)) }}">{{translate('View Commission')}}</a></li>   

                                        <li><a href="javascript:;" onclick="show_partner_details('{{$partner->id}}');">{{translate('View')}}</a></li>

                                        @if($partner->peer_type =='master' && $partner->peertype_approval == 1)
                                           <li><a href="{{ route('peer_partner.sub_peer', encrypt($partner->id)) }}">{{translate('Manage Sub Peers')}}</a></li> 
                                        @endif

                                        <li>
                                             <a href="{{route('peer_partner.login', encrypt($partner->id))}}">
                                                @if($partner->verification_status == 1) {{translate('Log in as this Partner')}} @else {{translate('Log in as this User')}}  @endif
                                            </a>
                                        </li>
                                        <li><a href="{{route('admin.edit_partner_admin', encrypt($partner->id))}}">{{translate('Edit')}}</a></li>


                                        <!-- @if($partner->user->banned != 1)
                                        <li><a href="#" onclick="confirm_ban('{{route('peer_partner.ban', $partner->id)}}');">{{translate('Ban this partner')}}  <i class="fa fa-ban text-danger" aria-hidden="true"></i> </a></li>
                                        @else
                                        <li><a href="#" onclick="confirm_unban('{{route('peer_partner.ban', $partner->id)}}');">{{translate('Unban this partner')}} <i class="fa fa-check text-success" aria-hidden="true"></i></a></li>
                                        @endif -->

                                       <!--  <li><a onclick="confirm_modal('{{route('peer_partner.destroy', $partner->id)}}');">{{translate('Delete')}}</a></li> -->
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
                    {{ $peer_partner->appends(request()->input())->links() }}
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
            $.post('{{ route('peer_partner.approved') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Peer Partner status updated successfully');
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

