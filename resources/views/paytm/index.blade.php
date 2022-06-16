@extends('frontend.layouts.app')
@section('content')
    <div id="paytm-checkoutjs"></div>
    <script>
    function onScriptLoad(){
        var token = "{{$res->body->txnToken}}";
        console.log(token);
        var config = {
         "root": "",
         "flow": "DEFAULT",
         "data": {
          "orderId":  "{{$order->code}}" /* update order id */,
          "token": token /* update token value */,
          "tokenType": "TXN_TOKEN",
          "amount": "{{$amount}}" /* update amount */
         },
         "handler": {
            "notifyMerchant": function(eventName,data){
              console.log("notifyMerchant handler function called");
              console.log("eventName => ",eventName);
              console.log("data => ",data);
              if(eventName == 'APP_CLOSED'){
                window.history.back()
              }
            } 
          }
        };

        if(window.Paytm && window.Paytm.CheckoutJS){
            window.Paytm.CheckoutJS.onLoad(function excecuteAfterCompleteLoad() {
                // initialze configuration using init method 
                window.Paytm.CheckoutJS.init(config).then(function onSuccess() {
                   // after successfully update configuration invoke checkoutjs
                   window.Paytm.CheckoutJS.invoke();
                }).catch(function onError(error){
                    console.log("error => ",error);
                });
            });
        } 
    }
</script>
<script type="application/javascript" crossorigin="anonymous" src="
https://securegw.paytm.in/merchantpgpui/checkoutjs/merchants/wBTQBg46282026626557.js" onload="onScriptLoad();"></script>

@endsection