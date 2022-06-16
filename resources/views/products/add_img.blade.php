@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Add Media') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('products.storemedia')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<input type="hidden" name="added_by" value="admin">

			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Add Images')}}</h3>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Gallery Images')}} </label>
						<div class="col-lg-7">
							<div id="photos">

							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Thumbnail Image')}} <small>(290x300)</small></label>
						<div class="col-lg-7">
							<div id="thumbnail_img">

							</div>
						</div>
					</div>
				</div>
			</div>
			
			
			
			
        
			
			<div class="mar-all text-right">
				<button type="submit" name="button" class="btn btn-info">{{ translate('Add Media') }}</button>
			</div>
		</form>
	</div>
</div>


@endsection

@section('script')

<script type="text/javascript">
	
	 function add_more_customer_choice_option(i, name){

        $.ajax({
           type:'GET',
           url:'{{url("admin/products/getlist/")}}', 
           data:{'id':i},
           success: function(res){
                // console.log(res);
                $("#test"+i).html(res);
                
           }
        })

        $('#customer_choice_options').append('<div class="form-group"><div class="col-lg-2"><input type="hidden" name="choice_no[]" value="'+i+'"><input type="text" class="form-control" name="choice[]" id="current_name_'+i+'" value="'+name+'" placeholder="{{ translate('Choice Title') }}" readonly></div><div class="col-lg-7" id="test'+i+'"></div><input type="hidden" class="form-control" id="choice_options_'+i+'" name="choice_options_'+i+'[]" placeholder="{{ translate('Enter choice values') }}" data-role="tagsinput" onchange="update_sku()" style=""></div>');

        $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
    }

	$('input[name="colors_active"]').on('change', function() {
	    if(!$('input[name="colors_active"]').is(':checked')){
			$('#colors').prop('disabled', true);
		}
		else{
			$('#colors').prop('disabled', false);
		}
		update_sku();
	});

	$('#colors').on('change', function() {
	    update_sku();
	});

	$('input[name="unit_price"]').on('keyup', function() {
	    update_sku();
	});

	$('input[name="name"]').on('keyup', function() {
	    update_sku();
	});

	function delete_row(em){
		$(em).closest('.form-group').remove();
		update_sku();
	}

	


	function get_vendors_by_manage_id(){
		var manage_id = $('#managed_id').val();
		$.post('{{ route('vendors.get_vendors_by_manage_id') }}',{_token:'{{ csrf_token() }}', manage_id:manage_id}, function(data){
		    $('#seller_id').html(null);
		    for (var i = 0; i < data.length; i++) {
		        $('#seller_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		        $('.demo-select2').select2();
		        update_sku();
		    }
		});
	}

	function get_subcategories_by_category(){
		var category_id = $('#category_id').val();
		$.post('{{ route('subcategories.get_subcategories_by_category') }}',{_token:'{{ csrf_token() }}', category_id:category_id}, function(data){
		    $('#subcategory_id').html(null);
		    for (var i = 0; i < data.length; i++) {
		        $('#subcategory_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		        $('.demo-select2').select2();
		    }
		    get_subsubcategories_by_subcategory();
		});
	}

	function get_subsubcategories_by_subcategory(){
		var subcategory_id = $('#subcategory_id').val();
		$.post('{{ route('subsubcategories.get_subsubcategories_by_subcategory') }}',{_token:'{{ csrf_token() }}', subcategory_id:subcategory_id}, function(data){
		    $('#subsubcategory_id').html(null);
			$('#subsubcategory_id').append($('<option>', {
				value: null,
				text: null
			}));
		    for (var i = 0; i < data.length; i++) {
		        $('#subsubcategory_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		        $('.demo-select2').select2();
		    }
		    //get_brands_by_subsubcategory();
			//get_attributes_by_subsubcategory();
		});
	}

	function get_brands_by_subsubcategory(){
		var subsubcategory_id = $('#subsubcategory_id').val();
		$.post('{{ route('subsubcategories.get_brands_by_subsubcategory') }}',{_token:'{{ csrf_token() }}', subsubcategory_id:subsubcategory_id}, function(data){
		    $('#brand_id').html(null);
		    for (var i = 0; i < data.length; i++) {
		        $('#brand_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		        $('.demo-select2').select2();
		    }
		});
	}

	function get_attributes_by_subsubcategory(){
		var subsubcategory_id = $('#subsubcategory_id').val();
		$.post('{{ route('subsubcategories.get_attributes_by_subsubcategory') }}',{_token:'{{ csrf_token() }}', subsubcategory_id:subsubcategory_id}, function(data){
		    $('#choice_attributes').html(null);
		    for (var i = 0; i < data.length; i++) {
		        $('#choice_attributes').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		    }
			$('.demo-select2').select2();
		});
	}

	$(document).ready(function(){
	    get_subcategories_by_category();
		$("#photos").spartanMultiImagePicker({
			fieldName:        'photos[]',
			maxCount:         10,
			rowHeight:        '200px',
			groupClassName:   'col-md-4 col-sm-4 col-xs-6',
			maxFileSize:      '',
			dropFileLabel : "Drop Here",
			onExtensionErr : function(index, file){
				console.log(index, file,  'extension err');
				alert('Please only input png or jpg type file')
			},
			onSizeErr : function(index, file){
				console.log(index, file,  'file size too big');
				alert('File size too big');
			}
		});
		$("#thumbnail_img").spartanMultiImagePicker({
			fieldName:        'thumbnail_img',
			maxCount:         1,
			rowHeight:        '200px',
			groupClassName:   'col-md-4 col-sm-4 col-xs-6',
			maxFileSize:      '',
			dropFileLabel : "Drop Here",
			onExtensionErr : function(index, file){
				console.log(index, file,  'extension err');
				alert('Please only input png or jpg type file')
			},
			onSizeErr : function(index, file){
				console.log(index, file,  'file size too big');
				alert('File size too big');
			}
		});
		$("#meta_photo").spartanMultiImagePicker({
			fieldName:        'meta_img',
			maxCount:         1,
			rowHeight:        '200px',
			groupClassName:   'col-md-4 col-sm-4 col-xs-6',
			maxFileSize:      '',
			dropFileLabel : "Drop Here",
			onExtensionErr : function(index, file){
				console.log(index, file,  'extension err');
				alert('Please only input png or jpg type file')
			},
			onSizeErr : function(index, file){
				console.log(index, file,  'file size too big');
				alert('File size too big');
			}
		});
	});

	$('#managed_id').on('change', function() {
	    get_vendors_by_manage_id();
	});

	$('#category_id').on('change', function() {
	    get_subcategories_by_category();
	});

	$('#subcategory_id').on('change', function() {
	    get_subsubcategories_by_subcategory();
	});

	$('#subsubcategory_id').on('change', function() {
	    // get_brands_by_subsubcategory();
		//get_attributes_by_subsubcategory();
	});

	$('#choice_attributes').on('change', function() {
		$('#customer_choice_options').html(null);
		$.each($("#choice_attributes option:selected"), function(){
			//console.log($(this).val());
            add_more_customer_choice_option($(this).val(), $(this).text());
        });
		update_sku();
	});

$("#copy").click(function(){
	console.log($("#json_tags").val());
	copyToClipboard($("#json_tags").val());
	$("#copy_tags").html("copied");
});

$("#meta_tags").click(function(){
	console.log($("#tags").val());
	copyToClipboard($("#tags").val());
	$("#copy_meta_tags").html("copied");
});

function copyToClipboard(text) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
}

</script>

@endsection
