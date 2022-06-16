@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<!--===================================================-->

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
    <div class="row">
       
        <div class="col-sm-12">
          
          
        </div>
    </div>

    <br>
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">Add Sub Peer</h3>
        <div class="panel-body">
        
          <form class="form form-horizontal mar-top" action="{{ route('peer_partner.add', encrypt($id)) }}" method="POST" >
            @csrf
                <div class="form-group" id="category">
                    <input type="hidden" name="master_id" value="{{$id}}">
                        <label class="col-lg-3 control-label">{{translate('Select Sub Peer')}}*</label>
                        <div class="col-lg-6">
                            <select class="form-control" name="sub_id" id="sub_id" required>
                                <option value="">Select One</option>
                                @foreach($allsub_peers as $sub)
                                    <option value="{{$sub->id}}">{{__($sub->name)}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
            <div class="mar-all text-right">
                <button type="submit" name="button" class="btn btn-info">{{ translate('Submit') }}</button>
            </div>

           </form> 
            
        </div>
    </div>
    <div class="panel-body">
       
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('Refferal Code')}}</th>
                    <th>{{translate('Verified')}}</th>
                    <th>{{translate('Date')}}</th>
                    <th>{{translate('Action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_peers as $key => $row)
                    
                    @if($all_peers != null)
                          
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$row->name}}</td>
                            <td>{{$row->email}}</td>
                            <td>@if($row->code==null) <span>NA</span> @else <span>{{$row->code}}</span>  @endif</td>
                            <td>@if($row->verification_status==0) <span>NO</span> @else <span>YES</span>  @endif</td>
                            <td> {{date('d-m-Y', strtotime($row->created_at)) }}</td>     
                            <td> <a onclick="confirm_modal('{{route('peer_partner.subpeerdestroy', $row->id)}}');" style="cursor: pointer;">{{translate('Remove')}}</a></td>                  
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        
    </div>
</div>

@endsection


@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5', 'csvHtml5'
                ]
            } );
        } );
    </script>
@endsection
