@extends('layouts.app')

@section('content')

    <div class="col-lg-8 col-lg-offset-2">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">{{translate('User Mapping')}}</h3>
            </div>

            <form class="form-horizontal" action="{{ route('mapping.store') }}" method="POST" enctype="multipart/form-data">
            	@csrf
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-3 control-label" for="name">{{translate('User Type')}}</label>
                        <div class="col-lg-9">
                            <select name="mapping_type" id="mapping_type" class="form-control demo-select2" onchange="mapping_form()" required>
                                <option value="">{{translate('Select One') }}</option>
                                <option value="cluster_hub">{{translate('Cluster Hub')}}</option>
                                <option value="sorting_hub">{{translate('Sorting Hub')}}</option>
                                <!-- <option value="peer_partner">{{translate('Peer Partner')}}</option> -->
                            </select>
                        </div>
                    </div>

                    <div id="mapping_form">

                    </div>

                <div class="panel-footer text-right">
                    <button class="btn btn-purple" type="submit">{{translate('Save')}}</button>
                </div>
            </form>

        </div>
    </div>

@endsection
@section('script')

<script type="text/javascript">

    function mapping_form(){
        var mapping_type = $('#mapping_type').val();
		$.post('{{ route('mapping.get_mapping_form') }}',{_token:'{{ csrf_token() }}', mapping_type:mapping_type}, function(data){
            $('#mapping_form').html(data);

            $('#demo-dp-range .input-daterange').datepicker({
                startDate: '-0d',
                todayBtn: "linked",
                autoclose: true,
                todayHighlight: true
        	});
		});
    }

</script>

@endsection
