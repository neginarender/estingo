@extends('layouts.app')

@section('content') 

<div class="row">

		<div class="panel">
			<div class="panel-heading">
				<h1 class="panel-title"><strong>{{translate('Upload CSV DOFO Orders')}}</strong></h1>
			</div>
			<div class="panel-body">
				<form class="form-horizontal" action="{{ route('DOFO.upload-csv-order') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<input type="file" class="form-control" name="csv_dofo_orders" required>
					</div>
					<div class="form-group">
						<div class="col-lg-12">
							<button class="btn btn-primary" type="submit">{{translate('Upload CSV Order')}}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('CSV Order List')}}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
               
                <tr>
                    <th>#</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('product_ids')}}</th>
                    <th>{{translate('product_qty')}}</th>
                    <th>{{translate('peer_code')}}</th>
                    <th>{{translate('created_date')}}</th>
                    <th>{{translate('created_time')}}</th>
                    <th>{{translate('order_code')}}</th>
                </tr>
                </thead>
                <tbody>
                
                @foreach($csvorders as $key => $value)
                
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{$value->email}}</td>
                        <td>{{$value->product_ids}}</td>
                        <td>{{$value->product_qty}}</td>
                        <td>{{$value->peer_code}}</td>
                        <td>{{$value->created_date}}</td>
                        <td>{{$value->created_time}}</td>
                        <td>{{$value->order_code}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$csvorders->links()}}
            {{-- <div class="clearfix">
                <div class="pull-right">
                    
                </div>
            </div> --}}
        </div>
    </div>

@endsection