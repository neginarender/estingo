@extends('layouts.app')

@section('content')

    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate('Product Bulk Upload')}}</h3>
        </div>
        <div class="panel-body">
          
          <!--   <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                <strong>{{translate('Step 2')}}:</strong>
                <p>1. {{translate('Category,Sub category,Sub Sub category and Brand should be in numerical ids')}}.</p>
                <p>2. {{translate('You can download the pdf to get Category,Sub category,Sub Sub category and Brand id')}}.</p>
            </div> -->
           
            
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <h1 class="panel-title"><strong>{{translate('Upload Product Map File')}}</strong></h1>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" action="{{ route('productmap_bulk_product_upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" class="form-control" name="bulk_file" required>
                </div>
                <div class="form-group">
                    <div class="col-lg-12">
                        <input type="hidden" name="take_mobile" class="take_mobile" >
                        <input type="hidden" name="take_description" class="take_description" >
                        <button class="btn btn-primary otp_btn" type="button">{{translate('Upload CSV')}}</button>
                        <button class="btn btn-primary newclick" type="submit" style="display: none">{{translate('Upload CSV')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


<!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Verify OTP</h4>
        </div>
        <div class="modal-body">
        <form class="form form-horizontal mar-top" action="" method="POST" id="otp_form">
        @csrf
        
            <div class="form-group">
                <label class="col-lg-2 control-label">Mobile*</label>
                <div class="col-lg-7">
                    <input type="number" placeholder="Mobile" name="mobilenum" class="form-control get_mobile" autocomplete="off" id="getmobile" required>
                    <span style="color: green; display: none" class="show_verified">Verified</span>
                </div>
            </div>
             <div class="form-group otp_module" style="display: none;">
                <label class="col-lg-2 control-label">OTP*</label>
                <div class="col-lg-7">
                    <input type="hidden" value="" name="check_code" class="check_code"> 
                    <input type="text" placeholder="OTP" name="otp" class="form-control get_otp" autocomplete="off" required>
                    <span style="color: green; display: none" class="show_verifiedotp">Verified</span>
                </div>
            </div>

            <div class="form-group description_v" style="display: none;">
                <label class="col-lg-2 control-label">Description*</label>
                <div class="col-lg-7">
                    <input type="text" placeholder="Description" name="description_name" class="form-control description_name" required>
                </div>

                <div class="mar-all text-right">
                <button type="button" class="btn btn-info verifyotp" disabled="disabled">{{ translate('Verify') }}</button>
                <button type="button" class="btn btn-info verifiedotp" style="display: none">{{ translate('Verify') }}</button>
                </div>
            </div>


        </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-default close_modal" data-dismiss="modal">Close</button>
        </div>
        </div>

    </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">

$('.otp_btn').on('click', function() {
    $('#myModal').modal('show');
});

$("#getmobile").blur(function(){
    var mobile = $('.get_mobile').val();
    $.post('{{ route('productimport.set_otp') }}',{_token:'{{ csrf_token() }}', mobile:mobile}, function(data){
       // console.log(data);
       if(data==1){
            $('.show_verified').show();
            $('.otp_module').show();
            $('.get_otp').val('');
            $('.verifiedotp').hide();
            $('.verifyotp').show();
            $('.description_v').show();
       }else{
            alert('The Mobile number you entered is incorrect.');
            $('.otp_module').hide();
            $('.show_verified').hide();
            $('.show_verifiedotp').hide();
            $('.description_v').hide();
       }
        
  });
});

$(".get_otp").blur(function(){
    var mobile = $('.get_mobile').val();
    var otp = $('.get_otp').val();
    $.post('{{ route('productimport.get_otp') }}',{_token:'{{ csrf_token() }}', mobile:mobile, otp:otp}, function(data){
       if(data==1){
            $('.show_verifiedotp').show();
            $('.verifiedotp').show();
            $('.verifyotp').hide();
       }else{
            alert('The OTP you entered is incorrect.');
            $('.verifiedotp').hide();
            $('.verifyotp').show();
       }
        
  });
});

$(".verifiedotp").click(function(){
    var description_name = $('.description_name').val();
    // alert(description_name);
    if(description_name!=''){
        var mobilenum = $('.get_mobile').val();
        $('.take_description').val(description_name);
        $('.take_mobile').val(mobilenum);
        $('.close_modal').trigger("click");
        $(".newclick").trigger("click");
    }else{
        alert('Please fill all mandatory fields');
    }
});
</script>
@endsection
