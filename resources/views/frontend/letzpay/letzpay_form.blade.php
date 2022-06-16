<!-- checkout Content -->

<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="checkout-area">
<div class="container">
<div class="row">
<div style="text-align: center"><h2>Please wait .........</h2>
<form method="post" action="{{ $url }}" name="f1">

<table border="1">
<tbody>
<?php
foreach($result['paramList'] as $name => $value) {

//cho '<input type="hidden" name="' . $name .'" value="' . $value . '">';
}
?>


<input type="hidden" name="AMOUNT" value="<?php echo $result['paramList']["AMOUNT"]; ?>">
<input type="hidden" name="CURRENCY_CODE" value="<?php echo $result['paramList']["CURRENCY_CODE"]; ?>">
<input type="hidden" name="CUST_EMAIL" value="<?php echo $result['paramList']["CUST_EMAIL"]; ?>">
<input type="hidden" name="CUST_NAME" value="<?php echo $result['paramList']["CUST_NAME"]; ?>">
<input type="hidden" name="CUST_PHONE" value="<?php echo $result['paramList']["CUST_PHONE"]; ?>">
<input type="hidden" name="ORDER_ID" value="<?php echo $result['paramList']["ORDER_ID"]; ?>">
<input type="hidden" name="PAY_ID" value="<?php echo $result['paramList']["PAY_ID"]; ?>">
<input type="hidden" name="RETURN_URL" value="<?php echo $result['paramList']["RETURN_URL"]; ?>">
<input type="hidden" name="TXNTYPE" value="<?php echo $result['paramList']["TXNTYPE"]; ?>">
<input type="hidden" name="PROD_DESC" value="<?php echo $result['paramList']["PROD_DESC"];?>">

<input type="hidden" name="HASH" value="<?php echo $result['hashdata']; ?>">
</tbody>
</table>

</form>
</div>
</div>
</div>
</section>

<script>
document.f1.submit();
</script>
