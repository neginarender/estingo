<?php

Route::prefix('v5/auth')->group(function () {
    Route::post('login', 'Api\v5\AuthController@login');
    Route::post('loginwithOtp', 'Api\v5\AuthController@loginWithOtp');
    Route::post('verifyOtp', 'Api\v5\AuthController@verifyOtpUser');
    Route::post('signup', 'Api\v5\AuthController@signup');
    Route::post('social-login', 'Api\v5\AuthController@socialLogin');
    Route::post('password/create', 'Api\v5\PasswordResetController@create');
    Route::post('password/update', 'Api\v5\PasswordResetController@updatePassword');
    Route::post('password/change', 'Api\v5\PasswordResetController@changePassword');
    Route::post('password/changepassword','Api\v5\PasswordResetController@changePassApi')->middleware('auth:api');
    Route::post('password/verifyuser', 'Api\v5\PasswordResetController@verifyUser');
    Route::post('password/verifyotp', 'Api\v5\PasswordResetController@verifyOTP');
    Route::post('verifymail', 'Api\v5\AuthController@verifymail');
    Route::get('users/verify_reg/{id}', 'Api\v5\AuthController@verifyReg');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\v5\AuthController@logout');
        Route::get('user', 'Api\v5\AuthController@user');
    });
});

// Route::prefix('v5')->group(function () {
Route::group(['prefix' => 'v5','middleware' => 'localization'],function () {

	Route::post('/check_updates','Api\v5\AppController@checkUpdates');
	//06-11-2021 - new api for home page
    // Route::get('homepage', 'Api\v5\HomePageController@index')->middleware('apilocalization');
    Route::get('homepage', 'Api\v5\HomePageController@index');
    Route::get('statelist', 'Api\v5\HomePageController@stateList')->name('statelist');
    Route::get('citylist', 'Api\v5\HomePageController@cityList')->name('citylist');
    Route::get('blocklist', 'Api\v5\HomePageController@blockList')->name('blocklist');
    Route::get('pincodelist', 'Api\v5\HomePageController@pincodeList')->name('pincodelist');

    Route::post('dashboard/rozana','Api\v5\HomePageController@dashboardRozana')->middleware('auth:api');
    Route::post('dashboard/customer','Api\v5\HomePageController@dashboardCustomer')->middleware('auth:api');
    Route::post('dashboard/earning/detail','Api\v5\HomePageController@earningDetail')->middleware('auth:api');
    Route::post('dashboard/saving/detail','Api\v5\HomePageController@savingDetail')->middleware('auth:api');

    Route::get('customer/detail/{id}','Api\v5\PeerController@customerDetail')->middleware('auth:api');
    Route::get('peerlist','Api\v5\PeerController@Peerlist')->middleware('auth:api');

    Route::post('enrolledcustomer','Api\v5\UserController@enrolledCustomer')->middleware('auth:api');
    Route::get('enrollmentlist/{id}','Api\v5\UserController@enrolledCustomerlist')->middleware('auth:api');
    Route::get('enrolledcustomerview/{id}','Api\v5\UserController@enrolledcustomerview')->middleware('auth:api');
    Route::post('enrolledcustomerbyself','Api\v5\UserController@createUserBySelf');
    Route::post('updatecustomerbyself','Api\v5\UserController@updateUserBySelf');
    Route::get('lasfivedays','Api\v5\UserController@lasfivedays');
    Route::post('/updateaddress','Api\v5\UserController@updatePeerAddressByCallCenter')->name('updateaddress');

    Route::post('searchorderhistory','Api\v5\SearchController@searchOrderHistory')->middleware('auth:api');


    //Archived Order START
    Route::post('/archive/getorder','Api\v5\OrderArchiveController@getOrderHistory')->middleware('auth:api');
    Route::post('/archive/getOrderDetails','Api\v5\OrderArchiveController@getOrderDetailHistory')->middleware('auth:api');
    //Archived Order END

    // Route::post('orderlist','Api\OrderController@orderList')->middleware('auth:api');
    // Route::get('vieworder/{$id}', 'Api\OrderController@vieworder')->middleware('auth:api');

    //Transfer Wallet Money Start
    Route::post('wallet/add_account','Api\v5\WalletController@addFundAccound');
    Route::get('wallet/get_account/{user_id}','Api\v5\WalletController@getAccount');
    Route::post('wallet/withdraw_request','Api\v5\WalletController@payRequest');
    //Transfer Wallet Money End


    //20-10-2021
    Route::get('getsortingid','Api\v5\HomePageController@getDistributorSortingID'); 

    Route::apiResource('banners', 'Api\v5\BannerController')->only('index');

    Route::get('brands/top', 'Api\v5\BrandController@top');
    Route::apiResource('brands', 'Api\v5\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\v5\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\v5\CategoryController@featured');
    Route::get('categories/home', 'Api\v5\CategoryController@home');
    Route::apiResource('categories', 'Api\v5\CategoryController')->only('index')->middleware('apilocalization');
    Route::get('sub-categories/{id}', 'Api\v5\SubCategoryController@index')->name('apiv5.subCategories.index')->middleware('apilocalization');

    Route::apiResource('colors', 'Api\v5\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\v5\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\v5\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\v5\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\v5\HomeCategoryController')->only('index');

    Route::get('purchase-history/{id}', 'Api\v5\PurchaseHistoryController@index')->middleware('auth:api');
    Route::get('purchase-history-details/{id}', 'Api\v5\PurchaseHistoryDetailController@index')->middleware('auth:api');

    Route::get('order/history/{id}', 'Api\v5\PurchaseHistoryController@getOrders')->middleware('auth:api');
    Route::get('order/test/history/{id}', 'Api\v5\PurchaseHistoryController@getOrdersTest')->middleware('auth:api');
    Route::get('order/history/tocustomer/{id}', 'Api\v5\PurchaseHistoryController@getOrdersToCustomer')->middleware('auth:api');
    Route::get('order/history/bycustomer/{id}', 'Api\v5\PurchaseHistoryController@getOrdersByCustomer')->middleware('auth:api');
    Route::post('order/history/detail', 'Api\v5\PurchaseHistoryDetailController@getOrderDetails')->middleware('auth:api')->middleware('apilocalization');

    Route::get('products/admin', 'Api\v5\ProductController@admin');
    Route::get('products/seller', 'Api\v5\ProductController@seller');
    Route::get('products/category/{id}', 'Api\v5\ProductController@category')->name('apiv5.products.category');
    Route::get('products/sub-category/{id}', 'Api\v5\ProductController@subCategory')->name('apiv5.products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\v5\ProductController@subSubCategory')->name('apiv5.products.subSubCategory')->middleware('apilocalization');
    Route::get('products/brand/{id}', 'Api\v5\ProductController@brand')->name('apiv5.products.brand');
    Route::get('products/todays-deal', 'Api\v5\ProductController@todaysDeal');
    Route::get('products/flash-deal', 'Api\v5\ProductController@flashDeal');
    Route::get('products/featured', 'Api\v5\ProductController@featured');
    Route::get('products/best-seller', 'Api\v5\ProductController@bestSeller');
    Route::get('products/related/{id}', 'Api\v5\ProductController@related')->name('apiv5.products.related');
    Route::get('products/top-from-seller/{id}', 'Api\v5\ProductController@topFromSeller')->name('apiv5.products.topFromSeller');
    Route::post('products/search', 'Api\v5\ProductController@search')->name('products.search');
    Route::post('products/variant/price', 'Api\v5\ProductController@variantPrice');
    Route::get('products/home', 'Api\v5\ProductController@home');
    Route::post('products/peer_discount', 'Api\v5\ProductController@applyPartner');
    Route::get('products/remove_peercode', 'Api\v5\ProductController@removePeercode');
    Route::post('/search-universal','Api\v5\SearchController@searchInFinalProducts')->middleware('apilocalization');;

    Route::post('/get_peer_address','Api\v5\UserController@getPeerAddress');
    Route::apiResource('products', 'Api\v5\ProductController')->except(['store', 'update', 'destroy'])->middleware('apilocalization');

    Route::get('carts/{id}', 'Api\v5\CartController@index')->middleware('apilocalization');
    Route::post('carts/add', 'Api\v5\CartController@add');
    Route::post('carts/change-quantity', 'Api\v5\CartController@changeQuantity');
    Route::apiResource('carts', 'Api\v5\CartController')->only('destroy');
    Route::get('carts/delete/{id}', 'Api\v5\CartController@deleteCart');
    Route::post('carts/apply_discount_on_cart','Api\v5\CartController@applyPeerDiscountCart');
    Route::post('carts/check_in_cart','Api\v5\CartController@checkInCart');
    Route::post('carts/check_availability','Api\v5\CartController@checkAvailablity')->middleware('apilocalization');
    Route::post('carts/check_cart_price','Api\v5\CartController@checkCartPrice');
    Route::post('carts/apply_peer_on_checkout','Api\v5\CartController@applyPeerOnCheckout');
    Route::post('carts/itemno','Api\v5\CartController@getCartItemCount');
    Route::post('carts/checkpriceonpincode','Api\v5\CartController@checkPriceAfterShippingPinCode');


    Route::post('carts/getdeliveryslot','Api\v5\CartController@getdeliveryslot');

    Route::get('reviews/product/{id}', 'Api\v5\ReviewController@index')->name('apiv5.reviews.index');
    Route::post('store-review', 'Api\v5\ReviewController@store')->name('apiv5.reviews.store')->middleware('auth:api');
    Route::match(['GET', 'POST'], 'products/sorting/filter', 'Api\v5\ProductController@ProductSorting')->middleware('apilocalization');

    Route::get('shop/user/{id}', 'Api\v5\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\v5\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\v5\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\v5\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\v5\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\v5\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\v5\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\v5\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\v5\SliderController')->only('index')->middleware('apilocalization');
    Route::get('allcategory', 'Api\v5\ProductController@getAllCategory')->middleware('apilocalization');
    //Route::post('allcategory', 'Api\ProductController@getAllCategory')->middleware('apilocalization');
    Route::post('homecategory', 'Api\v5\ProductController@homePageCategory')->middleware('apilocalization');

    Route::get('wishlists/{id}', 'Api\v5\WishlistController@index')->middleware('auth:api')->middleware('apilocalization');
    Route::post('wishlists/check-product', 'Api\v5\WishlistController@isProductInWishlist')->middleware('auth:api');
    Route::apiResource('wishlists', 'Api\v5\WishlistController')->except(['index', 'update', 'show'])->middleware('auth:api');

    Route::apiResource('settings', 'Api\v5\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\v5\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\v5\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\v5\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\v5\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\v5\UserController@updateName')->middleware('auth:api');
    Route::post('user/peer/partner', 'Api\v5\UserController@createPeerPartner');
    Route::post('user/peer/update_partner', 'Api\v5\UserController@updatePeerPartner');
    Route::post('user/peer/check_referral', 'Api\v5\UserController@check_referral');
    Route::get('user/get_last_used_referral_code/{id}', 'Api\v5\UserController@getUserLastUsedPeerCode')->middleware('auth:api');
    
    Route::get('user/shipping/address/{id}', 'Api\v5\AddressController@addresses')->middleware('auth:api');
    Route::post('user/shipping/create', 'Api\v5\AddressController@createShippingAddress')->middleware('auth:api');
    Route::get('user/shipping/delete/{id}', 'Api\v5\AddressController@deleteShippingAddress')->middleware('auth:api');
    Route::post('user/shipping/update', 'Api\v5\AddressController@updateShippingAddress')->middleware('auth:api');
    //06-10-2021
    Route::post('user/shipping/setdefaultaddress', 'Api\v5\AddressController@setAddressDefault')->middleware('auth:api');

    Route::post('user/shipping/checkshippinglocation', 'Api\v5\AddressController@checkShippingLocation');

    Route::post('coupon/apply', 'Api\v5\CouponController@apply')->middleware('auth:api');

    Route::post('payments/pay/stripe', 'Api\v5\StripeController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/paypal', 'Api\v5\PaypalController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/wallet', 'Api\v5\WalletController@processPayment')->middleware('apilocalization');
    Route::post('payments/pay/cod', 'Api\v5\PaymentController@cashOnDelivery')->middleware('apilocalization');

    //24-11-2021
    Route::post('search/suggestion', 'Api\v5\SearchController@suggestion')->name('search.suggestion');
    Route::post('search/result','Api\v5\SearchController@searchList')->name('search.list')->middleware('apilocalization');

    Route::post('deliveryslot', 'Api\v5\DeliveryslotController@index')->middleware('auth:api');

    //Order 
    Route::post('order/initiate', 'Api\v5\OrderController@orderinitiate');
    Route::post('order/store', 'Api\v5\OrderController@checkout_done')->middleware('apilocalization');
    Route::post('order/cancel', 'Api\v5\OrderController@cancelOrder');
    Route::post('order/track','Api\v5\PurchaseHistoryDetailController@trackOrder');
    
    Route::post('order/suborder_track','Api\v5\PurchaseHistoryDetailController@trackSubOrder')->middleware('apilocalization');

    Route::post('order/updatestatus','Api\v5\OrderController@updateOrderStatus')->middleware('auth:api');

    Route::post('getproductattribute','Api\v5\ProductController@getProductAttribute')->middleware('apilocalization');

    Route::get('wallet/balance/{id}', 'Api\v5\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\v5\WalletController@walletRechargeHistory')->middleware('auth:api');
    Route::post('wallet/recharge','Api\v5\WalletController@recharge')->middleware('auth:api');
    Route::post('wallet/rechargestore','Api\v5\WalletController@wallet_recharge_done')->middleware('auth:api');
    Route::post('wallet/refund','Api\v5\OrderController@walletRefund')->middleware('auth:api');
    Route::get('/mapped_cities','Api\v5\HomeController@mapped_cities');
    Route::post('/city_pincode','Api\v5\HomeController@get_area_for_delivery');
    Route::get('/razorpay_key','Api\v5\HomeController@razorPayKey');

    // Refund Request
    Route::get('refund_request/{id}', 'Api\v5\RefundRequestController@refund_request_send_page')->middleware('auth:api');
    Route::post('/refund_requests/store', 'Api\v5\RefundRequestController@refund_request_sends')->middleware('auth:api');

    //Replacement Request
    Route::get('replacement_request/{id}', 'Api\v5\ReplacementController@order_replacement')->middleware('auth:api');
    Route::post('/replacement_request/store', 'Api\v5\ReplacementController@storeReplacementRequest')->middleware('auth:api');
    Route::post('/upload-images','Api\v5\ReplacementController@uploadImages')->middleware('auth:api');

    //Conversation
    Route::post('/conversation','Api\v5\ConversationController@store')->middleware('auth:api');
    Route::post('/get_zone','Api\v5\HomeController@check_pinall');
    Route::get('/notifications/{user_id}','Api\v5\NotificationController@notificationList');
    Route::post('/notification/update_status','Api\v5\NotificationController@updateNotificationStatus');
    // Delivery Boy Routes
    Route::prefix('deliveryboy')->group(function(){
        Route::post('/orders','Api\v5\OrderController@deliveryBoyOrders')->middleware('auth:api');
        Route::get('/orderdetail/{id}','Api\v5\OrderController@orderDetail')->middleware('auth:api');
        Route::post('/order_status_update','Api\v5\OrderController@update_delivery_status')->middleware('auth:api');
        Route::get('/count_new_order/{id}','Api\v5\OrderController@count_new_order')->middleware('auth:api');
        Route::get('/update_new_order_status/{id}','Api\v5\OrderController@update_new_order_status')->middleware('auth:api');
        Route::get('/new_orders/{id}','Api\v5\OrderController@new_orders')->middleware('auth:api');
        Route::post('/orders_product_wise','Api\v5\OrderController@get_order_product_wise')->middleware('auth:api');
        Route::get('/replacement_orders/{id}','Api\v5\ReplacementController@deliveryBoyReplacementOrders')->middleware('auth:api');
        Route::get('/replacement_order_details/{id}','Api\v5\ReplacementController@replacementOrderDetail')->middleware('auth:api');
        Route::post('/update_replacement_status','Api\v5\ReplacementController@update_replacement_status')->middleware('auth:api');
        Route::post('/take_order','Api\v5\OrderController@takeOrder');
        Route::post('/call_to_customer','Api\v5\AddressController@callToCustomer');
        Route::post('/razorpay_payment_link','Api\v5\OrderController@razorpay_payment_link');
        Route::post('/razorpay_payment_link_webhook','Api\v5\DeliveryboyController@razorpayPaymentLinkWebHook');
        Route::post('/qr_code','Api\v5\DeliveryboyController@generateQRCode');
        Route::post('/razorpay_qr_code_webhook','Api\v5\DeliveryboyController@razorpayQrCodeWebHook');
        Route::post('/assign_orders','Api\v5\DeliveryboyController@assignedOrders');
        Route::post('/sub_order_details','Api\v5\DeliveryboyController@SubOrderDetail');
    });

    
    //sale accounting voucher api - for tally customization
    Route::post('/saletally','Api\v5\SaleTallyController@getOrderDetails')->name('saleTally');
     //language setting
     Route::post('language/setting','Api\v5\AddressController@languageSetting')->name('lanuage.setting');

});


Route::post('login-neodove-agent','WatiController@neodoveAgenetLogin')->middleware('neodove');
Route::post('get-neodove-user','WatiController@getUserWithMobile')->middleware('auth:api');

Route::fallback(function() {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ]);
});
