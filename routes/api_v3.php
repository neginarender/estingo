<?php

Route::prefix('v3/auth')->group(function () {
    Route::post('login', 'Api\v3\AuthController@login');
    Route::post('loginwithOtp', 'Api\v3\AuthController@loginWithOtp');
    Route::post('verifyOtp', 'Api\v3\AuthController@verifyOtpUser');
    Route::post('signup', 'Api\v3\AuthController@signup');
    Route::post('social-login', 'Api\v3\AuthController@socialLogin');
    Route::post('password/create', 'Api\v3\PasswordResetController@create');
    Route::post('password/update', 'Api\v3\PasswordResetController@updatePassword');
    Route::post('password/change', 'Api\v3\PasswordResetController@changePassword');
    Route::post('password/changepassword','Api\v3\PasswordResetController@changePassApi')->middleware('auth:api');
    Route::post('password/verifyuser', 'Api\v3\PasswordResetController@verifyUser');
    Route::post('password/verifyotp', 'Api\v3\PasswordResetController@verifyOTP');
    Route::post('verifymail', 'Api\v3\AuthController@verifymail');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\v3\AuthController@logout');
        Route::get('user', 'Api\v3\AuthController@user');
    });
});

Route::group(['prefix' => 'v3','middleware' => 'localization'],function () {
	Route::post('/check_updates','Api\v3\AppController@checkUpdates');
	//06-11-2021 - new api for home page
    Route::get('homepage', 'Api\v3\HomePageController@index');

    //20-10-2021
    Route::get('getsortingid','Api\v3\HomePageController@getDistributorSortingID'); 

    Route::apiResource('banners', 'Api\v3\BannerController')->only('index');

    Route::get('brands/top', 'Api\v3\BrandController@top');
    Route::apiResource('brands', 'Api\v3\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\v3\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\v3\CategoryController@featured');
    Route::get('categories/home', 'Api\v3\CategoryController@home');
    Route::apiResource('categories', 'Api\v3\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\v3\SubCategoryController@index')->name('v3.subCategories.index');

    Route::apiResource('colors', 'Api\v3\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\v3\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\v3\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\v3\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\v3\HomeCategoryController')->only('index');

    Route::get('purchase-history/{id}', 'Api\v3\PurchaseHistoryController@index')->middleware('auth:api');
    Route::get('purchase-history-details/{id}', 'Api\v3\PurchaseHistoryDetailController@index')->name('v3.purchaseHistory.details')->middleware('auth:api');
    Route::get('order/history/{id}', 'Api\v3\PurchaseHistoryController@getOrders')->middleware('auth:api');
    Route::post('order/history/detail', 'Api\v3\PurchaseHistoryDetailController@getOrderDetails')->middleware('auth:api');

    Route::get('products/admin', 'Api\v3\ProductController@admin');
    Route::get('products/seller', 'Api\v3\ProductController@seller');
    Route::get('products/category/{id}', 'Api\v3\ProductController@category')->name('apiv3.products.category');
    Route::get('products/sub-category/{id}', 'Api\v3\ProductController@subCategory')->name('apiv3.products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\v3\ProductController@subSubCategory')->name('apiv3.products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\v3\ProductController@brand')->name('apiv3.products.brand');
    Route::get('products/todays-deal', 'Api\v3\ProductController@todaysDeal');
    Route::get('products/flash-deal', 'Api\v3\ProductController@flashDeal');
    Route::get('products/featured', 'Api\v3\ProductController@featured');
    Route::get('products/best-seller', 'Api\v3\ProductController@bestSeller');
    Route::get('products/related/{id}', 'Api\v3\ProductController@related')->name('apiv3.products.related');
    Route::get('products/top-from-seller/{id}', 'Api\v3\ProductController@topFromSeller')->name('products.topFromSeller');
    Route::post('products/search', 'Api\v3\ProductController@search')->name('products.search');
    Route::post('products/variant/price', 'Api\v3\ProductController@variantPrice');
    Route::get('products/home', 'Api\v3\ProductController@home');
    Route::post('products/peer_discount', 'Api\v3\ProductController@applyPartner');
    Route::get('products/remove_peercode', 'Api\v3\ProductController@removePeercode');
    
    Route::apiResource('products', 'Api\v3\ProductController')->except(['store', 'update', 'destroy']);

    Route::get('carts/{id}', 'Api\v3\CartController@index');
    Route::post('carts/add', 'Api\v3\CartController@add');
    Route::post('carts/change-quantity', 'Api\v3\CartController@changeQuantity');
    Route::apiResource('carts', 'Api\v3\CartController')->only('destroy');
    Route::get('carts/delete/{id}', 'Api\v3\CartController@deleteCart');
    Route::post('carts/apply_discount_on_cart','Api\v3\CartController@applyPeerDiscountCart');
    Route::post('carts/check_in_cart','Api\v3\CartController@checkInCart');
    Route::post('carts/check_availability','Api\v3\CartController@checkAvailablity');
    Route::post('carts/check_cart_price','Api\v3\CartController@checkCartPrice');
    Route::post('carts/apply_peer_on_checkout','Api\v3\CartController@applyPeerOnCheckout');
    Route::post('carts/itemno','Api\v3\CartController@getCartItemCount');
    Route::post('carts/checkpriceonpincode','Api\v3\CartController@checkPriceAfterShippingPinCode');


    Route::post('carts/getdeliveryslot','Api\v3\CartController@getdeliveryslot');

    Route::get('reviews/product/{id}', 'Api\v3\ReviewController@index')->name('apiv3.reviews.index');
    Route::post('store-review', 'Api\v3\ReviewController@store')->name('apiv3.reviews.store')->middleware('auth:api');
    Route::match(['GET', 'POST'], 'products/sorting/filter', 'Api\v3\ProductController@ProductSorting');

    Route::get('shop/user/{id}', 'Api\v3\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\v3\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\v3\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\v3\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\v3\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\v3\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\v3\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\v3\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\v3\SliderController')->only('index');
    Route::get('allcategory', 'Api\v3\ProductController@getAllCategory');

    Route::get('wishlists/{id}', 'Api\v3\WishlistController@index')->middleware('auth:api');
    Route::post('wishlists/check-product', 'Api\v3\WishlistController@isProductInWishlist')->middleware('auth:api');
    Route::apiResource('wishlists', 'Api\v3\WishlistController')->except(['index', 'update', 'show'])->middleware('auth:api');

    Route::apiResource('settings', 'Api\v3\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\v3\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\v3\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\v3\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\v3\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\v3\UserController@updateName')->middleware('auth:api');
    Route::post('user/peer/partner', 'Api\v3\UserController@createPeerPartner')->middleware('auth:api');
    Route::post('user/peer/check_referral', 'Api\v3\UserController@check_referral')->middleware('auth:api');
    Route::get('user/get_last_used_referral_code/{id}', 'Api\v3\UserController@getUserLastUsedPeerCode')->middleware('auth:api');
    
    Route::get('user/shipping/address/{id}', 'Api\v3\AddressController@addresses')->middleware('auth:api');
    Route::post('user/shipping/create', 'Api\v3\AddressController@createShippingAddress')->middleware('auth:api');
    Route::get('user/shipping/delete/{id}', 'Api\v3\AddressController@deleteShippingAddress')->middleware('auth:api');
    Route::post('user/shipping/update', 'Api\v3\AddressController@updateShippingAddress')->middleware('auth:api');
    //06-10-2021
    Route::post('user/shipping/setdefaultaddress', 'Api\v3\AddressController@setAddressDefault')->middleware('auth:api');

    Route::post('user/shipping/checkshippinglocation', 'Api\v3\AddressController@checkShippingLocation');

    Route::post('coupon/apply', 'Api\v3\CouponController@apply')->middleware('auth:api');

    Route::post('payments/pay/stripe', 'Api\v3\StripeController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/paypal', 'Api\v3\PaypalController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/wallet', 'Api\v3\WalletController@processPayment');
    Route::post('payments/pay/cod', 'Api\v3\PaymentController@cashOnDelivery');

    //24-11-2021
    Route::post('search/suggestion', 'Api\v3\SearchController@suggestion')->name('search.suggestion');
    Route::post('search/result','Api\v3\SearchController@searchList')->name('search.list');

    Route::post('deliveryslot', 'Api\v3\DeliveryslotController@index');

    //Order 
    Route::post('order/initiate', 'Api\v3\OrderController@orderinitiate');
    Route::post('order/store', 'Api\v3\OrderController@checkout_done');
    Route::post('order/cancel', 'Api\v3\OrderController@cancelOrder');
    Route::post('order/track','Api\v3\PurchaseHistoryDetailController@trackOrder');
    Route::post('order/suborder_track','Api\v3\PurchaseHistoryDetailController@trackSubOrder');
    Route::post('getproductattribute','Api\v3\ProductController@getProductAttribute');

    Route::get('wallet/balance/{id}', 'Api\v3\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\v3\WalletController@walletRechargeHistory')->middleware('auth:api');
    Route::post('wallet/recharge','Api\v3\WalletController@recharge')->middleware('auth:api');
    Route::post('wallet/rechargestore','Api\v3\WalletController@wallet_recharge_done')->middleware('auth:api');
    Route::get('/mapped_cities','Api\v3\HomeController@mapped_cities');
    Route::post('/city_pincode','Api\v3\HomeController@get_area_for_delivery');
    Route::get('/razorpay_key','Api\v3\HomeController@razorPayKey');

    //Recurring-order
    Route::post('order/recurring','Api\v3\RecurringController@orderRecurring')->middleware('auth:api');

    Route::get('order/recurringpayment','Api\v3\RecurringController@OrderRecurringPayment');

    Route::post('order/recurringonline','Api\v3\RecurringController@orderRecurringByOnlinePay')->middleware('auth:api');
    Route::post('order/recurringstore','Api\v3\RecurringController@recurringStore')->middleware('auth:api');
    Route::post('order/recurringlist','Api\v3\RecurringController@recurringList')->middleware('auth:api');
    Route::post('order/recurringprice','Api\v3\RecurringController@recurringPrice')->middleware('auth:api');
    Route::post('order/recurringorderlist','Api\v3\RecurringController@recurringOrderList')->middleware('auth:api');
    Route::post('order/recurringorderdetail','Api\v3\RecurringController@recurringOrderDetail')->middleware('auth:api');
    
    Route::post('order/unsubscribed','Api\v3\RecurringController@unsubscribed')->middleware('auth:api');
    Route::post('user/addressbyshortid', 'Api\v3\AddressController@addressListBySortinghub')->middleware('auth:api');

    Route::post('order/recurringordertest','Api\v3\RecurringController@OrderRecurringPayment')->middleware('auth:api');

    // Refund Request
    Route::get('refund_request/{id}', 'Api\v3\RefundRequestController@refund_request_send_page')->middleware('auth:api');
    Route::post('/refund_requests/store', 'Api\v3\RefundRequestController@refund_request_sends')->middleware('auth:api');

    //Replacement Request
    Route::get('replacement_request/{id}', 'Api\v3\ReplacementController@order_replacement')->middleware('auth:api');
    Route::post('/replacement_request/store', 'Api\v3\ReplacementController@storeReplacementRequest')->middleware('auth:api');
    Route::post('/upload-images','Api\v3\ReplacementController@uploadImages')->middleware('auth:api');

    //Conversation
    Route::post('/conversation','Api\v3\ConversationController@store')->middleware('auth:api');

    // Delivery Boy Routes
    Route::prefix('deliveryboy')->group(function(){
        Route::post('/orders','Api\v3\OrderController@deliveryBoyOrders')->middleware('auth:api');
        Route::get('/orderdetail/{id}','Api\v3\OrderController@orderDetail')->middleware('auth:api');
        Route::post('/order_status_update','Api\v3\OrderController@update_delivery_status')->middleware('auth:api');
        Route::get('/count_new_order/{id}','Api\v3\OrderController@count_new_order')->middleware('auth:api');
        Route::get('/update_new_order_status/{id}','Api\v3\OrderController@update_new_order_status')->middleware('auth:api');
        Route::get('/new_orders/{id}','Api\v3\OrderController@new_orders')->middleware('auth:api');
        Route::post('/orders_product_wise','Api\v3\OrderController@get_order_product_wise')->middleware('auth:api');
        Route::get('/replacement_orders/{id}','Api\v3\ReplacementController@deliveryBoyReplacementOrders')->middleware('auth:api');
        Route::get('/replacement_order_details/{id}','Api\v3\ReplacementController@replacementOrderDetail')->middleware('auth:api');
        Route::post('/update_replacement_status','Api\v3\ReplacementController@update_replacement_status')->middleware('auth:api');
        Route::post('/take_order','Api\v3\OrderController@takeOrder');
        Route::post('/call_to_customer','Api\v3\AddressController@callToCustomer');
        Route::post('/razorpay_payment_link','Api\v3\OrderController@razorpay_payment_link');
        Route::post('/razorpay_payment_link_webhook','Api\v3\DeliveryboyController@razorpayPaymentLinkWebHook');
        Route::post('/qr_code','Api\v3\DeliveryboyController@generateQRCode');
        Route::post('/razorpay_qr_code_webhook','Api\v3\DeliveryboyController@razorpayQrCodeWebHook');
        Route::post('/assign_orders','Api\v3\DeliveryboyController@assignedOrders');
        Route::post('/sub_order_details','Api\v3\DeliveryboyController@SubOrderDetail');
    });

    
    //sale accounting voucher api - for tally customization
    Route::post('/saletally','Api\v3\SaleTallyController@getOrderDetails')->name('saleTally');

    //language setting
    Route::post('language/setting','Api\v3\AddressController@languageSetting')->name('lanuage.setting');

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
