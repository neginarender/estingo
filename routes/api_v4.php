<?php

Route::prefix('v4/auth')->group(function () {
    Route::post('login', 'Api\v4\AuthController@login');
    Route::post('loginwithOtp', 'Api\v4\AuthController@loginWithOtp');
    Route::post('verifyOtp', 'Api\v4\AuthController@verifyOtpUser');
    Route::post('signup', 'Api\v4\AuthController@signup');
    Route::post('social-login', 'Api\v4\AuthController@socialLogin');
    Route::post('password/create', 'Api\v4\PasswordResetController@create');
    Route::post('password/update', 'Api\v4\PasswordResetController@updatePassword');
    Route::post('password/change', 'Api\v4\PasswordResetController@changePassword');
    Route::post('password/changepassword','Api\v4\PasswordResetController@changePassApi')->middleware('auth:api');
    Route::post('password/verifyuser', 'Api\v4\PasswordResetController@verifyUser');
    Route::post('password/verifyotp', 'Api\v4\PasswordResetController@verifyOTP');
    Route::post('verifymail', 'Api\v4\AuthController@verifymail');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\v4\AuthController@logout');
        Route::get('user', 'Api\v4\AuthController@user');
    });
});

Route::group(['prefix' => 'v4','middleware' => 'localization'],function () {
	Route::post('/check_updates','Api\v4\AppController@checkUpdates');
	//06-11-2021 - new api for home page
    Route::get('homepage', 'Api\v4\HomePageController@index');

    //20-10-2021
    Route::get('getsortingid','Api\v4\HomePageController@getDistributorSortingID'); 

    Route::apiResource('banners', 'Api\v4\BannerController')->only('index');

    Route::get('brands/top', 'Api\v4\BrandController@top');
    Route::apiResource('brands', 'Api\v4\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\v4\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\v4\CategoryController@featured');
    Route::get('categories/home', 'Api\v4\CategoryController@home');
    Route::apiResource('categories', 'Api\v4\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\v4\SubCategoryController@index')->name('subCategories.index');

    Route::apiResource('colors', 'Api\v4\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\v4\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\v4\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\v4\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\v4\HomeCategoryController')->only('index');

    Route::get('purchase-history/{id}', 'Api\v4\PurchaseHistoryController@index')->middleware('auth:api');
    Route::get('purchase-history-details/{id}', 'Api\v4\PurchaseHistoryDetailController@index')->name('purchaseHistory.details')->middleware('auth:api');
    Route::get('order/history/{id}', 'Api\v4\PurchaseHistoryController@getOrders')->middleware('auth:api');
    Route::post('order/history/detail', 'Api\v4\PurchaseHistoryDetailController@getOrderDetails')->middleware('auth:api');

    //Archived Order START
    Route::post('/archive/getorder','Api\v4\OrderArchiveController@getOrderHistory')->middleware('auth:api');
    Route::post('/archive/getOrderDetails','Api\v4\OrderArchiveController@getOrderDetailHistory')->middleware('auth:api');
    //Archived Order END

    Route::get('products/admin', 'Api\v4\ProductController@admin');
    Route::get('products/seller', 'Api\v4\ProductController@seller');
    Route::get('products/category/{id}', 'Api\v4\ProductController@category')->name('api.products.category');
    Route::get('products/sub-category/{id}', 'Api\v4\ProductController@subCategory')->name('products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\v4\ProductController@subSubCategory')->name('products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\v4\ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'Api\v4\ProductController@todaysDeal');
    Route::get('products/flash-deal', 'Api\v4\ProductController@flashDeal');
    Route::get('products/featured', 'Api\v4\ProductController@featured');
    Route::get('products/best-seller', 'Api\v4\ProductController@bestSeller');
    Route::get('products/related/{id}', 'Api\v4\ProductController@related')->name('products.related');
    Route::get('products/top-from-seller/{id}', 'Api\v4\ProductController@topFromSeller')->name('products.topFromSeller');
    Route::post('products/search', 'Api\v4\ProductController@search')->name('products.search');
    Route::post('products/variant/price', 'Api\v4\ProductController@variantPrice');
    Route::get('products/home', 'Api\v4\ProductController@home');
    Route::post('products/peer_discount', 'Api\v4\ProductController@applyPartner');
    Route::get('products/remove_peercode', 'Api\v4\ProductController@removePeercode');
    
    Route::apiResource('products', 'Api\v4\ProductController')->except(['store', 'update', 'destroy']);

    Route::get('carts/{id}', 'Api\v4\CartController@index');
    Route::post('carts/add', 'Api\v4\CartController@add');
    Route::post('carts/change-quantity', 'Api\v4\CartController@changeQuantity');
    Route::apiResource('carts', 'Api\v4\CartController')->only('destroy');
    Route::get('carts/delete/{id}', 'Api\v4\CartController@deleteCart');
    Route::post('carts/apply_discount_on_cart','Api\v4\CartController@applyPeerDiscountCart');
    Route::post('carts/check_in_cart','Api\v4\CartController@checkInCart');
    Route::post('carts/check_availability','Api\v4\CartController@checkAvailablity');
    Route::post('carts/check_cart_price','Api\v4\CartController@checkCartPrice');
    Route::post('carts/apply_peer_on_checkout','Api\v4\CartController@applyPeerOnCheckout');
    Route::post('carts/itemno','Api\v4\CartController@getCartItemCount');
    Route::post('carts/checkpriceonpincode','Api\v4\CartController@checkPriceAfterShippingPinCode');


    Route::post('carts/getdeliveryslot','Api\v4\CartController@getdeliveryslot');

    Route::get('reviews/product/{id}', 'Api\v4\ReviewController@index')->name('api.reviews.index');
    Route::post('store-review', 'Api\v4\ReviewController@store')->name('api.reviews.store')->middleware('auth:api');
    Route::match(['GET', 'POST'], 'products/sorting/filter', 'Api\v4\ProductController@ProductSorting');

    Route::get('shop/user/{id}', 'Api\v4\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\v4\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\v4\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\v4\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\v4\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\v4\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\v4\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\v4\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\v4\SliderController')->only('index');
    Route::get('allcategory', 'Api\v4\ProductController@getAllCategory');

    Route::get('wishlists/{id}', 'Api\v4\WishlistController@index')->middleware('auth:api');
    Route::post('wishlists/check-product', 'Api\v4\WishlistController@isProductInWishlist')->middleware('auth:api');
    Route::apiResource('wishlists', 'Api\v4\WishlistController')->except(['index', 'update', 'show'])->middleware('auth:api');

    Route::apiResource('settings', 'Api\v4\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\v4\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\v4\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\v4\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\v4\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\v4\UserController@updateName')->middleware('auth:api');
    Route::post('user/peer/partner', 'Api\v4\UserController@createPeerPartner')->middleware('auth:api');
    Route::post('user/peer/check_referral', 'Api\v4\UserController@check_referral')->middleware('auth:api');
    Route::get('user/get_last_used_referral_code/{id}', 'Api\v4\UserController@getUserLastUsedPeerCode')->middleware('auth:api');
    
    Route::get('user/shipping/address/{id}', 'Api\v4\AddressController@addresses')->middleware('auth:api');
    Route::post('user/shipping/create', 'Api\v4\AddressController@createShippingAddress')->middleware('auth:api');
    Route::get('user/shipping/delete/{id}', 'Api\v4\AddressController@deleteShippingAddress')->middleware('auth:api');
    Route::post('user/shipping/update', 'Api\v4\AddressController@updateShippingAddress')->middleware('auth:api');
    //06-10-2021
    Route::post('user/shipping/setdefaultaddress', 'Api\v4\AddressController@setAddressDefault')->middleware('auth:api');

    Route::post('user/shipping/checkshippinglocation', 'Api\v4\AddressController@checkShippingLocation');

    Route::post('coupon/apply', 'Api\v4\CouponController@apply')->middleware('auth:api');

    Route::post('payments/pay/stripe', 'Api\v4\StripeController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/paypal', 'Api\v4\PaypalController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/wallet', 'Api\v4\WalletController@processPayment');
    Route::post('payments/pay/cod', 'Api\v4\PaymentController@cashOnDelivery');

    //24-11-2021
    Route::post('search/suggestion', 'Api\v4\SearchController@suggestion')->name('search.suggestion');
    Route::post('search/result','Api\v4\SearchController@searchList')->name('search.list');

    Route::post('deliveryslot', 'Api\v4\DeliveryslotController@index');

    //Order 
    Route::post('order/initiate', 'Api\v4\OrderController@orderinitiate');
    Route::post('order/store', 'Api\v4\OrderController@checkout_done');
    Route::post('order/cancel', 'Api\v4\OrderController@cancelOrder');
    Route::post('wallet/refund','Api\v4\OrderController@walletRefund')->middleware('auth:api');
    
    Route::post('order/track','Api\v4\PurchaseHistoryDetailController@trackOrder');
    Route::post('order/suborder_track','Api\v4\PurchaseHistoryDetailController@trackSubOrder');
    Route::post('getproductattribute','Api\v4\ProductController@getProductAttribute');

    Route::get('wallet/balance/{id}', 'Api\v4\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\v4\WalletController@walletRechargeHistory')->middleware('auth:api');
    Route::post('wallet/recharge','Api\v4\WalletController@recharge')->middleware('auth:api');
    Route::post('wallet/rechargestore','Api\v4\WalletController@wallet_recharge_done')->middleware('auth:api');
    Route::get('/mapped_cities','Api\v4\HomeController@mapped_cities');
    Route::post('/city_pincode','Api\v4\HomeController@get_area_for_delivery');
    Route::get('/razorpay_key','Api\v4\HomeController@razorPayKey');

    //Recurring-order
    Route::post('order/recurring','Api\v4\RecurringController@orderRecurring')->middleware('auth:api');

    Route::get('order/recurringpayment','Api\v4\RecurringController@OrderRecurringPayment');

    Route::post('order/recurringonline','Api\v4\RecurringController@orderRecurringByOnlinePay')->middleware('auth:api');
    Route::post('order/recurringstore','Api\v4\RecurringController@recurringStore')->middleware('auth:api');
    Route::post('order/recurringlist','Api\v4\RecurringController@recurringList')->middleware('auth:api');
    Route::post('order/recurringprice','Api\v4\RecurringController@recurringPrice')->middleware('auth:api');
    Route::post('order/recurringorderlist','Api\v4\RecurringController@recurringOrderList')->middleware('auth:api');
    Route::post('order/recurringorderdetail','Api\v4\RecurringController@recurringOrderDetail')->middleware('auth:api');
    
    Route::post('order/unsubscribed','Api\v4\RecurringController@unsubscribed')->middleware('auth:api');
    Route::post('user/addressbyshortid', 'Api\v4\AddressController@addressListBySortinghub')->middleware('auth:api');

    Route::post('order/recurringordertest','Api\v4\RecurringController@OrderRecurringPayment')->middleware('auth:api');

    // Refund Request
    Route::get('refund_request/{id}', 'Api\v4\RefundRequestController@refund_request_send_page')->middleware('auth:api');
    Route::post('/refund_requests/store', 'Api\v4\RefundRequestController@refund_request_sends')->middleware('auth:api');

    //Replacement Request
    Route::get('replacement_request/{id}', 'Api\v4\ReplacementController@order_replacement')->middleware('auth:api');
    Route::post('/replacement_request/store', 'Api\v4\ReplacementController@storeReplacementRequest')->middleware('auth:api');
    Route::post('/upload-images','Api\v4\ReplacementController@uploadImages')->middleware('auth:api');

    //Conversation
    Route::post('/conversation','Api\v4\ConversationController@store')->middleware('auth:api');

    // Delivery Boy Routes
    Route::prefix('deliveryboy')->group(function(){
        Route::post('/orders','Api\v4\OrderController@deliveryBoyOrders')->middleware('auth:api');
        Route::get('/orderdetail/{id}','Api\v4\OrderController@orderDetail')->middleware('auth:api');
        Route::post('/order_status_update','Api\v4\OrderController@update_delivery_status')->middleware('auth:api');
        Route::get('/count_new_order/{id}','Api\v4\OrderController@count_new_order')->middleware('auth:api');
        Route::get('/update_new_order_status/{id}','Api\v4\OrderController@update_new_order_status')->middleware('auth:api');
        Route::get('/new_orders/{id}','Api\v4\OrderController@new_orders')->middleware('auth:api');
        Route::post('/orders_product_wise','Api\v4\OrderController@get_order_product_wise')->middleware('auth:api');
        Route::get('/replacement_orders/{id}','Api\v4\ReplacementController@deliveryBoyReplacementOrders')->middleware('auth:api');
        Route::get('/replacement_order_details/{id}','Api\v4\ReplacementController@replacementOrderDetail')->middleware('auth:api');
        Route::post('/update_replacement_status','Api\v4\ReplacementController@update_replacement_status')->middleware('auth:api');
        Route::post('/take_order','Api\v4\OrderController@takeOrder');
        Route::post('/call_to_customer','Api\v4\AddressController@callToCustomer');
        Route::post('/razorpay_payment_link','Api\v4\OrderController@razorpay_payment_link');
        Route::post('/razorpay_payment_link_webhook','Api\v4\DeliveryboyController@razorpayPaymentLinkWebHook');
        Route::post('/qr_code','Api\v4\DeliveryboyController@generateQRCode');
        Route::post('/razorpay_qr_code_webhook','Api\v4\DeliveryboyController@razorpayQrCodeWebHook');
        Route::post('/assign_orders','Api\v4\DeliveryboyController@assignedOrders');
        Route::post('/sub_order_details','Api\v4\DeliveryboyController@SubOrderDetail');
    });

    
    //sale accounting voucher api - for tally customization
    Route::post('/saletally','Api\v4\SaleTallyController@getOrderDetails')->name('saleTally');

    //language setting
    Route::post('language/setting','Api\v4\AddressController@languageSetting')->name('lanuage.setting');

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
