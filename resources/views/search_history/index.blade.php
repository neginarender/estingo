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
        <h3 class="panel-title pull-left pad-no">{{translate('Serach history')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_customers" action="{{ route('search_history.download') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                    
                        <input type="text" class="form-control datepicker" id="date_from" name="date_from" value="{{ date('d-m-Y')}}" placeholder="Date From">
                    
                     </div>
                </div>
                
                <div class="box-inline pad-rgt pull-left">
                
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control datepicker" id="date_to" name="date_to" value="{{ date('d-m-Y')}}" placeholder="Date To">
                    
                     </div>
                </div>
                
                <div class="box-inline pad-rgt pull-left">
               
                    <div class="" style="min-width: 200px;">
                        <!-- <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type email or name & Enter') }}">
                    -->
                    <button type="submit" class="btn btn-success btn-sm">Download Excel</span></a>
                     </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body">
    <form action="{{ route('search_history.delete') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
        
            <thead>
            <tr><td colspan="6"><button type="submit" class="btn btn-danger btn-sm">Delete</button></td></tr>
                <tr>
                    <th><input type="checkbox" id="allCheck" onclick="checkAll(this.value)" /></th>
                    <th>#</th>
                    <th>{{translate('Search Keyword')}}</th>
                    <th>{{translate('Ip Address')}}</th>
                    <th>{{translate('Customer ID')}}</th>
                    <th width="10%">{{translate('Date')}}</th>
                </tr>
            </thead>
            <tbody>
            
                @foreach($searches as $key => $search)
                    @if (!empty($search->search))
                        @php 
                        if(!empty($search->customer_id))
                        {
                            $customer = \App\User::find($search->customer_id);
                            $customer_name = $customer->name;
                        }
                        
                            
                        @endphp
                        <tr>
                            <td><input type="checkbox" name="check[]" class="check" value="{{ $search->id }}" /></td>
                            <td>{{ ($key+1) + ($searches->currentPage() - 1)*$searches->perPage() }}</td>
                            <td>{{ $search->search }}</td>
                            <td>{{$search->customer_ip}}</td>
                            <td>@if(!empty($search->customer_id)) {{ $customer_name }} @endif</td>
                            <td>
                            {{ date('d-m-Y',strtotime($search->created_at)) }}
                                <!-- <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        
                                       
                                    </ul>
                                </div> -->
                            </td>
                        </tr>
                    @endif
                @endforeach
               
            </tbody>
            
        </table>
        <form>
         <div class="clearfix">
            <div class="pull-right">
                {{ $searches->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#allCheck").click(function(){
    $('input:checkbox').not(this).prop('checked', this.checked);
});

$('.datepicker').datepicker({
    format: 'dd-mm-yyyy'
});

</script>
@endsection

