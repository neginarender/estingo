@extends('frontend.layouts.app')
@section('content')

<div class="content" style="height: 250px;">

    <div class="rowdisplay">       

        <img src="{{asset('/public/uploads/logo/loader.gif')}}" style="    margin-top: 40px;

                                                                           position: absolute;

                                                                           margin-left: 620px;

                                                                         

                                                                        ">

        <h3 style="color: red; padding-left: 540px;">Do not refresh this page...</h3>

    </div>

</div>

@endsection

@section('script')

<button id="rzp-button1" hidden>Pay</button>  

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>

var options = {

    "key": "{{$response['razorpayId']}}", 

    "amount": "{{$response['amount']}}",

    "currency": "{{$response['currency']}}",

    "name": "{{$response['name']}}",

    "description": "{{$response['description']}}",

    "image": "{{ my_asset(\App\GeneralSetting::first()->logo) }}", 

    "order_id": "{{$response['orderId']}}", 

    "handler": function (response){

      //console.log(response);

        document.getElementById('rzp_paymentid').value = response.razorpay_payment_id;

        document.getElementById('rzp_orderid').value = response.razorpay_order_id;

        document.getElementById('rzp_signature').value = response.razorpay_signature;
        
        document.getElementById('rzp-paymentresponse').click();

    },

    "prefill": {

        "name": "{{$response['name']}}",

        "email": "{{$response['email']}}",

        "contact": "{{$response['contactNumber']}}"

    },

    "notes": {

        "orderID": "{{$response['receipt']}}",

        "name": "{{$response['name']}}"

    },

    "theme": {

        "color": "#ff7529"

    },

     "modal": {

        "ondismiss": function(){

            window.location.href = "https://www.rozana.in/wallet";

        }

    }

};

var rzp1 = new Razorpay(options);
rzp1.on('payment.failed', function (response){
        console.log(response.error);
        document.getElementById('rzp_fail_paymentid').value = response.error.metadata.payment_id;
        document.getElementById('rzp_fail_orderid').value = response.error.metadata.order_id;
        document.getElementById('rzp_fail_reason').value = response.error.reason;
        document.getElementById('rzp_fail_desc').value = response.error.description
        
        document.getElementById('rzp-payment_fail_response').click();
        
});

window.onload = function(){

    document.getElementById('rzp-button1').click();

};



document.getElementById('rzp-button1').onclick = function(e){

    rzp1.open();

    e.preventDefault();

}



</script>



<form action="{!!route('payment.rozer')!!}" method="POST" hidden>

        <input type="hidden" value="{{csrf_token()}}" name="_token" /> 

        <input type="text" class="form-control" id="rzp_paymentid"  name="rzp_paymentid">

        <input type="text" class="form-control" id="rzp_orderid" name="rzp_orderid">

        <input type="text" class="form-control" id="rzp_signature" name="rzp_signature">

    <button type="submit" id="rzp-paymentresponse" class="btn btn-primary">Submit</button>

</form>
<form action="{!!route('payment.razorpay_fail')!!}" method="POST" hidden>

        <input type="hidden" value="{{csrf_token()}}" name="_token" /> 

        <input type="text" class="form-control" id="rzp_fail_paymentid"  name="rzp_fail_paymentid">

        <input type="text" class="form-control" id="rzp_fail_orderid" name="rzp_fail_orderid">
        <input type="text" class="form-control" id="rzp_fail_reason" name="rzp_fail_reason" />
        <input type="text" class="form-control" id="rzp_fail_desc" name="rzp_fail_desc" />

    <button type="submit" id="rzp-payment_fail_response" class="btn btn-primary">Submit</button>

</form>

@endsection



