<?php

Route::prefix('v2/auth')->group(function () {
    Route::post('login', 'Api\v2\AuthController@login');
    Route::post('loginwithOtp', 'Api\v2\AuthController@loginWithOtp');
    Route::post('verifyOtp', 'Api\v2\AuthController@verifyOtpUser');
    Route::post('signup', 'Api\v2\AuthController@signup');
    Route::post('social-login', 'Api\v2\AuthController@socialLogin');
    Route::post('password/create', 'Api\v2\PasswordResetController@create');
    Route::post('password/update', 'Api\v2\PasswordResetController@updatePassword');
    Route::post('password/change', 'Api\v2\PasswordResetController@changePassword');
    Route::post('password/changepassword','Api\v2\PasswordResetController@changePassApi')->middleware('auth:api');
    Route::post('password/verifyuser', 'Api\v2\PasswordResetController@verifyUser');
    Route::post('password/verifyotp', 'Api\v2\PasswordResetController@verifyOTP');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\v2\AuthController@logout');
        Route::get('user', 'Api\v2\AuthController@user');
    });
});

Route::prefix('v2')->group(function () {
	Route::post('/check_updates','Api\v2\AppController@checkUpdates');
	//06-11-2021 - new api for home page
    Route::get('homepage', 'Api\v2\HomePageController@index');

    //20-10-2021
    Route::get('getsortingid','Api\v2\HomePageController@getDistributorSortingID'); 

    Route::apiResource('banners', 'Api\v2\BannerController')->only('index');

    Route::get('brands/top', 'Api\v2\BrandController@top');
    Route::apiResource('brands', 'Api\v2\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\v2\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\v2\CategoryController@featured');
    Route::get('categories/home', 'Api\v2\CategoryController@home');
    Route::apiResource('categories', 'Api\v2\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\v2\SubCategoryController@index')->name('apiv2.subCategories.index');

    Route::apiResource('colors', 'Api\v2\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\v2\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\v2\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\v2\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\v2\HomeCategoryController')->only('index');

    Route::get('purchase-history/{id}', 'Api\v2\PurchaseHistoryController@index')->middleware('auth:api');
    Route::get('purchase-history-details/{id}', 'Api\v2\PurchaseHistoryDetailController@index')->name('apiv2.purchaseHistory.details')->middleware('auth:api');
    Route::get('order/history/{id}', 'Api\v2\PurchaseHistoryController@getOrders')->middleware('auth:api');
    Route::post('order/history/detail', 'Api\v2\PurchaseHistoryDetailController@getOrderDetails')->middleware('auth:api');

    Route::get('products/admin', 'Api\v2\ProductController@admin');
    Route::get('products/seller', 'Api\v2\ProductController@seller');
    Route::get('products/category/{id}', 'Api\v2\ProductController@category')->name('apiv2.products.category');
    Route::get('products/sub-category/{id}', 'Api\v2\ProductController@subCategory')->name('apiv2.products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\v2\ProductController@subSubCategory')->name('apiv2.products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\v2\ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'Api\v2\ProductController@todaysDeal');
    Route::get('products/flash-deal', 'Api\v2\ProductController@flashDeal');
    Route::get('products/featured', 'Api\v2\ProductController@featured');
    Route::get('products/best-seller', 'Api\v2\ProductController@bestSeller');
    Route::get('products/related/{id}', 'Api\v2\ProductController@related')->name('apiv2.products.related');
    Route::get('products/top-from-seller/{id}', 'Api\v2\ProductController@topFromSeller')->name('products.topFromSeller');
    Route::post('products/search', 'Api\v2\ProductController@search')->name('products.search');
    Route::post('products/variant/price', 'Api\v2\ProductController@variantPrice');
    Route::get('products/home', 'Api\v2\ProductController@home');
    Route::post('products/peer_discount', 'Api\v2\ProductController@applyPartner');
    Route::get('products/remove_peercode', 'Api\v2\ProductController@removePeercode');
    
    Route::apiResource('products', 'Api\v2\ProductController')->except(['store', 'update', 'destroy']);

    Route::get('carts/{id}', 'Api\v2\CartController@index');
    Route::post('carts/add', 'Api\v2\CartController@add');
    Route::post('carts/change-quantity', 'Api\v2\CartController@changeQuantity');
    Route::apiResource('carts', 'Api\v2\CartController')->only('destroy');
    Route::get('carts/delete/{id}', 'Api\v2\CartController@deleteCart');
    Route::post('carts/apply_discount_on_cart','Api\v2\CartController@applyPeerDiscountCart');
    Route::post('carts/check_in_cart','Api\v2\CartController@checkInCart');
    Route::post('carts/check_availability','Api\v2\CartController@checkAvailablity');
    Route::post('carts/check_cart_price','Api\v2\CartController@checkCartPrice');
    Route::post('carts/apply_peer_on_checkout','Api\v2\CartController@applyPeerOnCheckout');
    Route::post('carts/itemno','Api\v2\CartController@getCartItemCount');

    Route::get('reviews/product/{id}', 'Api\v2\ReviewController@index')->name('apiv2.reviews.index');
    Route::post('store-review', 'Api\v2\ReviewController@store')->name('api.reviews.store')->middleware('auth:api');
    Route::match(['GET', 'POST'], 'products/sorting/filter', 'Api\v2\ProductController@ProductSorting');

    Route::get('shop/user/{id}', 'Api\v2\ShopController@shopOfUser')->middleware('auth:api');
    Route::get('shops/details/{id}', 'Api\v2\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\v2\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\v2\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\v2\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\v2\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\v2\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\v2\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\v2\SliderController')->only('index');
    Route::get('allcategory', 'Api\v2\ProductController@getAllCategory');

    Route::get('wishlists/{id}', 'Api\v2\WishlistController@index')->middleware('auth:api');
    Route::post('wishlists/check-product', 'Api\v2\WishlistController@isProductInWishlist')->middleware('auth:api');
    Route::apiResource('wishlists', 'Api\v2\WishlistController')->except(['index', 'update', 'show'])->middleware('auth:api');

    Route::apiResource('settings', 'Api\v2\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\v2\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\v2\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\v2\PolicyController@returnPolicy')->name('policies.return');

    Route::get('user/info/{id}', 'Api\v2\UserController@info')->middleware('auth:api');
    Route::post('user/info/update', 'Api\v2\UserController@updateName')->middleware('auth:api');
    Route::post('user/peer/partner', 'Api\v2\UserController@createPeerPartner')->middleware('auth:api');
    Route::post('user/peer/check_referral', 'Api\v2\UserController@check_referral')->middleware('auth:api');
    Route::get('user/get_last_used_referral_code/{id}', 'Api\v2\UserController@getUserLastUsedPeerCode')->middleware('auth:api');
    
    Route::get('user/shipping/address/{id}', 'Api\v2\AddressController@addresses')->middleware('auth:api');
    Route::post('user/shipping/create', 'Api\v2\AddressController@createShippingAddress')->middleware('auth:api');
    Route::get('user/shipping/delete/{id}', 'Api\v2\AddressController@deleteShippingAddress')->middleware('auth:api');

    //06-10-2021
    Route::post('user/shipping/setdefaultaddress', 'Api\v2\AddressController@setAddressDefault')->middleware('auth:api');

    Route::post('coupon/apply', 'Api\v2\CouponController@apply')->middleware('auth:api');

    Route::post('payments/pay/stripe', 'Api\v2\StripeController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/paypal', 'Api\v2\PaypalController@processPayment')->middleware('auth:api');
    Route::post('payments/pay/wallet', 'Api\v2\WalletController@processPayment');
    Route::post('payments/pay/cod', 'Api\v2\PaymentController@cashOnDelivery');

    //24-11-2021
    Route::post('search/suggestion', 'Api\v2\SearchController@suggestion')->name('search.suggestion');
    Route::post('search/result','Api\v2\SearchController@searchList')->name('search.list');

    //Order 
    Route::post('order/initiate', 'Api\v2\OrderController@orderinitiate');
    Route::post('order/store', 'Api\v2\OrderController@checkout_done');
    Route::post('order/cancel', 'Api\v2\OrderController@cancelOrder');
    Route::post('order/track','Api\v2\PurchaseHistoryDetailController@trackOrder');
    Route::post('getproductattribute','Api\v2\ProductController@getProductAttribute');

    Route::get('wallet/balance/{id}', 'Api\v2\WalletController@balance')->middleware('auth:api');
    Route::get('wallet/history/{id}', 'Api\v2\WalletController@walletRechargeHistory')->middleware('auth:api');
    Route::get('/mapped_cities','Api\v2\HomeController@mapped_cities');
    Route::post('/city_pincode','Api\v2\HomeController@get_area_for_delivery');
    Route::post('/call_to_customer','Api\v2\AddressController@callToCustomer');
    Route::post('/razorpay_payment_link','Api\v2\OrderController@razorpay_payment_link');
    Route::get('/razorpay_key','Api\v2\HomeController@razorPayKey');

    // Refund Request
    Route::get('refund_request/{id}', 'Api\v2\RefundRequestController@refund_request_send_page')->middleware('auth:api');
    Route::post('/refund_requests/store', 'Api\v2\RefundRequestController@refund_request_sends')->middleware('auth:api');

    //Replacement Request
    Route::get('replacement_request/{id}', 'Api\v2\ReplacementController@order_replacement')->middleware('auth:api');
    Route::post('/replacement_request/store', 'Api\v2\ReplacementController@storeReplacementRequest')->middleware('auth:api');
    Route::post('/upload-images','Api\v2\ReplacementController@uploadImages')->middleware('auth:api');

    //Conversation
    Route::post('/conversation','Api\v2\ConversationController@store')->middleware('auth:api');

    // Delivery Boy Routes
    
    Route::post('deliveryboy/orders','Api\v2\OrderController@deliveryBoyOrders')->middleware('auth:api');
    Route::get('deliveryboy/orderdetail/{id}','Api\v2\OrderController@orderDetail')->middleware('auth:api');
    Route::post('deliveryboy/order_status_update','Api\v2\OrderController@update_delivery_status')->middleware('auth:api');
    Route::get('deliveryboy/count_new_order/{id}','Api\v2\OrderController@count_new_order')->middleware('auth:api');
    Route::get('deliveryboy/update_new_order_status/{id}','Api\v2\OrderController@update_new_order_status')->middleware('auth:api');
    Route::get('deliveryboy/new_orders/{id}','Api\v2\OrderController@new_orders')->middleware('auth:api');
    Route::post('deliveryboy/orders_product_wise','Api\v2\OrderController@get_order_product_wise')->middleware('auth:api');
    Route::get('deliveryboy/replacement_orders/{id}','Api\v2\ReplacementController@deliveryBoyReplacementOrders')->middleware('auth:api');
    Route::get('deliveryboy/replacement_order_details/{id}','Api\v2\ReplacementController@replacementOrderDetail')->middleware('auth:api');
    Route::post('deliveryboy/update_replacement_status','Api\v2\ReplacementController@update_replacement_status')->middleware('auth:api');
    Route::post('deliveryboy/take_order','Api\v2\OrderController@takeOrder');
    
    //sale accounting voucher api - for tally customization
    Route::post('/saletally','Api\v2\SaleTallyController@getOrderDetails')->name('saleTally');;

});

Route::fallback(function() {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ]);
});
