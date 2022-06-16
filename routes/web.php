<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// use App\Mail\SupportMailManager;
//demo
Route::get('/demo/cron_1', 'DemoController@cron_1');
Route::get('/demo/cron_2', 'DemoController@cron_2');

// Route::get('helloworld', function() {
// 	return new SupportMailManager();
// });

Route::get('index', function(){
	return view('frontend.indexold');
});

	Route::get('callcetertbl','CallCenterController@Callcetertbl')->name('callceter.callcetertbl');
	Route::get('/peer_partner/login/{id}', 'CallCenterController@login')->name('callceter.login');

	Route::get('fieldofficer','CallCenterController@fieldofficer')->name('callceter.fieldofficer');
	Route::post('fieldofficerisactive','CallCenterController@Fieldofficerisactive')->name('callceter.fieldofficerisactive');
	Route::post('/updateapibycallcenter','AddressController@updateAddressByCallcenter')->name('updateaddressbycallcenter');




	Route::get('/create_partner_callcenter','CallCenterController@createPartnerByAdmin')->name('callceter.create_partner');
	Route::post('/store_partner_admin','CallCenterController@storePartnerCreateByAdmin')->name('callceter.store_partner');


	Route::get('/finalordershtml', 'CallCenterController@html_data_table')->name('callceter.finalordershtml');

	Route::get('/finalorders/export', 'CallCenterController@final_orders_export')->name('callceter.exportproduct');
	Route::get('/finalorders/productexportfinal', 'CallCenterController@orders_productexport_final')->name('callceter.exportproductwise');


	Route::get('/order-operations', 'OperationCallcenterController@admin_orders')->name('callceter.orderspration');
	Route::get('/orders/{id}/show', 'OperationCallcenterController@show')->name('callceter.show');
	Route::get('/remove-order-product/{id}', 'OperationCallcenterController@removeOrderProduct')->name('callceter.removeorderproduct');

	Route::get('/add_to_order/{id}','OrderController@addtoOrderoperations')->name('callceter.add_to_order');
	Route::post('/products_add_to_order','OrderController@get_product_by_hub_category')->name('admin.products_add_to_order');
	Route::post('/products_added_to_order','OrderController@load_products_added_to_order')->
	       name('admin.load_products_added_to_order');
	Route::post('/store_added_to_order','OrderController@storeAddToOrder')->name('admin.store_added_to_order');
	
    Route::post('/set-cancel-otp', 'DeliveryBoyController@set_cancel_otp')->name('callceter.cancelorder');
	Route::post('/get-cancel-otp', 'DeliveryBoyController@get_cancel_otp')->name('callceter.getcancel_otp');
	Route::post('/sorthinghub/assign_order', 'SortingHubController@assignOrder')->name('sorthinghub.assign_order');




	Route::get('logincallcenter','CallCenterController@Login')->name('callceter.logincallcenter');
	Route::post('logincc','CallCenterController@Logincc')->name('callceter.logincc');


Route::get('/updateSubOrder/{start_date}/{end_date}','TestController@updateSubOrder');

Auth::routes(['verify' => true]);
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('/email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
Route::get('/verification-confirmation/{code}', 'Auth\VerificationController@verification_confirmation')->name('email.verification.confirmation');
Route::get('/email_change/callback', 'HomeController@email_change_callback')->name('email_change.callback');

Route::get('/addcustomer','CustomerController@addToCustomerIfNotExist');
Route::post('/language', 'LanguageController@changeLanguage')->name('language.change');
Route::post('/language-invoice', 'LanguageController@changeInvoiceLang')->name('language.change-invoice');
Route::post('/currency', 'CurrencyController@changeCurrency')->name('currency.change');

Route::post('/location', 'HomeController@setLocation')->name('location.set');

Route::get('/social-login/redirect/{provider}', 'Auth\LoginController@redirectToProvider')->name('social.login');
Route::get('/social-login/{provider}/callback', 'Auth\LoginController@handleProviderCallback')->name('social.callback');
Route::get('/users/login', 'HomeController@login')->name('user.login');
Route::get('/users/registration', 'HomeController@registration')->name('user.registration');
Route::post('/users/register', 'HomeController@register')->name('user.register');
Route::get('/users/verify_otp', 'HomeController@verify_otp')->name('user.verify_otp');
Route::post('/users/login_phone', 'Auth\LoginController@loginphone')->name('user.login_phone');
Route::get('/users/login_otp', 'HomeController@login_otp')->name('user.login_otp');
Route::post('/otp/resend','Auth\LoginController@resend_otp')->name('otp.resend');

Route::post('/users/register_phone', 'Auth\RegisterController@register_user_phone')->name('user.register_phone');
Route::post('/users/user_otp_register', 'Auth\RegisterController@verifyRegistration')->name('user.verify_registration');

Route::get('/users/verify_reg/{id}', 'Auth\RegisterController@verifyReg')->name('user.verify_reg');

Route::get('/users/user_otp_register', 'HomeController@user_otp')->name('user.user_otp');
Route::get('/users/register_phone', 'HomeController@register_phone')->name('user.register_phone');

//Route::post('/users/login', 'HomeController@user_login')->name('user.login.submit');
Route::post('/users/login/cart', 'HomeController@cart_login')->name('cart.login.submit');

Route::post('/vendors/get_vendor_by_manage_id', 'SellerController@get_vendors_by_manage_id')->name('vendors.get_vendors_by_manage_id');


Route::post('/subcategories/get_subcategories_by_category', 'SubCategoryController@get_subcategories_by_category')->name('subcategories.get_subcategories_by_category');
Route::post('/subsubcategories/get_subsubcategories_by_subcategory', 'SubSubCategoryController@get_subsubcategories_by_subcategory')->name('subsubcategories.get_subsubcategories_by_subcategory');
Route::post('/subsubcategories/get_brands_by_subsubcategory', 'SubSubCategoryController@get_brands_by_subsubcategory')->name('subsubcategories.get_brands_by_subsubcategory');
Route::post('/subsubcategories/get_attributes_by_subsubcategory', 'SubSubCategoryController@get_attributes_by_subsubcategory')->name('subsubcategories.get_attributes_by_subsubcategory');

//17 may 2021
Route::post('/categories/get_categories_by_hub', 'SubCategoryController@get_categories_by_hub')->name('categories.get_categories_by_hub');


Route::resource('peer-partner', 'PeerPartnerController');
Route::post('/discount/apply_partner_coupon_code', 'HomeController@apply_partner_coupon_code')->name('discount.apply_partner_coupon_code');
Route::post('/discount/remove_partner_coupon_code', 'HomeController@remove_partner_coupon_code')->name('discount.remove_partner_coupon_code');
Route::get('partner_referral_history', 'PeerPartnerController@referral_history')->name('partner.referral.history');

//12-10-2021
Route::post('/discount/apply_partner_coupon_code_without_login', 'HomeController@apply_partner_coupon_code_without_login')->name('discount.apply_partner_coupon_code_without_login');

//14may
Route::get('/peer_partner/create', 'PeerPartnerController@create_peer')->name('peer_partner.createpeer');
Route::post('/products/get_products_by_category', 'PeerPartnerController@get_products_by_category')->name('products.get_products_by_category');
Route::post('/products/peerdiscount', 'PeerPartnerController@store_peer_discount')->name('product.store_peer_discount');


Route::get('/peer_partner/show_all_peer_commission', 'PeerPartnerController@showall_peer_commission')->name('peer_partner.peer_commision');
	Route::post('/peer_partner/show_all_peer_commission', 'PeerPartnerController@showall_peer_commission_by_date')->name('peer_partner.peer_commisionbydate');

    Route::get('/peer_partner/{id}/show_all_subpeer', 'PeerPartnerController@showall_subpeer')->name('peer_partner.sub_peer');
	Route::post('/peer_partner/{id}/show_all_subpeer', 'PeerPartnerController@add_subpeer')->name('peer_partner.add');
	Route::get('/peer_partner/destroy/{id}', 'PeerPartnerController@subpeer_destroy')->name('peer_partner.subpeerdestroy');
	Route::post('/peer_partner/check_referral', 'PeerPartnerController@check_referral')->name('peer_partner.referrals');


//Home Page
// Route::get('/shop_now', 'FrontEndController@index')->name('home');
Route::get('/shop_now', 'HomeController@index')->name('home');
Route::get('/', 'HomeController@shopNow')->name('shopnow');
//Route::get('/', 'FrontEndController@index')->name('new.home');
	// Route::get('/index1', 'HomeController@index')->name('home1');
///Route::get('/', 'HomeController@maintenance')->name('home');
Route::post('/home/section/featured', 'HomeController@load_featured_section')->name('home.section.featured');
Route::post('/home/section/best_selling', 'HomeController@load_best_selling_section')->name('home.section.best_selling');
Route::post('/home/section/home_categories', 'HomeController@load_home_categories_section')->name('home.section.home_categories');
Route::post('/home/section/best_sellers', 'HomeController@load_best_sellers_section')->name('home.section.best_sellers');

Route::post('/home/section/banner_slider', 'HomeController@load_banner_slider_section')->name('home.section.banner_slider');
Route::post('/home/section/finance_banner', 'HomeController@load_finance_banner_section')->name('home.section.finance_banner');
Route::post('/home/section/master_banner', 'HomeController@load_master_banner_section')->name('home.section.master_banner');
Route::post('/home/check_pin', 'HomeController@check_pinall')->name('home.checkpin');

//category dropdown menu ajax call
Route::post('/category/nav-element-list', 'HomeController@get_category_items')->name('category.elements');
Route::post('/state/get_state_by_country_id', 'SubCategoryController@get_state_by_country_id')->name('state.get_state_by_country_id');

Route::post('/city/get_city_by_state', 'SubCategoryController@citySearch')->name('city.get_city_by_state');

//Flash Deal Details Page
Route::get('/flash-deal/{slug}', 'HomeController@flash_deal_details')->name('flash-deal-details');


//Route
Route::get('/location/area-search/', 'HomeController@area_searching')->name('location.area.search');

Route::get('/sitemap.xml', function(){
	return base_path('sitemap.xml');
});


Route::get('/customer-products', 'CustomerProductController@customer_products_listing')->name('customer.products');
Route::get('/customer-products?subsubcategory={subsubcategory_slug}', 'CustomerProductController@search')->name('customer_products.subsubcategory');
Route::get('/customer-products?subcategory={subcategory_slug}', 'CustomerProductController@search')->name('customer_products.subcategory');
Route::get('/customer-products?category={category_slug}', 'CustomerProductController@search')->name('customer_products.category');
Route::get('/customer-products?city={city_id}', 'CustomerProductController@search')->name('customer_products.city');
Route::get('/customer-products?q={search}', 'CustomerProductController@search')->name('customer_products.search');
Route::get('/customer-product/{slug}', 'CustomerProductController@customer_product')->name('customer.product');
Route::get('/customer-packages', 'HomeController@premium_package_index')->name('customer_packages_list_show');
//Route::get('/test','OrderController@testSMS')->name('test_sms');
Route::get('/generate-commission/{id}','TestController@generateOrderCommission');


Route::get('/product/{slug}', 'HomeController@product')->name('product');
Route::get('/products', 'HomeController@listing')->name('products');
Route::get('/search?category={category_slug}', 'HomeController@search')->name('products.category');
Route::get('/search?subcategory={subcategory_slug}', 'HomeController@search')->name('products.subcategory');
Route::get('/search?subsubcategory={subsubcategory_slug}', 'HomeController@search')->name('products.subsubcategory');
Route::get('/search?brand={brand_slug}', 'HomeController@search')->name('products.brand');
Route::post('/product/variant_price', 'HomeController@variant_price')->name('products.variant_price');

Route::get('/shops/visit/{slug}', 'HomeController@shop')->name('shop.visit');
Route::get('/shops/visit/{slug}/{type}', 'HomeController@filter_shop')->name('shop.visit.type');

Route::get('/cart', 'CartController@index')->name('cart');
Route::post('/cart/nav-cart-items', 'CartController@updateNavCart')->name('cart.nav_cart');
Route::post('/cart/show-cart-modal', 'CartController@showCartModal')->name('cart.showCartModal');
Route::post('/cart/addtocart', 'CartController@addToCart')->name('cart.addToCart');
Route::post('/cart/removeFromCart', 'CartController@removeFromCart')->name('cart.removeFromCart');
Route::post('/cart/updateQuantity', 'CartController@updateQuantity')->name('cart.updateQuantity');
Route::post('/cart/updatecartq','CartController@updateCartQ')->name('cart.updatecartq');
Route::post('cart/cart_qty','CartController@cart_qty')->name('cart.cart_qty');
Route::post('cart/total_items','CartController@totalCartItem')->name('cart.total_items');

Route::post('cart/remove_cart_products', 'CartController@removecartproducts')->name('cart.remove_cart_products');
//Checkout Routes
Route::group(['middleware' => ['checkout']], function(){
	Route::get('/checkout', 'CheckoutController@get_shipping_info')->name('checkout.shipping_info');
	Route::get('/checkout/delivery_info', 'CheckoutController@get_delivery_info_view')->name('checkout.delivery_info');
	Route::post('/checkout/delivery_info', 'CheckoutController@store_shipping_info')->name('checkout.store_shipping_infostore');
	Route::post('/checkout/payment_select', 'CheckoutController@store_delivery_info')->name('checkout.store_delivery_info');
});

Route::get('/checkout/order-confirmed', 'CheckoutController@order_confirmed')->name('order_confirmed');
Route::post('/checkout/payment', 'CheckoutController@checkout')->name('payment.checkout');
Route::post('/get_pick_ip_points', 'HomeController@get_pick_ip_points')->name('shipping_info.get_pick_ip_points');
Route::get('/checkout/payment_select', 'CheckoutController@get_payment_info')->name('checkout.payment_info');
Route::post('/checkout/apply_coupon_code', 'CheckoutController@apply_coupon_code')->name('checkout.apply_coupon_code');
Route::post('/checkout/remove_coupon_code', 'CheckoutController@remove_coupon_code')->name('checkout.remove_coupon_code');

//Paypal START
Route::get('/paypal/payment/done', 'PaypalController@getDone')->name('payment.done');
Route::get('/paypal/payment/cancel', 'PaypalController@getCancel')->name('payment.cancel');
//Paypal END

// SSLCOMMERZ Start
Route::get('/sslcommerz/pay', 'PublicSslCommerzPaymentController@index');
Route::POST('/sslcommerz/success', 'PublicSslCommerzPaymentController@success');
Route::POST('/sslcommerz/fail', 'PublicSslCommerzPaymentController@fail');
Route::POST('/sslcommerz/cancel', 'PublicSslCommerzPaymentController@cancel');
Route::POST('/sslcommerz/ipn', 'PublicSslCommerzPaymentController@ipn');
//SSLCOMMERZ END

//Stipe Start
Route::get('stripe', 'StripePaymentController@stripe');
Route::post('stripe', 'StripePaymentController@stripePost')->name('stripe.post');
//Stripe END

Route::get('/compare', 'CompareController@index')->name('compare');
Route::get('/compare/reset', 'CompareController@reset')->name('compare.reset');
Route::post('/compare/addToCompare', 'CompareController@addToCompare')->name('compare.addToCompare');

Route::resource('subscribers','SubscriberController');

Route::get('/brands', 'HomeController@all_brands')->name('brands.all');
Route::get('/categories', 'HomeController@all_categories')->name('categories.all');
Route::get('/search', 'HomeController@search')->name('search');
Route::get('/search?q={search}', 'HomeController@search')->name('suggestion.search');
Route::post('/ajax-search', 'HomeController@ajax_search')->name('search.ajax');
Route::post('/config_content', 'HomeController@product_content')->name('configs.update_status');

Route::get('/sellerpolicy', 'HomeController@sellerpolicy')->name('sellerpolicy');
Route::get('/returnpolicy', 'HomeController@returnpolicy')->name('returnpolicy');
Route::get('/supportpolicy', 'HomeController@supportpolicy')->name('supportpolicy');
Route::get('/terms', 'HomeController@terms')->name('terms');
Route::get('/privacypolicy', 'HomeController@privacypolicy')->name('privacypolicy');
Route::get('/contact-us', 'HomeController@contactUs')->name('contactus');
Route::get('/about-us', 'HomeController@aboutUs')->name('aboutus');
Route::post('/area/get_area_for_delivery', 'UserMappingController@get_area_for_delivery')->name('area.get_area_for_delivery');

Route::group(['middleware' => ['user','unbanned']], function(){
	Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
	Route::get('/profile', 'HomeController@profile')->name('profile');
	Route::post('/new-user-verification', 'HomeController@new_verify')->name('user.new.verify');
	Route::post('/new-user-email', 'HomeController@update_email')->name('user.change.email');
	Route::post('/customer/update-profile', 'HomeController@customer_update_profile')->name('customer.profile.update');
	Route::post('/seller/update-profile', 'HomeController@seller_update_profile')->name('seller.profile.update');

	Route::resource('purchase_history','PurchaseHistoryController');
	Route::post('/purchase_history/details', 'PurchaseHistoryController@purchase_history_details')->name('purchase_history.details');
	Route::get('/purchase_history/destroy/{id}', 'PurchaseHistoryController@destroy')->name('purchase_history.destroy');

	Route::resource('wishlists','WishlistController');
	Route::post('/wishlists/remove', 'WishlistController@remove')->name('wishlists.remove');

	Route::get('/wallet', 'WalletController@index')->name('wallet.index');
	Route::post('/recharge', 'WalletController@recharge')->name('wallet.recharge');
	Route::post('/wallet/transfer', 'WalletController@transferToBankAccount')->name('wallet.transfer');

	Route::post('/wallet/{id}/show', 'WalletController@show_subpeers_by_date')->name('subpeers_commissions.showbydate');
	Route::get('/wallet_peer_commissions/{id}/show', 'WalletController@show_wpeer_commission')->name('wallet_peer_commissions.show');
	Route::post('/wallet_peer_commissions/{id}/show', 'WalletController@show_wpeer_commission_by_date')->name('wallet_peer_commissions.showbydate');
	Route::get('/wallet_customerpeer/{id}/show', 'WalletController@show_wallet_customerpeer_commission')->name('wallet_customerpeer_commissions.show');
	Route::post('/wallet_customerpeer/{id}/show', 'WalletController@show_wallet_customerpeer_commission_by_date')->name('wallet_customerpeer_commissions.showbydate');

	Route::post('/wallet_subpeer_commissions/{id}/show', 'WalletController@show_wsubpeer_commission_by_date')->name('wallet_subpeer_commissions.showbydate');
	Route::get('/wallet_customer_subpeer/{id}/show', 'WalletController@show_wallet_customer_subpeer_commission')->name('wallet_customer_subpeer_commissions.show');
	Route::post('/wallet_customer_subpeer/{id}/show', 'WalletController@show_wallet_customer_subpeer_commission_by_date')->name('wallet_customer_subpeer_commissions.showbydate');




	Route::resource('support_ticket','SupportTicketController');
	Route::post('support_ticket/reply','SupportTicketController@seller_store')->name('support_ticket.seller_store');

	Route::post('/customer_packages/purchase', 'CustomerPackageController@purchase_package')->name('customer_packages.purchase');
	Route::resource('customer_products', 'CustomerProductController');
	Route::post('/customer_products/published', 'CustomerProductController@updatePublished')->name('customer_products.published');


	
	Route::post('/customer_products/status', 'CustomerProductController@updateStatus')->name('customer_products.update.status');

	Route::get('digital_purchase_history', 'PurchaseHistoryController@digital_index')->name('digital_purchase_history.index');
});
    Route::post('/mapping/published','ProductMapController@changePublished')->name('mapping.published');
    Route::post('/mapping/recurring','ProductMapController@changeRecurring')->name('mapping.recurring');
    Route::post('/distributor/status','DistributorController@changeDistributorStatus')->name('distributor.status');
	Route::post('/mapping/stock','ProductMapController@updateStock')->name('mapping.stock');
	Route::post('/mapping/purchased-price','ProductMapController@updatePurchasePrice')->name('mapping.purchased_price');
	Route::post('/mapping/selling-price','ProductMapController@updateSellingPrice')->name('mapping.selling_price');
	Route::post('/product/minpurchaselimit','ProductController@minPurchaseLimit')->name('product.purchaselimit');
	Route::post('/product/min_purchaselimit','ProductController@min_PurchaseLimit')->name('product.min_purchaselimit');

	Route::post('/mapping/productsbyhub','ProductMapController@changeproductsbyhub')->name('mapping.productsbyhub');

	Route::post('/mapping/recurproductsbyhub','ProductMapController@changerecurproductsbyhub')->name('mapping.recurproductsbyhub');

Route::get('/customer_products/destroy/{id}', 'CustomerProductController@destroy')->name('customer_products.destroy');

Route::group(['prefix' =>'seller', 'middleware' => ['seller', 'verified', 'user']], function(){
	Route::get('/products', 'HomeController@seller_product_list')->name('seller.products');
	Route::get('/product/upload', 'HomeController@show_product_upload_form')->name('seller.products.upload');
	Route::get('/product/{id}/edit', 'HomeController@show_product_edit_form')->name('seller.products.edit');
	Route::resource('payments','PaymentController');

	Route::get('/shop/apply_for_verification', 'ShopController@verify_form')->name('shop.verify');
	Route::post('/shop/apply_for_verification', 'ShopController@verify_form_store')->name('shop.verify.store');

	Route::get('/reviews', 'ReviewController@seller_reviews')->name('reviews.seller');

	//digital Product
	Route::get('/digitalproducts', 'HomeController@seller_digital_product_list')->name('seller.digitalproducts');
	Route::get('/digitalproducts/upload', 'HomeController@show_digital_product_upload_form')->name('seller.digitalproducts.upload');
	Route::get('/digitalproducts/{id}/edit', 'HomeController@show_digital_product_edit_form')->name('seller.digitalproducts.edit');
});

Route::group(['middleware' => ['auth']], function(){
	Route::post('/products/store/','ProductController@store')->name('products.store');
	Route::post('/products/update/{id}','ProductController@update')->name('products.update');
	Route::get('/products/destroy/{id}', 'ProductController@destroy')->name('products.destroy');
	Route::get('/products/duplicate/{id}', 'ProductController@duplicate')->name('products.duplicate');
	Route::post('/products/sku_combination', 'ProductController@sku_combination')->name('products.sku_combination');
	Route::post('/products/sku_combination_edit', 'ProductController@sku_combination_edit')->name('products.sku_combination_edit');
	Route::post('/products/featured', 'ProductController@updateFeatured')->name('products.featured');
	Route::post('/products/published', 'ProductController@updatePublished')->name('products.published');

	//03-11-2021
	Route::get('/products/delete/{id}', 'ProductController@delete_product')->name('products.delete');

	Route::post('/products/topproducts', 'ProductController@updatetopproducts')->name('products.topproducts');

	Route::get('invoice/customer/{order_id}', 'InvoiceController@customer_invoice_download')->name('customer.invoice.download');
	Route::get('invoice/seller/{order_id}', 'InvoiceController@seller_invoice_download')->name('seller.invoice.download');
	Route::get('invoice/breakup/{slug}', 'InvoiceController@breakUpInvoice')->name('breakUpInvoice.invoice.download');
	Route::post('/invoice/size','InvoiceController@set_invoice_size')->name('admin.set_invoice_size');
	Route::post('invoice/save_print_invoice','InvoiceController@store_print_invoice')->name('invoice.save_print_invoice');



	Route::resource('orders','OrderController');
	Route::get('/orders/destroy/{id}', 'OrderController@destroy')->name('orders.destroy');
	Route::post('/orders/details', 'OrderController@order_details')->name('orders.details');
	Route::post('/orders/update_delivery_status', 'OrderController@update_delivery_status')->name('orders.update_delivery_status');
	Route::post('/orders/update_payment_status', 'OrderController@update_payment_status')->name('orders.update_payment_status');
	Route::get('/orders/cancel/{id}', 'OrderController@cancel_order');
	Route::post('orders/cancel','OrderController@cancelOrder')->name('order.cancel');
	// Route::post('detail/order','OrderController@getOrderReplaceDetail')->name("detail.order");
	// Route::post('replacement/store','OrderController@storeOrderReplace')->name("orderReplacement.store");
	// Route::get('/orders/replace/{id}', 'OrderController@order_replacement');

	Route::get('/orders/replace/{id}', 'ReplacementController@order_replacement');
	Route::post('detail/order','ReplacementController@getOrderReplaceDetail')->name("detail.order");
	Route::post('replacement/store','ReplacementController@storeOrderReplace')->name("orderReplacement.store");


	
	Route::resource('/reviews', 'ReviewController');

	Route::resource('/withdraw_requests', 'SellerWithdrawRequestController');
	Route::get('/withdraw_requests_all', 'SellerWithdrawRequestController@request_index')->name('withdraw_requests_all');
	Route::post('/withdraw_request/payment_modal', 'SellerWithdrawRequestController@payment_modal')->name('withdraw_request.payment_modal');
	Route::post('/withdraw_request/message_modal', 'SellerWithdrawRequestController@message_modal')->name('withdraw_request.message_modal');

	Route::resource('conversations','ConversationController');
	Route::get('/conversations/destroy/{id}', 'ConversationController@destroy')->name('conversations.destroy');
	Route::post('conversations/refresh','ConversationController@refresh')->name('conversations.refresh');
	Route::resource('messages','MessageController');

	//Product Bulk Upload
	Route::get('/product-bulk-upload/index', 'ProductBulkUploadController@index')->name('product_bulk_upload.index');
	// Route::post('/bulk-product-upload', 'ProductBulkUploadController@bulk_upload')->name('bulk_product_upload');
	Route::get('/product-csv-download/{type}', 'ProductBulkUploadController@import_product')->name('product_csv.download');
	Route::get('/vendor-product-csv-download/{id}', 'ProductBulkUploadController@import_vendor_product')->name('import_vendor_product.download');
	Route::group(['prefix' =>'bulk-upload/download'], function(){
		Route::get('/category', 'ProductBulkUploadController@pdf_download_category')->name('pdf.download_category');
		Route::get('/sub_category', 'ProductBulkUploadController@pdf_download_sub_category')->name('pdf.download_sub_category');
		Route::get('/sub_sub_category', 'ProductBulkUploadController@pdf_download_sub_sub_category')->name('pdf.download_sub_sub_category');
		Route::get('/brand', 'ProductBulkUploadController@pdf_download_brand')->name('pdf.download_brand');
		Route::get('/seller', 'ProductBulkUploadController@pdf_download_seller')->name('pdf.download_seller');
		Route::post('/bulk-product-upload', 'ProductBulkUploadController@bulk_upload')->name('bulk_product_upload');

		Route::post('/set-productimport-otp', 'ProductBulkUploadController@set_productimport_otp')->name('productimport.set_otp');
		Route::post('/get-productimport-otp', 'ProductBulkUploadController@get_productimport_otp')->name('productimport.get_otp');
	});

	//Product Export
	Route::get('/product-bulk-export', 'ProductBulkUploadController@export')->name('product_bulk_export.index');

	//10dec
	Route::get('/productmap-bulk-upload/index', 'ProductBulkUploadController@productmap_index')->name('productmap_bulk_upload.index');
	Route::post('/bulk-product-upload', 'ProductBulkUploadController@productmap_bulk_upload')->name('productmap_bulk_product_upload');

	Route::resource('digitalproducts','DigitalProductController');
	Route::get('/digitalproducts/destroy/{id}', 'DigitalProductController@destroy')->name('digitalproducts.destroy');
	Route::get('/digitalproducts/download/{id}', 'DigitalProductController@download')->name('digitalproducts.download');
});

Route::resource('shops', 'ShopController');
Route::get('/track', 'HomeController@trackOrder')->name('orders.track');

Route::get('/instamojo/payment/pay-success', 'InstamojoController@success')->name('instamojo.success');

Route::post('rozer/payment/pay-success', 'RazorpayController@payment')->name('payment.rozer');
Route::post('rozer/payment/pay-fail', 'RazorpayController@paymentFail')->name('payment.razorpay_fail');

Route::get('/paystack/payment/callback', 'PaystackController@handleGatewayCallback');

Route::get('/vogue-pay', 'VoguePayController@showForm');
Route::get('/vogue-pay/success/{id}', 'VoguePayController@paymentSuccess');
Route::get('/vogue-pay/failure/{id}', 'VoguePayController@paymentFailure');

//2checkout Start
Route::post('twocheckout/payment/callback', 'TwoCheckoutController@twocheckoutPost')->name('twocheckout.post');
//2checkout END

Route::resource('addresses','AddressController');
Route::get('/addresses/destroy/{id}', 'AddressController@destroy')->name('addresses.destroy');
Route::get('/addresses/set_default/{id}', 'AddressController@set_default')->name('addresses.set_default');
Route::any('/addresses/set_location', 'AddressController@set_location')->name('addresses.set_location');
Route::get('showmap/{page}', 'AddressController@showMap')->name('show.map');


//Route::resource('careers','CareerController');
Route::get('/careers','CareerController@index')->name('careers.index');
Route::post('/careers/store','CareerController@store')->name('careers.store');
Route::get('/careers/detail/{id}','CareerController@detail')->name('careers.detail');
Route::post('/careers','CareerController@search')->name('careers.search');

//payhere below
Route::get('/payhere/checkout/testing', 'PayhereController@checkout_testing')->name('payhere.checkout.testing');
Route::get('/payhere/wallet/testing', 'PayhereController@wallet_testing')->name('payhere.checkout.testing');
Route::get('/payhere/customer_package/testing', 'PayhereController@customer_package_testing')->name('payhere.customer_package.testing');

Route::any('/payhere/checkout/notify', 'PayhereController@checkout_notify')->name('payhere.checkout.notify');
Route::any('/payhere/checkout/return', 'PayhereController@checkout_return')->name('payhere.checkout.return');
Route::any('/payhere/checkout/cancel', 'PayhereController@chekout_cancel')->name('payhere.checkout.cancel');

Route::any('/payhere/wallet/notify', 'PayhereController@wallet_notify')->name('payhere.wallet.notify');
Route::any('/payhere/wallet/return', 'PayhereController@wallet_return')->name('payhere.wallet.return');
Route::any('/payhere/wallet/cancel', 'PayhereController@wallet_cancel')->name('payhere.wallet.cancel');

Route::any('/payhere/seller_package_payment/notify', 'PayhereController@seller_package_notify')->name('payhere.seller_package_payment.notify');
Route::any('/payhere/seller_package_payment/return', 'PayhereController@seller_package_payment_return')->name('payhere.seller_package_payment.return');
Route::any('/payhere/seller_package_payment/cancel', 'PayhereController@seller_package_payment_cancel')->name('payhere.seller_package_payment.cancel');

Route::any('/payhere/customer_package_payment/notify', 'PayhereController@customer_package_notify')->name('payhere.customer_package_payment.notify');
Route::any('/payhere/customer_package_payment/return', 'PayhereController@customer_package_return')->name('payhere.customer_package_payment.return');
Route::any('/payhere/customer_package_payment/cancel', 'PayhereController@customer_package_cancel')->name('payhere.customer_package_payment.cancel');

//N-genius
Route::any('ngenius/cart_payment_callback', 'NgeniusController@cart_payment_callback')->name('ngenius.cart_payment_callback');
Route::any('ngenius/wallet_payment_callback', 'NgeniusController@wallet_payment_callback')->name('ngenius.wallet_payment_callback');
Route::any('ngenius/customer_package_payment_callback', 'NgeniusController@customer_package_payment_callback')->name('ngenius.customer_package_payment_callback');
Route::any('ngenius/seller_package_payment_callback', 'NgeniusController@seller_package_payment_callback')->name('ngenius.seller_package_payment_callback');

//Custom page
Route::get('/{slug}', 'PageController@show_custom_page')->name('custom-pages.show_custom_page');

//12 may refund
Route::post('/refund-requests/{id}', 'RefundRequestController@refund_request_sends')->name('purchase_history.refund_request_sends');

Route::get('/refund-request-back/{id}', 'RefundRequestController@refund_request_send_back')->name('purchase_history.refund_request_send_back');
Route::get('/sorting-hub-refund-back/{id}', 'RefundRequestController@sorting_hub_refund_back')->name('purchase_history.sorting_hub_refund_back');

Route::any('letzpay/payment/response', 'LetzpayController@LetsPayResponse');

Route::get('razorpay/customer/create', 'RazorpayController@createCustomer');
Route::get('razorpay/customer/create/wallet', 'RazorpayController@createCustomerWallet');
Route::get('razorpay/payout', 'RazorpayController@createPartnerPayout');
Route::get('razorpay/create/contact/{id}', 'RazorpayController@createContact')->name('create.contact');
Route::get('razorpay/create/fund_account', 'RazorpayController@createFundAccount');


// Whats App Business Api Twillio
Route::group(['prefix' => 'whatsapp'], function() {
	Route::post('/incoming-message','WhatsappController@incomingMessageWebHook')->name('whatsapp.incoming-mesasge');
	Route::post('/callback-status','WhatsappController@callbackStatus')->name('whatsapp.callback_status');
	Route::get('/create_message','WhatsappController@notifyOrderOnDelivery');
	Route::get('/update_dist','ProductMapController@updateDist');
	Route::get('/update_discount','TestController@updateReferalDiscount');
	Route::get('/exportorders','TestController@orderExportWithDate');
});

// Gift Card
Route::group(['prefix' => 'gift-card','middleware'=>'user'], function() {
	Route::post('/save-address','AddressController@saveGiftAddress')->name('gift_card.save-address');
	// Route::get('/redeem-gift-card','WalletController@redeemGiftCardView')->name('redeem-gift-card');
	// Route::post('/redeem-gift-card','WalletController@redeemGiftCard')->name('redeem-gift-card.store');
});

Route::group(['prefix'=>'elastic-search'],function(){
	Route::get('/create-index','AddressController@addProductToElastic');
	Route::get('/store-in-final-products','SortingHubController@generateFinalProduct');
	//Route::get('/get-category-products','ElasticSearchController@getProductCategory');
	//Route::get('/get-a-doc','ElasticSearchController@getADocument');
	//Route::get('/search-product','ElasticSearchController@searchProduct');
});


Route::get('redis/create_final_order','RedisController@createFinalOrder');

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
