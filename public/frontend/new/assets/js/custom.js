 $(document).ready(function(){
     $('#loader').fadeOut('slow');
    $('.single-item').slick();
    $('.demo-select2').select2();
    $(".pincode").hide();
    $(".ovrlay-landing").hide();
    $( "#datepicker" ).datepicker();
 });
  $(".loc").click(function(){
        $(".pincode").show();
        $(".ovrlay-landing").show();
    });
 $(".ovrlay-landing").click(function() {
    $(".pincode").hide();
    $(".ovrlay-landing").hide();
});
  $('.loc').click(function() {
    $('.location-landing').removeClass('hide');
    $('.location-landing').addClass('show');
    $('.ovrlay-landing').fadeIn();
});

  // header search bar
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
        }
        else {
            $('.typed-search-box').addClass('d-none');
            $('body').removeClass("typed-search-box-shown");
        }
    }

 
  // $('.quantity .plus').click(function(){
  //   $(this).parent().find('.quant_add_btn').hide();
    
   
  //   var currentVal = parseInt($(this).parent().find('.qty').val());
  //    $(this).parent().find('.qty').val(currentVal+1);
  //     $(this).parent().find('.cart_loader').show().delay(200).fadeOut(300);
  // });

  //  $('.quantity .minus').click(function(){
  //   var currentVal = parseInt($(this).parent().find('.qty').val());
  //     $(this).parent().find('.qty').val(currentVal-1);
  //     $(this).parent().find('.cart_loader').show().delay(200).fadeOut(300);
  //    if(currentVal <= 1){
  //      $(this).parent().find('.quant_add_btn').show();
  //       $(this).parent().find('.cart_loader').show().delay(0).fadeOut(100);
  //    }
  // });

   // show/hide password
     $('.pass a').click(function(){
        $(this).find('i').toggleClass("fa-eye-slash");
        var input = $(this).parent().find('input');
         if (input.attr("type") === "password") { 
            input.attr("type", "text");
          } 
          else   {
            input.attr("type", "password");
          }  
        // $('.overly').fadeIn();
     });

    // range slider
    $( function() {
      $( "#slider-range" ).slider({
        range: true,
        min: 0,
        max: 4000,
        values: [ 4, 4000 ],
        slide: function( event, ui ) {
          $( "#input-slider-range-value-low" ).text(   ui.values[ 0 ] + ".00" );
          $( "#input-slider-range-value-high" ).text(   ui.values[ 1 ] + ".00" );
        }
      });
      $( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
        " - $" + $( "#slider-range" ).slider( "values", 1 ) );
   });

    // cart poge select all check box
    function selectAll(obj){
      if($(obj).prop('checked') == true){
      $('.selectedId').prop('checked', 'checked');
         
      }
      else{
          $('.selectedId').removeAttr('checked');
      }
      if($('.selectedId').filter(":checked").length>0){
              $(".remove_btn").show();
          }
          else{
              $(".remove_btn").hide();
          }
      
    }

    function singleCheck(){
      var check = ($('.selectedId').filter(":checked").length == $('.selectedId').length);
          $('#selectall').prop("checked", check);
          if($('.selectedId').filter(":checked").length>0){
              $(".remove_btn").show();
          }
          else{
              $(".remove_btn").hide();
          }
    }

    // coupn code
    $(document).on('click','#click_here, .coupon_box .btn',function(){
      
      $('#code_box').toggle();
    });

     function showCheckoutModal(){
        $('#GuestCheckout').modal();
    }

    // slotted delivery
    $('#slotted_delivery').click(function(){
      $('#deliveryDateTime').show();
    })
     $('#normal_delivery').click(function(){
      $('#deliveryDateTime').hide();
    });

     // edit cart quantity
     $('.p-btns .edit').click(function(){
        $(this).parent().parent().find('.updateQty').show();
     });

      $('.custom-button').click(function(){
        $(this).parent().hide();
     });

      // order_details modal

      function order_details(){
        $('#order_details').modal('show');
      }

      // wallet modal
      function show_wallet_modal(){
         $('#wallet_modal').modal('show');
      }

      // verify email
      $('.new-email-verification').on('click', function() {
         $(this).find('.loading').removeClass('d-none');
         $(this).find('.default').addClass('d-none');
      });
     
     // sweetalert 
     function updated(){
       Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Updated',
        showConfirmButton: false,
        timer: 50000
      });
    }

    function error(){
       Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: 'oops..',
        text: 'Something went wrong!',
        showConfirmButton: false,
        timer: 50000
      });
    }

    // track order
    function trackOrders(){
      $('#order-detail').show()
    }
    
    // become a partner
    $('#fbradio1').click(function() {
      if($('#fbradio1').is(':checked')) 
       { 
           $("#fb_tab").show();
           $("#fb_tab").find("input").prop('required',true);
       }
   });
   $('#fbradio2').click(function() {
      if($('#fbradio2').is(':checked')) 
       { 
           $("#fb_tab").hide();
           $("#fb_tab").find("input").val("");
           $("#fb_tab").find("input").prop('required',false);
       }
   });

   // Instagram

   $('#instaradio1').click(function() {
      if($('#instaradio1').is(':checked')) 
       { 
           $("#insta_tab").show();
           $("#insta_tab").find("input").prop('required',true);
       }
   });
   $('#instaradio2').click(function() {
      if($('#instaradio2').is(':checked')) 
       { 
           $("#insta_tab").hide();
           $("#fb_tab").find("input").val("");
           $("#insta_tab").find("input").prop('required',false);
       }
   });

   // Linkidin
   $('#linkedinradio1').click(function() {
      if($('#linkedinradio1').is(':checked')) 
       { 
           $("#linkedin_tab").show();
           $("#linkedin_tab").find("input").prop('required',true);
       }
   });
    $('#linkedinradio2').click(function() {
      if($('#linkedinradio2').is(':checked')) 
       { 
           $("#linkedin_tab").hide();
           $("#linkedin_tab").find("input").val("");
           $("#linkedin_tab").find("input").prop('required',false);
       }
   });

  //  subscribe order modal
  function subscribeModal(){
    $('#subscribe').modal('show')
  }
//  chat modal bulk order
function show_chat_modal(){
  $('#chat_modal').modal('show');
}

function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

$.fn.hasAttr = function(name) {  
  return this.attr(name) !== undefined;
};




   
