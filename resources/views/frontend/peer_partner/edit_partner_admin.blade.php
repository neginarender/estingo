@extends('layouts.app')

@section('content')
<style>
	.error{
		color: red;
	}
</style>

<div>
    <h1 class="page-header text-overflow">{{ translate('Edit Partner') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('admin.update_partner_admin')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<input type="hidden" name="added_by" value="admin">
			<input type="hidden" name="parent_id" class="parent_id" value="{{ $partner->parent }}" />
            <input type="hidden" name="user_id" value="{{ $partner->user_id }}" />
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('User Information')}}</h3>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Name')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="name" value="{{ $user->name }}" placeholder="{{ translate('Name') }}">
							
							@if($errors->has('name'))
                            <div class="error  mr-top">{{ $errors->first('name') }}</div>
                        	@endif
						</div>
                        
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Phone')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<input type="number" class="form-control"  name="phone" value="{{ $user->phone }}" placeholder="{{ translate('Phone') }}" required>
							
							@if($errors->has('phone'))
                            <div class="error  mr-top">{{ $errors->first('phone') }}</div>
                        	@endif
						</div>
                        
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Email')}}</label>
						<div class="col-lg-7">
							<input type="email" class="form-control" name="email" value="{{ $user->email }}" placeholder="{{ translate('Email') }}">
							
							@if($errors->has('email'))
                            <div class="error  mr-top">{{ $errors->first('email') }}</div>
                        	@endif
						</div>
                        
					</div>
					<div class="form-group" id="subsubcategory">
						<label class="col-lg-2 control-label">{{translate('Address')}} <span class="error">*</span></label>
						<div class="col-lg-7">
                        <input type="text" class="form-control" name="address" value="{{ $user->address }}" placeholder="{{ translate('Address') }}">
						
						@if($errors->has('address'))
                            <div class="error  mr-top">{{ $errors->first('address') }}</div>
                        @endif
						</div>
                        
					</div>
					
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('State')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<input type="hidden" name="state" id="state" value="" />
							<select class="form-control demo-select2 state_id" name="state_id" id="state_id" onchange="loadList(this,'abc')">
								<option value="">Select State</option>
								@foreach(\App\State::where('status',1)->get() as $key => $state)
									<option value="{{ $state->id }}" @if($user->state==$state->name) selected @endif>{{ $state->name }}</option>
								@endforeach
							</select>
							@if($errors->has('state'))
                            <div class="error  mr-top">{{ $errors->first('state') }}</div>
                        	@endif
						</div>
                        
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('City/District')}} <span class="error">*</span></label>
						<div class="col-lg-7">
						<input type="hidden" name="city" id="city" value="" />
						<select class="form-control demo-select2 city_id" name="city_id" id="city_id" onchange="loadList(this,'abc')">
								<option value="">Select City/District</option>
								
							</select>
							@if($errors->has('city'))
                            <div class="error  mr-top">{{ $errors->first('city') }}</div>
                        	@endif
						</div>
                       
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Block/Taaluka')}} 
							<span class="error">*</span>
						</label>
						<div class="col-lg-7">
						<input type="hidden" name="block" id="block" value="" />
						<select class="form-control demo-select2" name="block_id" id="block_id" onchange="loadList(this,'abc')">
								<option value="">Select Block</option>
							</select>
							@if($errors->has('block_id'))
                            <div class="error  mr-top">{{ $errors->first('block_id') }}</div>
                        	@endif
						</div>
                       
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Gram Panchayat')}} 
							<!-- <span class="error">*</span> -->
						</label>
						<div class="col-lg-7">
						<input type="text" class="form-control" name="village" value="{{ $address->village }}" placeholder="Gram Panchayat" />
							@if($errors->has('village'))
                            <div class="error  mr-top">{{ $errors->first('village') }}</div>
                        	@endif
						</div>
                       
					</div>
                    
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Pincode')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<select class="form-control pindata demo-select2" name="pincode" id="pincode_id">
								<option value="">Select Pincode</option>
							</select>
							
							@if($errors->has('pincode'))
                            <div class="error mr-top">{{ $errors->first('pincode') }}</div>
                       		 @endif
						</div>
                        
					</div>
                    
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Zone')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control zonedata" id="zone" name="zone" value="{{ $partner->zone }}" placeholder="{{ translate('Zone') }}" readonly>
                        </div>
                    </div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Refferal Code')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control blank_ref" id="referral_code" name="referral_code" value="{{ $parent_code }}" placeholder="{{ translate('Refferal Code') }}">
							
						</div>
						
					</div>
					<div class="form-group">
                                           
					<label class="col-lg-2 control-label">{{  translate('PAN No.') }}</label>
					
					<div class="col-lg-7">
					<input type="text" class="form-control mb-3" placeholder="{{ translate('PAN No.')}}" value="{{ $partner->pan_num }}" name="pannumber" id="panNumber">
					<br />
					<p style="font-size: 12px;color:green;margin-top: -10px;">First five characters are letters (A-Z), next 4 numerics (0-9), last character letter (A-Z)</p>
					</div>
					@if($errors->has('pannumber'))
                        <div class="error">{{ $errors->first('pannumber') }}</div>
                    @endif
					</div>

				</div>
			</div>
			
			<!-- <div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Social Media Information')}}</h3>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Do you have Facebook account ?')}}</label>
						<div class="col-lg-7">
							<select name="fb_account" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Do you have Instagram account ?')}}</label>
						<div class="col-lg-7">
                        <select name="instagram_account" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{ translate('Do you have LinkedIn profile ?') }}</label>
						<div class="col-lg-7">
                        <select name="linkedin_account" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>

							</div>
						</div>
				</div>
			</div> -->
			<div class="mar-all text-right">
				<button type="submit" name="button" class="btn btn-info">{{ translate('Update Partner') }}</button>
			</div>
		</form>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(document).ready(function(){
        $("#state_id").trigger('change',function(){
            
        });
        
      

		$('#referral_code').on('blur', function() {
        var referral_code = $('#referral_code').val();

        $.post("{{ route('peer_partner.referrals') }}", {_token:'{{ csrf_token() }}', referral_code:referral_code}, function(data){
            // console.log(data);
                if(data != 0){
                    $('.parent_id').val(data);
                    alert('Referral Code Successfully Applied');
                }
                else{
                    var myLengths = $("#referral_code").val().length;
                    if(myLengths!=0){
                        alert('The referral code entered by you does not exist');
                        $('.blank_ref').val('');
                    }    
                }
            });
  }); 

  

		$('#panNumber').on('blur', function() {
        var panVal = $('#panNumber').val();
        var regpan = /^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/;

        if(regpan.test(panVal)){
           // valid pan card number
        } else {
          var myLength = $("#panNumber").val().length;
          if(myLength!=0){
               alert('Please enter a valid pan number')
               $("#panNumber").val('');
          }     
        }
  });   
	});

	$('.pindata').on('change', function() {
        var pin_code = $('.pindata').val();       
        var pinlength = pin_code.toString().length;
        if(pinlength==6){

            $.post('{{ route('home.checkpin') }}', {_token:'{{ csrf_token() }}', pin_code:pin_code}, function(data){
                // console.log(data);
                 if(data != 0){
                    $('.zonedata').val(data);
                 }else{
                     alert('The pin code entered by you does not exist');
                     $('.pindata').val(''); 
                     $('.zonedata').val(''); 
                 }   
            });

        }else{
            alert('Please write correct pincode');
            $('.pindata').val(''); 
            $('.zonedata').val(''); 
        }
  });

  function loadList(el,abc){
	var id =$(el).attr('id');
    var firsttime =$("#state_id").attr('firsttime');
    var firsttimecity = $("#city_id").attr('firsttime');
	$("#"+id).prev("input").val($("#"+id+" option:selected").text());
	var url = "";

	var keyval = $(el).val();
	if(id=="state_id"){
		url = "{{ route('citylist') }}";
		data = {state_id:keyval};
		var loadid = "city_id";
	}

	if(id=="city_id"){
		url = "{{ route('blocklist') }}";
		data = {city_id:keyval};
		var loadid = "block_id";
	}

	if(id=="block_id"){
		url = "{{ route('pincodelist') }}";
		data = {block_id:keyval};
		var loadid = "pincode_id";
	}

	$.ajax({
	url: url,
	type: "get", //send it through get method
	data: data,
	success: function(response) {
		//Do Something
		$("#"+loadid).empty();
		$("#"+loadid).append("<option value=''>Select</option>");
		$.map(response.state.data,function(item){
			if(id=="city_id"){
                var selectblock = "";
                if(item.block_id=="{{ $address->block_id }}"){
                    selectblock = "selected";
                }
				$("#"+loadid).append("<option value="+item.block_id+" "+selectblock+">"+item.name+"</option>");
                
                if(typeof firsttimecity == 'undefined' && selectblock=="selected"){
                    console.log(firsttimecity);
                    $("#block_id").trigger('change',function(){
                        alert();
                        $("#city_id").attr('firsttime','yes');
                    });
                }
			}
			if(id=="state_id"){
				//console.log(item.name);
                var selectcity = "";

                if(item.name=="{{ $user->city}}"){
                    selectcity = "selected";
                    
                }
               

				$("#"+loadid).append("<option value="+item.city_id+" "+selectcity+">"+item.name+"</option>");
                if(typeof firsttime == 'undefined' && selectcity=="selected"){
                    $("#city_id").trigger('change',function(){
                        $("#state_id").attr('firsttime','yes');
                    });
                }
               
                
			}

			if(id=="block_id"){
				//console.log(item.name);
                var selectpincode = "";
                if(item.pincode=="{{ $user->postal_code }}"){
                    selectpincode = "selected";
                }
				$("#"+loadid).append("<option value="+item.pincode+" "+selectpincode+">"+item.pincode+"</option>");
			}
			
            });
            $('.demo-select2').select2();
	},
  error: function(xhr) {
    //Do Something to handle error
	console.log(xhr);
  }
});
  }
</script>
@endsection