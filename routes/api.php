<?php

Route::prefix('v1/auth')->group(function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('loginwithOtp', 'Api\AuthController@loginWithOtp');
    Route::post('verifyOtp', 'Api\AuthController@verifyOtpUser');
    Route::post('signup', 'Api\AuthController@signup');
    Route::post('social-login', 'Api\AuthController@socialLogin');
    Route::post('password/create', 'Api\PasswordResetController@create');
    Route::post('password/update', 'Api\PasswordResetController@updatePassword');
    Route::post('password/change', 'Api\PasswordResetController@changePassword');
    Route::post('password/changepassword','Api\PasswordResetController@changePassApi')->middleware('auth:api');
    Route::post('password/verifyuser', 'Api\PasswordResetController@verifyUser');
    Route::post('password/verifyotp', 'Api\PasswordResetController@verifyOTP');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\AuthController@logout');
        Route::get('user', 'Api\AuthController@user');
    });
});

Route::prefix('v1')->group(function () {
	Route::post('/check_updates','Api\AppController@checkUpdates');
	//06-11-2021 - new api for home page
    Route::get('homepage', 'Api\HomePageController@index');
	Route::get('getsortingid','Api\HomePageController@getDistributorSortingID'); 
    Route::apiResource('banners', 'Api\BannerController')->only('index');

    Route::get('brands/top', 'Api\BrandController@top');
    Route::apiResource('brands', 'Api\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\CategoryController@featured');
    Route::get('categories/home', 'Api\CategoryController@home');
    Route::apiResource('categories', 'Api\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\SubCategoryController@index')->name('apiv1.subCategories.index');

    Route::apiResource('colors', 'Api\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\HomeCategoryController')->only('index');

    Route::get('purchase-history/{id}', 'Api\PurchaseHistoryController@index')->middleware('auth:api');
    Route::get('purchase-history-details/{id}', 'Api\PurchaseHistoryDetailController@index')->name('apiv1.purchaseHistory.details')->middleware('auth:api');
    Route::get('order/history/{id}', 'Api\PurchaseHistoryController@getOrders')->middleware('auth:api');
    Route::post('order/history/detail', 'Api\PurchaseHistoryDetailController@getOrderDetails')->middleware('auth:api');

    Route::get('products/admin', 'Api\ProductController@admin');
    Route::get('products/seller', 'Api\ProductController@seller');
    Route::get('products/category/{id}', 'Api\ProductController@category')->name('apiv1.products.category');
    Route::get('products/sub-category/{id}', 'Api\ProductController@subCategory')->name('apiv1.products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\ProductController@subSubCategory')->name('apiv1.products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'Api\ProductController@todaysDeal');
    Route::get('products/flash-deal', 'Api\ProductController@flashDeal');
    Route::get('products/featured', 'Api\ProductController@featured');
    Route::get('products/best-seller', 'Api\ProductController@bestSeller');
    Route::get('products/related/{id}', 'Api\ProductController@related')->name('apiv1.products.related');
    Route::get('products/top-from-seller/{id}', 'Api\ProductController@topFromSeller')->name('products.topFromSeller');
    Route::get('products/search', 'Api\ProductController@search')->name('products.search');
    Route::post('products/variant/price', 'Api\ProductController@variantPrice');
    Route::get('products/home', 'Api\ProductController@home');
    Route::post('products/peer_discount', 'Api\ProductController@applyPartner');
    Route::apiResource('products', 'Api\ProductController')->except(['store', 'update', 'destroy']);

    Route::get('carts/{id}', 'Api\CartController@index');
    Route::post('carts/add', 'Api\CartController@add');
    Route::post('carts/change-quantity', 'Api\CartController@changeQuantity');
    Route::apiResource('carts', 'Api\CartController')->only('destroy');
    Route::get('carts/delete/{id}', 'Api\CartController@deleteCart');
    Route::post('carts/apply_discount_on_cart','Api\CartController@applyPeerDiscountCart');
    Route::post('carts/check_in_cart','Api\CartController@checkInCart');
    Route::post('carts/check_availability','Api\CartController@checkAvailablity');
    Route::post('carts/check_cart_price','Api\CartController@checkCartPrice');
    Route::post('carts/apply_peer_on_checkout','Api\CartController@applyPeerOnCheckout');

    Route::get('reviews/product/{id}', 'Api\ReviewController@index')->name('apiv1.reviews.index');
    Route::post('store-review', 'Api\ReviewController@store')->name('api.reviews.store')->middleware('auth:api');
    
    Route::match(['GET', 'POST'], 'products/sorting/filter', 'Api\ProductController@ProductSorting');

    Route::get('shop/user/{id}', 'Api\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\SliderController')->only('index');
    Route::get('allcategory', 'Api\ProductController@getAllCategory');

    Route::get('wishlists/{id}', 'Api\WishlistController@index')->middleware('auth:api');
    Route::post('wishlists/check-product', 'Api\WishlistController@isProductInWishlist')->middleware('auth:api');
    Route::apiResource('wishlists', 'Api\WishlistController')->except(['index', 'update', 'show'])->middleware('auth:api');

    Route::apiResource('settings', 'Api\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\UserController@updateName')->middleware('auth:api');
    Route::post('user/peer/partner', 'Api\UserController@createPeerPartner')->middleware('auth:api');
    Route::post('user/peer/check_referral', 'Api\UserController@check_referral')->middleware('auth:api');
    Route::get('user/get_last_used_referral_code/{id}', 'Api\UserController@getUserLastUsedPeerCode')->middleware('auth:api');
    
    Route::get('user/shipping/address/{id}', 'Api\AddressController@addresses')->middleware('auth:api');
    Route::post('user/shipping/create', 'Api\AddressController@createShippingAddress')->middleware('auth:api');
    Route::get('user/shipping/delete/{id}', 'Api\AddressController@deleteShippingAddress')->middleware('auth:api');

    //06-10-2021
    Route::post('user/shipping/setdefaultaddress', 'Api\AddressController@setAddressDefault')->middleware('auth:api');

    Route::post('coupon/apply', 'Api\CouponController@apply')->middleware('auth:api');

    Route::post('payments/pay/stripe', 'Api\StripeController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/paypal', 'Api\PaypalController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/wallet', 'Api\WalletController@processPayment');
    Route::post('payments/pay/cod', 'Api\PaymentController@cashOnDelivery');

    //13-12-2021
    Route::post('search/suggestion', 'Api\SearchController@suggestion')->name('search.suggestion');
    Route::post('search/result','Api\SearchController@searchList')->name('search.list');

    //Order 
    Route::post('order/initiate', 'Api\OrderController@orderinitiate');
    Route::post('order/store', 'Api\OrderController@checkout_done');
    Route::post('order/cancel', 'Api\OrderController@cancelOrder');
    Route::post('order/track','Api\PurchaseHistoryDetailController@trackOrder');
    Route::get('getproductattribute','Api\ProductController@getProductAttribute');

    Route::get('wallet/balance/{id}', 'Api\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\WalletController@walletRechargeHistory')->middleware('auth:api');
    Route::get('/mapped_cities','Api\HomeController@mapped_cities');
    Route::post('/city_pincode','Api\HomeController@get_area_for_delivery');
    Route::get('/razorpay_key','Api\HomeController@razorPayKey');

    // Refund Request
    Route::get('refund_request/{id}', 'Api\RefundRequestController@refund_request_send_page')->middleware('auth:api');
    Route::post('/refund_requests/store', 'Api\RefundRequestController@refund_request_sends')->middleware('auth:api');

    //Replacement Request
    Route::get('replacement_request/{id}', 'Api\ReplacementController@order_replacement')->middleware('auth:api');
    Route::post('/replacement_request/store', 'Api\ReplacementController@storeReplacementRequest')->middleware('auth:api');
    Route::post('/upload-images','Api\ReplacementController@uploadImages')->middleware('auth:api');

    //Conversation
    Route::post('/conversation','Api\ConversationController@store')->middleware('auth:api');

    // Delivery Boy Routes
    
    // Route::post('deliveryboy/orders','Api\OrderController@deliveryBoyOrders')->middleware('auth:api');
    // Route::get('deliveryboy/orderdetail/{id}','Api\OrderController@orderDetail')->middleware('auth:api');
    // Route::post('deliveryboy/order_status_update','Api\OrderController@update_delivery_status')->middleware('auth:api');
    // Route::get('deliveryboy/count_new_order/{id}','Api\OrderController@count_new_order')->middleware('auth:api');
    // Route::get('deliveryboy/update_new_order_status/{id}','Api\OrderController@update_new_order_status')->middleware('auth:api');
    // Route::get('deliveryboy/new_orders/{id}','Api\OrderController@new_orders')->middleware('auth:api');
    // Route::post('deliveryboy/orders_product_wise','Api\OrderController@get_order_product_wise')->middleware('auth:api');
    // Route::get('deliveryboy/replacement_orders/{id}','Api\ReplacementController@deliveryBoyReplacementOrders')->middleware('auth:api');
    // Route::get('deliveryboy/replacement_order_details/{id}','Api\ReplacementController@replacementOrderDetail')->middleware('auth:api');
    // Route::post('deliveryboy/update_replacement_status','Api\ReplacementController@update_replacement_status')->middleware('auth:api');
    // Route::post('deliveryboy/take_order','Api\OrderController@takeOrder');
    Route::post('/call_to_customer','Api\AddressController@callToCustomer');
    Route::post('/razorpay_payment_link','Api\OrderController@razorpay_payment_link');
    // Route::post('deliveryboy/razorpay_payment_link_webhook','Api\DeliveryboyController@razorpayPaymentLinkWebHook');
    // Route::post('deliveryboy/qr_code','Api\DeliveryboyController@generateQRCode');
    // Route::post('deliveryboy/razorpay_qr_code_webhook','Api\DeliveryboyController@razorpayQrCodeWebHook');
    Route::prefix('deliveryboy')->group(function(){
        Route::post('/orders','Api\OrderController@deliveryBoyOrders')->middleware('auth:api');
        Route::get('/orderdetail/{id}','Api\OrderController@orderDetail')->middleware('auth:api');
        Route::post('/order_status_update','Api\OrderController@update_delivery_status')->middleware('auth:api');
        Route::get('/count_new_order/{id}','Api\OrderController@count_new_order')->middleware('auth:api');
        Route::get('/update_new_order_status/{id}','Api\OrderController@update_new_order_status')->middleware('auth:api');
        Route::get('/new_orders/{id}','Api\OrderController@new_orders')->middleware('auth:api');
        Route::post('/orders_product_wise','Api\OrderController@get_order_product_wise')->middleware('auth:api');
        Route::get('/replacement_orders/{id}','Api\ReplacementController@deliveryBoyReplacementOrders')->middleware('auth:api');
        Route::get('/replacement_order_details/{id}','Api\ReplacementController@replacementOrderDetail')->middleware('auth:api');
        Route::post('/update_replacement_status','Api\ReplacementController@update_replacement_status')->middleware('auth:api');
        Route::post('/take_order','Api\OrderController@takeOrder');
        Route::post('/call_to_customer','Api\AddressController@callToCustomer');
        Route::post('/razorpay_payment_link','Api\OrderController@razorpay_payment_link');
        Route::post('/razorpay_payment_link_webhook','Api\DeliveryboyController@razorpayPaymentLinkWebHook');
        Route::post('/qr_code','Api\DeliveryboyController@generateQRCode');
        Route::post('/razorpay_qr_code_webhook','Api\DeliveryboyController@razorpayQrCodeWebHook');
        Route::post('/assign_orders','Api\DeliveryboyController@assignedOrders');
        Route::post('/sub_order_details','Api\DeliveryboyController@SubOrderDetail');
        Route::post('/take_sub_order','Api\DeliveryboyController@takeSubOrder');
        Route::post('/update_sub_order_status','Api\DeliveryboyController@updateSubOrderStatus')->middleware('auth:api');
        Route::get('/notifications/{user_id}','Api\NotificationController@notificationList');
        Route::post('/notification/update_status','Api\NotificationController@updateNotificationStatus');
    });

});

Route::fallback(function() {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ]);
});
