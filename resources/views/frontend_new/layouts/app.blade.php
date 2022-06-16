<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, 
         user-scalable=0">
    <meta name="robots" content="index, follow">
    <title>Rozana</title>
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Rozana">
    <meta name="sitemap_link" content="">
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="Rozana">
    <meta itemprop="description" content="">
    <meta itemprop="image" content="">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="Rozana">
    <meta name="twitter:description" content="">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="">

    <!-- Open Graph data -->
    <meta property="og:title" content="Rozana" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <meta property="og:description" content="" />
    <meta property="og:site_name" content="Rozana" />
    <meta property="fb:app_id" content="">
    <meta property="og:image" itemprop="image" content="" />

    
    <!-- Favicon -->
    <link type="image/x-icon" href="{{static_asset('frontend/new/assets/images/fav.png')}}" rel="shortcut icon" />
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet" >
     <!-- Custom style -->

    <link type="text/css" href="{{ static_asset('frontend/new/assets/css/style.css') }}" rel="stylesheet"  >
    <link rel="stylesheet" href="{{ static_asset('frontend/new/assets/css/font-awesome.min.css') }}" type="text/css"  >
    <link type="text/css" href="{{ static_asset('frontend/new/assets/css/custom.css') }}" rel="stylesheet" >
    <!-- jQuery -->
    @yield('css')
    
</head>

<body>

    <div id="loader"><img class="center" src="{{ static_asset('frontend/new/assets/images/loader1.jpg')}}" /></div>
    <!-- MAIN WRAPPER -->
    <div class="body-wrap shop-default shop-cards shop-tech gry-bg">
        @include('frontend_new.inc.nav')
        @yield('content')
                    

        @include('frontend_new.inc.footer')
    </div>
        <!--  main content end-->

        <!-- SCRIPTS -->
        <script src="{{ static_asset('frontend/new/assets/js/vendor/jquery.min.js') }}"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{{ static_asset('frontend/new/assets/js/typeahead.bundle.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/vendor/popper.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/vendor/bootstrap.min.js') }}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/select2.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/sweetalert2.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/slick.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/bootstrap-tagsinput.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/xzoom.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/lazysizes.min.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/active-shop.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/main.js')}}"></script>
        <script src="{{ static_asset('frontend/new/assets/js/custom.js')}}"></script>

        @yield('script')

        <script type="text/javascript">
            window.onunload = function() { debugger; }
            function getPincodes(city_id){
                
                $.post("{{ route('ajax.city_pincodes') }}",{_token:"{{ csrf_token() }}",city_id:city_id},function(data){
                   var area = $("#area_id");
                   area.empty();
                   area.append("option><option>");
                   $.map(data,function(item){
                   
                    area.append("<option value="+item.pincode+">"+item.pincode+"</option>");
                });
	            $("#area_id").select2();
                });
            }

            function setSortingHub(pincode){
                var city_name = $("#city_id option:selected").text();
                $.post("{{ route('ajax.set_sortinghubid') }}",{_token:"{{ csrf_token() }}",pincode:pincode,city_name:city_name},function(data){
                    if(data==1){
                        window.location.reload();
                    }
                });
            }

            function addToCart(cid){
                @if(!Cookie::has('sid'))
                    showFrontendAlert('danger','Select Your Delivery Location'); 
                    $(".quant_add_btn").show();
                    return false;
                @endif
                var qty = parseInt($("#pqty"+cid).val());
               
                var max_purchase_qty = parseInt($("#max_purchase_qty"+cid).val());
                if(qty>max_purchase_qty){
                    showFrontendAlert('danger','Maximum limit has been reached'); 
                    return false;
                }
                var data = $("#product_form"+cid).serializeArray();
                console.log(data);
                $.ajax({
                    type:"POST",
                    url: "{{ route('ajax.addtocart') }}",
                    data: data,
                    success: function(data){
                       if(data.status==true){
                        $(".item_in_cart").text(data.item_in_cart);
                        $("#pqty"+cid).attr('dcid',data.cart_id);
                        showFrontendAlert('success', 'Item Add to Cart');
                        loadNavcartItems();
                       }else{
                        showFrontendAlert('error', data.message);
                       }
                    }
                });
                
            }
             function buyNow(cid){
                @if(!Cookie::has('sid'))
                    showFrontendAlert('danger','Select Your Delivery Location'); 
                    $(".quant_add_btn").show();
                    return false;
                @endif
                var qty = parseInt($("#pqty"+cid).val())+1;
                var max_purchase_qty = parseInt($("#max_purchase_qty"+cid).val());
                
                if(max_purchase_qty<qty){
                    showFrontendAlert('danger','Maximum limit has been reached'); 
                    return false;
                }
                var data = $("#product_form"+cid).serializeArray();
                console.log(data);
                $.ajax({
                    type:"POST",
                    url: "{{ route('ajax.addtocart') }}",
                    data: data,
                    success: function(data){
                       if(data.status==true){
                        $(".item_in_cart").text(data.item_in_cart);
                        $("#pqty"+cid).attr('dcid',data.cart_id);
                        showFrontendAlert('success', 'Buy Now');
                        // loadNavcartItems();
                        window.location = 'https://ruraluat.rozana.in/new/cart';
                       }
                    }
                });
                
            }

            function updateCart(cid){
                var cart_id = $("#pqty"+cid).attr('dcid');
                var qty = parseInt($("#pqty"+cid).val());

                var max_purchase_qty = parseInt($("#max_purchase_qty"+cid).val());
                
                if(max_purchase_qty<qty){
                    showFrontendAlert('danger','Maximum limit has been reached'); 
                    return false;
                }
               
                if(qty<=0){
                    $("#btn_add"+cid).removeClass('display-none');
                    // remove product from cart
                    removeFromCart(cart_id);
                    loadNavcartItems();
                }else{
                    $.ajax({
                        type:"POST",
                        url: "{{ route('ajax.updatecart') }}",
                        data: {_token:"{{ csrf_token() }}",quantity:qty,cart_id:cart_id},
                        success: function(data){
                            console.log(data);
                            $(".item_in_cart").text(data.data.total_quantity);
                            loadNavcartItems();
                            if($("#pqty"+cid).hasAttr("page-cart")){
                                var total = (parseFloat($("#p-price"+cid).attr("p-price"+cid))*parseFloat(qty));
                                $("#total-price"+cid).html("₹"+total.toFixed(2));
                                loadCartSummary();
                            }
                        }
                    });
                    
                }
                
            }

            function removeFromCart(cart_id){
                $.ajax({
                    type:"POST",
                    url: "{{ route('ajax.removefromcart') }}",
                    data: {_token:"{{ csrf_token() }}",cart_id:cart_id},
                    success: function(data){
                        //console.log(data);
                        $(".item_in_cart").text(data.data.total_quantity);
                        
                        loadNavcartItems();
                        if($("#removebtn"+cart_id).hasAttr('page-cart')){
                            $("#row"+cart_id).remove();
                            showFrontendAlert("info","Your Item Remove");
                            loadCartSummary();
                            if($(".cart").length==0){
                            showFrontendAlert("info","Your cart empty");
                            window.location.href="{{ route('new.home') }}";
                        }
                        }
                        else{
                            $("input[dcid="+cart_id+"]").val(0);
                            $("input[dcid="+cart_id+"]").prev(".minus").prev('.plus').removeAttr('style').removeClass('display-none');
                                                        showFrontendAlert("info","Your Item Remove");


                        }
                        
                    }
                });
            }
            function applyPeerCode(){
                var peercode = $("#referal_code").val();
                var no_of_cart_items = parseInt($("#cart").val());
                $.post("{{ route('ajax.apply_peercode') }}",{_token:"{{ csrf_token() }}",peercode:peercode,cart:no_of_cart_items},function(data){
                    if(data==1){
                        showFrontendAlert('success','Peer code applied'); 
                        
                    }
                    else{
                        showFrontendAlert('danger','Invalid peer code'); 
                       
                    }
                    // window.location.reload();
                    
                });
            }

            function removePeerCode(){
                var peercode = "";
                var no_of_cart_items = parseInt($("#cart").val());
                $.post("{{ route('ajax.apply_peercode') }}",{_token:"{{ csrf_token() }}",peercode:peercode,cart:no_of_cart_items},function(data){
                    if(data==1){
                        document.cookie = "peer=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        showFrontendAlert('success','Peer code removed');
                    }
                    else{
                        showFrontendAlert('danger','Peer code not removed'); 
                       
                    }
                // window.location.reload();
            });
            }

        function showFrontendAlert(type, message){
            if(type == 'danger'){
                type = 'error';
            }
            swal({
                position: 'top-end',
                type: type,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
    }

    $('#referal_code').keypress(function (e) {
    if (e.which == 13) {
        $('#referal_btn').click();
        return false;    //<---- Add this line
    }
    });

    function load_categories(){
        $.ajax({
            url: '{{ route('ajax.categories.list') }}',
            type: 'post',
            data: {_token:'{{ csrf_token() }}'} ,
            cache:true,
            async:true,
            success:function(data){
                $('#categories').html(data);
                loadSubCategories();
            }
            });
    }
   
function loadSubCategories(){
    $('.category-nav-element').each(function(i, el) {
            $(el).on('mouseover', function(){
                if(!$(el).find('.sub-cat-menu').hasClass('loaded')){
                    $.post('{{ route('ajax.get_category_elements') }}', {_token: '{{ csrf_token()}}', id:$(el).data('id')}, function(data){
                        $(el).find('.sub-cat-menu').addClass('loaded').html(data);
                    });
                }
            });
        });
}

function loadMappedCities(){
    $.ajax({
            url: '{{ route('ajax.mapped_cities_list') }}',
            type: 'post',
            data: {_token:'{{ csrf_token() }}'} ,
            cache:true,
            async:true,
            success:function(data){
                $('#city_id').html(data);
            }
            });
}

  function plus(el){
    $(el).parent().find('.quant_add_btn').hide();
    var currentVal = parseInt($(el).parent().find('.qty').val());
    var max = parseInt($(el).parent().find('.qty').attr('max'));
    if(currentVal>=max){
        showFrontendAlert('danger','Maximum limit has been reached'); 
        return false;
    }else{
        $(el).parent().find('.qty').val(currentVal+1);
        $(el).parent().find('.cart_loader').show().delay(200).fadeOut(300);
    }
    
  }

  function minus(el){
    var currentVal = parseInt($(el).parent().find('.qty').val());
      $(el).parent().find('.qty').val(currentVal-1);
      $(el).parent().find('.cart_loader').show().delay(200).fadeOut(300);
     if(currentVal <= 1){
       $(el).parent().find('.quant_add_btn').show();
        $(el).parent().find('.cart_loader').show().delay(0).fadeOut(100);
     }
  }

  function loadNavCartCount(){
      var sessionID = getCookie("sessionID");
      $.ajax({
            url: '{{ route('ajax.loadnavcartcount') }}',
            type: 'post',
            data: {_token:"{{ csrf_token() }}",sessionID:sessionID,type:'refresh'} ,
            cache:true,
            async:true,
            success:function(data){
                $(".item_in_cart").html(data.no_of_items);
            }
            });
  }

  function loadNavcartItems(){
    var sessionID = getCookie("sessionID");
    $.ajax({
            url: '{{ route('ajax.loadnavcartitems') }}',
            type: 'post',
            data: {_token:"{{ csrf_token() }}",sessionID:sessionID} ,
            cache:true,
            async:true,
            success:function(data){
                $(".nav_cart").html(data);
            }
            });
  }

  $('#search').on('keyup', function(){
        search();
    });

    $('#search').on('focus', function(){
        search();
    });

    function search(){
        var search = $('#search').val();
        if(search.length > 0){
            $('body').addClass("typed-search-box-shown");

            $('.typed-search-box').removeClass('d-none');
            $('.search-preloader').removeClass('d-none');
            $.post('{{ route('phoneapi.elasticsearch-suggession') }}', { _token: '{{ @csrf_token() }}', search:search}, function(data){
                if(data == '0'){
                    // $('.typed-search-box').addClass('d-none');
                    $('#search-content').html(null);
                    $('.typed-search-box .search-nothing').removeClass('d-none').html('Sorry, nothing found for <strong>"'+search+'"</strong>');
                    $('.search-preloader').addClass('d-none');

                }
                else{
                    $('.typed-search-box .search-nothing').addClass('d-none').html(null);
                    $('#search-content').html(data);
                    $('.search-preloader').addClass('d-none');
                }
            });
        }
        else {
            $('.typed-search-box').addClass('d-none');
            $('body').removeClass("typed-search-box-shown");
        }
    }

    function loadList(el){
	var id =$(el).attr('id');
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
				$("#"+loadid).append("<option value="+item.block_id+">"+item.name+"</option>");
			}
			if(id=="state_id"){
				//console.log(item.name);
				$("#"+loadid).append("<option value="+item.city_id+">"+item.name+"</option>");
			}

			if(id=="block_id"){
				//console.log(item.name);
				$("#"+loadid).append("<option value="+item.pincode+">"+item.pincode+"</option>");
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
  
    $(document).ready(function() {
        load_categories();  
        loadMappedCities();
        loadNavCartCount();
        loadNavcartItems();
    });
    @if(Auth::check())
        @if(Auth::user()->is_old==1)
    $(window).on('load', function() {
        $('#wallet_modal_new').modal({
                        backdrop: 'static',
                        keyboard: true, 
                        show: true
                }); 
    });
        @endif
    @endif

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
        </script>

 
   </body>

</html>