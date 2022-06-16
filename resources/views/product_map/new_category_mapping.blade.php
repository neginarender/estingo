@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Category Mapping') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('sorting_hub.store_map_categories')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Product Mapping')}}</h3>
				</div>
				<div class="panel-body">

					@if(auth()->user()->staff->role->name == "Sorting Hub")
						<input type="hidden" class="form-control" name="sorting_hub_id" value="{{auth()->user()->id}}">
						<div class="form-group">
							<label class="col-lg-2 control-label">{{translate('Category')}}</label>
							<div class="col-lg-7">
								<select class="form-control demo-select2-placeholder category_id" name="category_ids[]" data-selected-text-format="count" data-actions-box="true" required="" multiple>
									<option value="">Select Category</option>
									@foreach($categories as $key => $category)
										<option value="{{$category->id}}">{{$category->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					@endif

			        </div>
                    <div class="panel-footer">
                    <div class="text-right" style="margin-top: 15px;">
                        <button type="submit" class="btn btn-info">{{ translate('Submit') }}</button>
                    </div>
                    </div>
			        

	</form>
	</div>
</div>

@endsection

@section('script')

	<script type="text/javascript">
	</script>
@endsection

