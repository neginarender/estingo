<?php

Route::group(['prefix'=>'new'],function(){
    Route::get('/home','FrontEndController@index')->name('new.home');
    Route::post('/subcategeries','FrontEndController@getSubcategories')->name('ajax.subcategories');
    Route::post('/get_pincodes','FrontEndController@getPincodes')->name('ajax.city_pincodes');
    Route::post('/set_sortinghubid','FrontEndController@setSortingHubId')->name('ajax.set_sortinghubid');
    Route::post('/apply_peercode','FrontEndController@applyPeerDiscount')->name('ajax.apply_peercode');
    Route::post('/get_category_elements','FrontEndController@getCategoryElements')->name('ajax.get_category_elements');
    Route::get('/product/{id}','FrontEndController@productDetails')->name('product.details');
    Route::post('/categories_list','FrontEndController@categoryList')->name('ajax.categories.list');
    Route::post('mapped_cities','FrontEndController@mappedCities')->name('ajax.mapped_cities_list');
    Route::get('/category/{id}/{type}','FrontEndController@categoryProducts')->name('category.products');
    Route::post('/categorypage/{id}','FrontEndController@categoryProductspage')->name('category.productspage');

    Route::post('/addtocart','FrontEndController@addToCart')->name('ajax.addtocart');
    Route::post('/updatecart','FrontEndController@updateCart')->name('ajax.updatecart');
    Route::post('removefromcart','FrontEndController@removeFromCart')->name('ajax.removefromcart');
    Route::post('/loadnavcartcount','FrontEndController@cartItems')->name('ajax.loadnavcartcount');
    Route::post('/loadnavcartitems','FrontEndController@cartItems')->name('ajax.loadnavcartitems');
    Route::get('/cart','FrontEndController@cartDetails')->name('cart.details');
    Route::post('/cart_summary','FrontEndController@cartSummary')->name('ajax.cart_summary');
    Route::get('/login/{next}','FrontEndController@login')->name('userapi.login');
    Route::get('/register/{next}','FrontEndController@register')->name('userapi.register');
    Route::post('/userregister','FrontEndController@userregister')->name('userapi.userregister');

    Route::get('/userlogin/{next}','FrontEndController@userlogin')->name('userapi.userlogin');
    Route::post('/loginuser','FrontEndController@loginuser')->name('userapi.loginuser');
    Route::get('/resetpassword','FrontEndController@resetpassword')->name('userapi.resetpassword');
    Route::post('/resetpasswordemail','FrontEndController@resetpasswordemail')->name('userapi.resetpasswordemail');
    Route::post('/verifyemailotp','FrontEndController@verifyemailotp')->name('userapi.verifyemailotp');
    Route::post('/forgoypassword','FrontEndController@forgoyPassword')->name('userapi.forgoypassword');
   
    Route::get('/profile','FrontEndController@profile')->name('phoneapi.profile');
    Route::post('/address/delete', 'FrontEndController@addressdelete')->name('address.delete');
    Route::post('/update/email', 'FrontEndController@updateEmail')->name('phoneapi.updateemail');
    Route::post('/update/userinfo', 'FrontEndController@updateUserinfo')->name('phoneapi.updateuserinfo');
    Route::get('/dashboard', 'FrontEndController@dashboard')->name('phoneapi.dashboard');


    Route::post('/sendotp','FrontEndController@sendOTP')->name('phoneapi.sendotp');
    Route::post('/verifyotp','FrontEndController@verifyOTP')->name('phoneapi.verifyotp');
    Route::get('/logout','FrontEndController@logout')->name('phoneapi.logout');
    Route::get('/shipping_info','FrontEndController@shippingInfo')->name('phoneapi.shipping_info');
    Route::post('/delivery_info','FrontEndController@deliveryInfo')->name('phoneapi.delivery_info');
    Route::post('/payment_options','FrontEndController@paymentOption')->name('phoneapi.payment_options');
    Route::post('/generate_order','FrontEndController@genereateOrder')->name('phoneapi.generate_order');
    Route::get('/order_confirmation','FrontEndController@confirmOrder')->name('new.phoneapi.confirm_order');
    Route::get('/shippingadress','FrontEndController@addadress')->name('phoneapi.shippingadress');
    Route::post('/addshippingaddress','FrontEndController@addshippingaddress');
    Route::get('/purchaseorderdetail/{id}','NewOrderController@purchaseorderdetail');
    Route::get('/purchase_history','NewOrderController@purchase_history')->name('phoneapi.purchase_history');
    Route::get('/track_order','NewOrderController@track_order')->name('phoneapi.track_order');
    Route::post('/showtrack_order','NewOrderController@showtrack_order')->name('phoneapi.showtrack_order');
    
    Route::post('/add_review','FrontEndController@add_review')->name('phoneapi.add_review');



    Route::get('/wallet','FrontEndController@wallet')->name('phoneapi.wallet');
    Route::get('/userinfo','FrontEndController@userinfo')->name('phoneapi.userinfo');
    Route::get('/wishlists','NewOrderController@wishlists')->name('phoneapi.wishlists');
    Route::post('/wishlistsremove','NewOrderController@wishlistsremove')->name('phoneapi.wishlistsremove');
    Route::post('/addwishlists','NewOrderController@addwishlists')->name('phoneapi.addwishlists');
    Route::post('/razorpay-success','FrontEndController@paymentSuccess')->name('razorpay.payment-success');

    Route::get('/wallet','NewOrderController@myWallet')->name('phoneapi.wallet');
    Route::post('/walletrecharge','NewOrderController@walletRecharge')->name('phoneapi.walletrecharge');
    Route::post('/walletrecharge-success','NewOrderController@walletrechargeSuccess')->name('phoneapi.walletrecharge-success');

    Route::get('/support/ticket','NewOrderController@supportTicket')->name('phoneapi.supportticket');
    Route::get('/future/order','NewOrderController@futureOrder')->name('phoneapi.futureorder');
    Route::get('/cancel/order/{id}','NewOrderController@cancelOrder')->name('phoneapi.cancelorder');
    Route::post('/cancelorder','NewOrderController@cancelOrderid')->name('phoneapi.cancelorder');
    Route::get('/help/{id}','NewOrderController@help')->name('phoneapi.help');

    Route::post('/emailverify','NewOrderController@emailverify')->name('phoneapi.emailverify');
    Route::get('/search','FrontEndController@elasticSearch')->name('phoneapi.elasticsearch');
    Route::post('/search/suggession','FrontEndController@elasticSearchSuggession')->name('phoneapi.elasticsearch-suggession');
    Route::post('/updateapibycallcenter','FrontEndController@updateAddressByCallcenter')->name('phoneapi.updateaddressbycallcenter');

;});