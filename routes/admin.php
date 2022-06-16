<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/dw', 'TestController@orderExport')->name('dw.export');
Route::get('/dwp', 'TestController@orderExportProductWise')->name('dwp.export');
Route::get('/dw/assignorders', 'TestController@orderExportAssignOrder')->name('dw.export_assign_orders');
Route::get('/admin', 'HomeController@admin_dashboard')->name('admin.dashboard')->middleware(['auth', 'admin']);
Route::post('/cities/get_mappedcity_by_state', 'UserMappingController@get_mapped_city_by_state_id')->name('cities.get_mapped_city_by_state_id');
Route::post('/area/get_area_by_city', 'UserMappingController@get_area_by_city_id')->name('area.get_area_by_city');
Route::post('get_cities_by_soroting_hub_id','UserMappingController@get_cities_by_soroting_hub_id')->name('get_cities_by_soroting_hub_id'); 

Route::post('get_cities_by_zone','UserMappingController@get_cities_by_zone')->name('get_cities_by_zone'); 
Route::post('get_pincode_by_city','UserMappingController@get_pincode_by_city')->name('get_pincode_by_city');

Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){

	Route::resource('mapping', 'UserMappingController');
	Route::post('/mapping/get_form', 'UserMappingController@get_mapping_form')->name('mapping.get_mapping_form');
	Route::post('/states/get_states_by_region', 'UserMappingController@get_state_by_region_id')->name('states.get_states_by_region');
	Route::post('/states/get_states_by_cluster', 'UserMappingController@get_state_by_cluster')->name('states.get_states_by_cluster');
	Route::post('/cities/get_city_by_state', 'UserMappingController@get_city_by_state_id')->name('cities.get_city_by_state');
	
	
	Route::post('/cluster/get_cluster_by_city', 'UserMappingController@get_cluster_by_city_id')->name('area.get_cluster_by_city_id');
	Route::post('/sorting_hub/get_sortinghub_by_cluster', 'UserMappingController@get_sortinghub_by_cluster_id')->name('sorting.get_sortinghub_by_cluster');
	Route::post('get_distributor_by_sortinghub', 'UserMappingController@get_distributor_by_sortinghub')->name('distributor.get_distributor_by_sorting_hub');

	Route::get('clone/distributor', 'DistributorController@clone_distributor')->name('clone.distributor');
	Route::post('clone/create/distributor', 'DistributorController@create_clone_distributor')->name('clone.create.distributor');
	Route::post('get-distributors-order-product','DistributorController@getDistributorsOrderProducts')->name('order-products.distributors');

	Route::resource('clusterhub', 'ClusterController');
	Route::post('/clusterhub/load_city','ClusterController@load_city')->name('clusterhub.load_city');
	Route::get('/cluster/destroy/{id}', 'ClusterController@destroy')->name('cluster.destroy');
	Route::post('/cluster/approve/', 'ClusterController@approve_cluster')->name('cluster.approved');
	Route::get('/cluster/login/{id}', 'ClusterController@login')->name('cluster.login');

	Route::resource('sorthinghub', 'SortingHubController');
	Route::get('/sorthinghub/destroy/{id}', 'SortingHubController@destroy')->name('sorthinghub.destroy');
	Route::post('/sorthinghub/approve/', 'SortingHubController@approve_sorting')->name('sorthinghub.approved');
	Route::get('/sorthinghub/login/{id}', 'SortingHubController@login')->name('sorthinghub.login');
	Route::post('/sorthinghub/assign_order', 'SortingHubController@assignOrder')->name('sorthinghub.assign_order');
	Route::get('/sortinghubbanner', 'SortingHubController@sortingHubBanner')->name('sorthinghub.sorting_banner');
	Route::post('/store/sortinghubbanner', 'SortingHubController@storeBanner')->name('sorthinghub.storeBanner');
	Route::get('/create/sortinghubbanner', 'SortingHubController@createBanner')->name('sorthinghub.createBanner');
	Route::get('/show/news', 'SortingHubController@showNews')->name('sorthinghub.news');
	Route::get('/create/news', 'SortingHubController@createNews')->name('sorthinghub.create_news');
	Route::post('/store/news', 'SortingHubController@storeNews')->name('sorthinghub.store_news');
	Route::get('/news/destroy/{id}', 'SortingHubController@destroyNews')->name('sorthinghub.destroy_news');
	Route::get('show/webp','TestController@showWebp')->name('webp.show');
	Route::post('convert/webp','TestController@uploadImage')->name('webp.convert');
	Route::get('/user-info','TestController@user_info');
	Route::post('/update-user','TestController@updateUserInfo')->name('bypass.update-user');

	//01-10-2021
	//Route::get('/sortinghubdiscountlist', 'SortingHubController@sortingDiscountList')->name('sortinghub.discount_list');
	Route::get('/peersettingupdate', 'SortingHubController@peersettingupdate')->name('sortinghub.peersettingupdate');

	Route::resource('distributor', 'DistributorController');
	Route::post('distributor/update/{id}', 'DistributorController@update')->name('distributor.update');
	Route::get('distributor/delete/{id}', 'DistributorController@destroy')->name('distributor.destroy');

	Route::resource('delivery_boy', 'DeliveryBoyController');
	Route::get('/delivery/orders', 'DeliveryBoyController@deliveryBoyOrders')->name('deliveryboy.order');
	Route::get('/deliveryboy/login/{id}', 'DeliveryBoyController@login')->name('deliveryboy.login');
	Route::get('/deliveryboy/destroy/{id}', 'DeliveryBoyController@destroy')->name('deliveryboy.destroy');
	Route::post('/deliveryboy/send-otp-verification', 'DeliveryBoyController@sendOtpVerification')->name('deliveryboy.sendotpverification');
	Route::post('/deliveryboy/verify-otp', 'DeliveryBoyController@verifyOTP')->name('deliveryboy.verifyOTP');

	//29 march neha
	Route::post('/set-cancel-otp', 'DeliveryBoyController@set_cancel_otp')->name('order.cancelorder');
	Route::post('/get-cancel-otp', 'DeliveryBoyController@get_cancel_otp')->name('order.getcancel_otp');

	Route::get('/delivery/export', 'DeliveryBoyController@delivery_export')->name('delivery_boy.delivery_export');


	Route::resource('product-mapping', 'ProductMapController');
	Route::get('mapped-product/list', 'ProductMapController@mapping_list')->name('mapped.product.list');
	Route::get('mapped-product/edit/{id}', 'ProductMapController@mapping_edit')->name('mapped.product.edit');
	Route::post('mapped-product/update/{id}', 'ProductMapController@mapping_update')->name('mapped.product.update');
	Route::post('mapped-product/modal', 'ProductMapController@product_map_modal')->name('mapped.product.modal');
	Route::get('mapped-product/destroy/{id}', 'ProductMapController@mapping_trash')->name('mapped.product.trash');
	Route::post('mapped-product/multiple-destroy', 'ProductMapController@multiple_mapping_trash')->name('mapped.product.multiple_trash');
	Route::post('mapped_product/delete','ProductMapController@delete_multiple')->name('mapped_product.delete');

	Route::any('mapping_categories','ProductMapController@categories')->name('sorting_hub.mapping_categories');
	Route::post('mapping_categories/update_status','ProductMapController@updateCategoryStatus')->name('sorting_hub.update_category_status');
	Route::get('mapping_categories/map_new_category','ProductMapController@mapCategories')->name('sorting_hub.map_new_categories');
	Route::post('mapping_categories/save_categories','ProductMapController@storeCategories')->name('sorting_hub.store_map_categories');
	
	
	Route::post('mapping/get_products', 'ProductMapController@get_product_by_category')->name('mapped.get_product_list');
	//17may2021
	Route::post('mapping/get_products_hub', 'ProductMapController@get_product_by_hub_category')->name('mapped.get_product_list_hub');
	Route::post('mapping/get_products_hub_discount', 'ProductMapController@get_product_by_hub_discount')->name('mapped.get_product_list_discount');

	#By Hasan
	Route::post('mapping-distributors','ProductMapController@map_distributors')->name('map.distributors');
	Route::post('store-mapped-distributors','ProductMapController@storeMappedDistributors')->name('store.mapped-distributors');

	Route::get('peer_partner/export', 'PeerPartnerController@export')->name('peer_partner.export');
	Route::get('peer_partner/allexport', 'PeerPartnerController@allexport')->name('peer_partner.allexport');
	Route::resource('peer_partner', 'PeerPartnerController');
	Route::post('/peer_partner/approved', 'PeerPartnerController@updateApproved')->name('peer_partner.approved');
	Route::post('/peer_partner/peerdiscount', 'PeerPartnerController@updatePeerDiscount')->name('peer_partner.peerdiscount');
	Route::post('peer_partner/get_peerdiscount', 'PeerPartnerController@get_peerdiscount')->name('peer.get_peerdiscount');
	Route::post('/peer_partner/profile_modal', 'PeerPartnerController@profile_modal')->name('peer_partner.profile_modal');
	Route::post('/peer_partner/approveds', 'PeerPartnerController@updatesubApproved')->name('peer_partner.subapproved');

	Route::get('/peer_partner/login/{id}', 'PeerPartnerController@login')->name('peer_partner.login');
	Route::get('peer_partner/{id}','PeerPartnerController@ban')->name('peer_partner.ban');
	Route::get('/peer_partner/destroy/{id}', 'PeerPartnerController@destroy')->name('peer_partner.destroy');

	Route::get('/create_partner_admin','PeerPartnerController@createPartnerByAdmin')->name('admin.create_partner');
	Route::post('/store_partner_admin','PeerPartnerController@storePartnerCreateByAdmin')->name('admin.store_partner');

	//12-11-2021
	Route::post('peer_partner/custom_code', 'PeerPartnerController@custom_code')->name('peer_partner.custom_code');
	Route::post('peer_partner/custom_code', 'PeerPartnerController@custom_code')->name('peer_partner.custom_code');
	Route::get('/payout_requests', 'WalletController@transferRequests')->name('peer_partner.payout_requests');
	Route::post('/payout_requests', 'WalletController@updatePayoutRequestStatus')->name('payout_requests.update_status');
	Route::post('/load_payout_requests', 'WalletController@loadPayoutRequest')->name('payout_requests.load_request');

	Route::get('/edit_partner_admin/{id}','PeerPartnerController@editPartnerByAdmin')->name('admin.edit_partner_admin');
	Route::post('/edit_partner_admin/update','PeerPartnerController@updatePartnerCreateByAdmin')->name('admin.update_partner_admin');

	Route::post('mapping/get_products_otp', 'PeerPartnerController@get_map_otp')->name('mapped.get_product_otp');
	Route::post('mapping/set_products_otp', 'PeerPartnerController@set_map_otp')->name('mapped.set_product_otp');

	Route::resource('categories','CategoryController');
	Route::get('/categories/destroy/{id}', 'CategoryController@destroy')->name('categories.destroy');
	Route::post('/categories/featured', 'CategoryController@updateFeatured')->name('categories.featured');
	Route::post('/categories/status', 'CategoryController@updateStatus')->name('categories.status');
	Route::resource('subcategories','SubCategoryController');
	Route::get('/subcategories/destroy/{id}', 'SubCategoryController@destroy')->name('subcategories.destroy');
	Route::get('/categories/download/{id}', 'CategoryController@downloadProducts')->name('categories.download');

	//Brand Slider
	Route::resource('brandslider','BrandSliderController');
	Route::get('/brandslider/destroy/{id}', 'BrandSliderController@destroy')->name('brandslider.destroy');
	Route::post('/brandslider/featured', 'BrandSliderController@updateFeatured')->name('brandslider.featured');
	Route::post('/brandslider/status', 'BrandSliderController@updateStatus')->name('brandslider.status');

	Route::resource('subsubcategories','SubSubCategoryController');
	Route::get('/subsubcategories/destroy/{id}', 'SubSubCategoryController@destroy')->name('subsubcategories.destroy');

	Route::resource('brands','BrandController');
	Route::get('/brands/destroy/{id}', 'BrandController@destroy')->name('brands.destroy');

	Route::get('/products/admin','ProductController@admin_products')->name('products.admin');
	Route::get('/products/seller','ProductController@seller_products')->name('products.seller');
	Route::get('/products/create','ProductController@create')->name('products.create');

	Route::get('/products/media','ProductController@admin_media')->name('products.media');
	Route::get('/products/createmedia','ProductController@createmedia')->name('products.createmedia');

    // 
	Route::post('/products/store','ProductController@store')->name('products.store');
	Route::post('/products/storemedia','ProductController@storemedia')->name('products.storemedia');
	// 

	Route::get('/products/admin/{id}/edit','ProductController@admin_product_edit')->name('products.admin.edit');
	Route::get('/products/seller/{id}/edit','ProductController@seller_product_edit')->name('products.seller.edit');
	Route::post('/products/todays_deal', 'ProductController@updateTodaysDeal')->name('products.todays_deal');
	Route::post('/products/search_status', 'ProductController@updateSearchStatus')->name('products.search_status');
	Route::post('/products/get_products_by_subcategory', 'ProductController@get_products_by_subcategory')->name('products.get_products_by_subcategory');

	Route::resource('sellers','SellerController');
	Route::get('sellers_ban/{id}','SellerController@ban')->name('sellers.ban');
	Route::get('/sellers/destroy/{id}', 'SellerController@destroy')->name('sellers.destroy');
	Route::get('/sellers/view/{id}/verification', 'SellerController@show_verification_request')->name('sellers.show_verification_request');
	Route::get('/sellers/approve/{id}', 'SellerController@approve_seller')->name('sellers.approve');
	Route::get('/sellers/reject/{id}', 'SellerController@reject_seller')->name('sellers.reject');
	Route::get('/sellers/login/{id}', 'SellerController@login')->name('sellers.login');
	Route::post('/sellers/payment_modal', 'SellerController@payment_modal')->name('sellers.payment_modal');
	Route::get('/seller/payments', 'PaymentController@payment_histories')->name('sellers.payment_histories');
	Route::get('/seller/payments/show/{id}', 'PaymentController@show')->name('sellers.payment_history');

	Route::resource('customers','CustomerController');
	Route::get('customers_ban/{customer}', 'CustomerController@ban')->name('customers.ban');
	Route::get('customers_peer_partner/{customer}', 'CustomerController@add_peer_partner')->name('customers.peer.partner');


	Route::resource('callcenter','CallCenterController'); 
	Route::get('operations','CallCenterController@Operations')->name('callceter.operationsuser');
	Route::get('callcenterall','CallCenterController@Callcenterall')->name('callceter.callcenterall');
	Route::post('isactive','CallCenterController@Isactive')->name('callceter.isactive');

	

	Route::post('addcustomer','CallCenterController@AddCustomer')->name('callceter.addcustomer');
	Route::post('addoperation','CallCenterController@Addoperation')->name('callceter.addoperation');

	Route::get('/customers/login/{id}', 'CustomerController@login')->name('customers.login');
	Route::get('/customers/destroy/{id}', 'CustomerController@destroy')->name('customers.destroy');

	Route::get('customers_peer_partner/{customer}', 'CustomerController@add_peer_partner')->name('customers.peer.partner');

	Route::get('/newsletter', 'NewsletterController@index')->name('newsletters.index');
	Route::post('/newsletter/send', 'NewsletterController@send')->name('newsletters.send');

	Route::resource('profile','ProfileController');

	Route::post('/business-settings/update', 'BusinessSettingsController@update')->name('business_settings.update');
	Route::post('/business-settings/update/activation', 'BusinessSettingsController@updateActivationSettings')->name('business_settings.update.activation');
	Route::get('/activation', 'BusinessSettingsController@activation')->name('activation.index');
	Route::get('/payment-method', 'BusinessSettingsController@payment_method')->name('payment_method.index');
	Route::get('/file_system', 'BusinessSettingsController@file_system')->name('file_system.index');
	Route::get('/social-login', 'BusinessSettingsController@social_login')->name('social_login.index');
	Route::get('/smtp-settings', 'BusinessSettingsController@smtp_settings')->name('smtp_settings.index');
	Route::get('/google-analytics', 'BusinessSettingsController@google_analytics')->name('google_analytics.index');
	Route::get('/google-recaptcha', 'BusinessSettingsController@google_recaptcha')->name('google_recaptcha.index');
	Route::get('/facebook-chat', 'BusinessSettingsController@facebook_chat')->name('facebook_chat.index');
	Route::post('/env_key_update', 'BusinessSettingsController@env_key_update')->name('env_key_update.update');
	Route::post('/payment_method_update', 'BusinessSettingsController@payment_method_update')->name('payment_method.update');
	Route::post('/google_analytics', 'BusinessSettingsController@google_analytics_update')->name('google_analytics.update');
	Route::post('/google_recaptcha', 'BusinessSettingsController@google_recaptcha_update')->name('google_recaptcha.update');
	Route::post('/facebook_chat', 'BusinessSettingsController@facebook_chat_update')->name('facebook_chat.update');
	Route::post('/facebook_pixel', 'BusinessSettingsController@facebook_pixel_update')->name('facebook_pixel.update');
	Route::get('/currency', 'CurrencyController@currency')->name('currency.index');
    Route::post('/currency/update', 'CurrencyController@updateCurrency')->name('currency.update');
    Route::post('/your-currency/update', 'CurrencyController@updateYourCurrency')->name('your_currency.update');
	Route::get('/currency/create', 'CurrencyController@create')->name('currency.create');
	Route::post('/currency/store', 'CurrencyController@store')->name('currency.store');
	Route::post('/currency/currency_edit', 'CurrencyController@edit')->name('currency.edit');
	Route::post('/currency/update_status', 'CurrencyController@update_status')->name('currency.update_status');
	Route::get('/verification/form', 'BusinessSettingsController@seller_verification_form')->name('seller_verification_form.index');
	Route::post('/verification/form', 'BusinessSettingsController@seller_verification_form_update')->name('seller_verification_form.update');
	Route::get('/vendor_commission', 'BusinessSettingsController@vendor_commission')->name('business_settings.vendor_commission');
	Route::post('/vendor_commission_update', 'BusinessSettingsController@vendor_commission_update')->name('business_settings.vendor_commission.update');

	Route::resource('/languages', 'LanguageController');
	Route::post('/languages/update_rtl_status', 'LanguageController@update_rtl_status')->name('languages.update_rtl_status');
	Route::get('/languages/destroy/{id}', 'LanguageController@destroy')->name('languages.destroy');
	Route::get('/languages/{id}/edit', 'LanguageController@edit')->name('languages.edit');
	Route::post('/languages/{id}/update', 'LanguageController@update')->name('languages.update');
	Route::post('/languages/key_value_store', 'LanguageController@key_value_store')->name('languages.key_value_store');

	Route::get('/frontend_settings/home', 'HomeController@home_settings')->name('home_settings.index');
	Route::post('/frontend_settings/home/top_10', 'HomeController@top_10_settings')->name('top_10_settings.store');
	Route::get('/sellerpolicy/{type}', 'PolicyController@index')->name('sellerpolicy.index');
	Route::get('/returnpolicy/{type}', 'PolicyController@index')->name('returnpolicy.index');
	Route::get('/supportpolicy/{type}', 'PolicyController@index')->name('supportpolicy.index');
	Route::get('/terms/{type}', 'PolicyController@index')->name('terms.index');
	Route::get('/privacypolicy/{type}', 'PolicyController@index')->name('privacypolicy.index');

	//Policy Controller
	Route::post('/policies/store', 'PolicyController@store')->name('policies.store');

	Route::group(['prefix' => 'frontend_settings'], function(){
		Route::resource('sliders','SliderController');
		Route::any('sliders/update/{id}','SliderController@update_slider')->name('sliders.update_slider');
	    Route::get('/sliders/destroy/{id}', 'SliderController@destroy')->name('sliders.destroy');

	    // Banner Slider
	    Route::resource('banner_sliders','BannerSliderController');
	    Route::post('/banner_sliders/update_status', 'BannerSliderController@update_status')->name('banner_sliders.update_status');
	    Route::get('/banner_sliders/destroy/{id}', 'BannerSliderController@destroy')->name('banner_sliders.destroy');

		//Service Not Available Banner
		Route::resource('serviceNotAvailable','ServiceNotAvailable');
		Route::resource('uploads','UploadController');
		Route::get('uploads/destroy/{id}','UploadController@destroy')->name('uploads.destroy');

	    //Master Banner
	    Route::resource('master_banners','MasterBannerController');
	    Route::get('/master_banners/destroy/{id}', 'MasterBannerController@destroy')->name('master_banners.destroy');
	    Route::post('/master_banners/update_status', 'MasterBannerController@update_status')->name('master_banners.update_status');

	    Route::resource('finance_banner','FinanceBannerController');
	    Route::post('/finance_banner/update_status', 'FinanceBannerController@update_status')->name('finance_banner.update_status');
	    Route::get('/finance_banner/destroy/{id}', 'FinanceBannerController@destroy')->name('finance_banner.destroy');

		Route::resource('home_banners','BannerController');
		Route::get('/home_banners/create/{position}', 'BannerController@create')->name('home_banners.create');
		Route::post('/home_banners/update_status', 'BannerController@update_status')->name('home_banners.update_status');
	    Route::get('/home_banners/destroy/{id}', 'BannerController@destroy')->name('home_banners.destroy');

		Route::resource('home_categories','HomeCategoryController');
	    Route::get('/home_categories/destroy/{id}', 'HomeCategoryController@destroy')->name('home_categories.destroy');
		Route::post('/home_categories/update_status', 'HomeCategoryController@update_status')->name('home_categories.update_status');
		Route::post('/home_categories/get_subsubcategories_by_category', 'HomeCategoryController@getSubSubCategories')->name('home_categories.get_subsubcategories_by_category');
	});

	Route::resource('roles','RoleController');
    Route::get('/roles/destroy/{id}', 'RoleController@destroy')->name('roles.destroy');

    Route::resource('staffs','StaffController');
    Route::get('/staffs/destroy/{id}', 'StaffController@destroy')->name('staffs.destroy');

	Route::resource('DOFO','DOFOController');
	Route::get('/DOFO/destroy/{id}', 'DOFOController@destroy')->name('DOFO.destroy');
	Route::post('/DOFO/status', 'DOFOController@updateStatus')->name('DOFO.status');
	Route::get('/dofo_orders', 'DOFOController@dofoOrder')->name('DOFO.orders');
	Route::post('/bulk-dofo-users', 'DOFOController@UploadDOFOUsers')->name('DOFO.upload');
	Route::post('/upload-dofo-peer-partner','DOFOController@uploadPeerPartner')->name('DOFO.upload-peer');
	Route::post('/change_purpose_status','DOFOController@changeStatusPurpose')->name('DOFO.change-purpose');

	Route::get('replacemet-requests','ReplacementController@replacement_requests')->name('admin.replacement');
	Route::post('approve-replacement-request','ReplacementController@approve_replacement_request')->name('approve.replacement');
	Route::post('replacement/assign_order','ReplacementController@assign_order')->name('replacement.assign_order');

	Route::get('/create-dofo-order','DOFOController@createDofoOrders')->name('DOFO.create-order');
	Route::post('/store-dofo-order','DOFOController@storeDofoOrders')->name('DOFO.store-order');
	Route::post('/addcart-dofo-order','DOFOController@addToCartDofoOrders')->name('DOFO.cart-dofo-order');
	Route::get('/dofo-user-detail/{email}','DOFOController@getUserDetail')->name('DOFO.user-detail');
	Route::post('dofo/get_products_hub', 'DOFOController@getProductBySortingHub')->name('DOFO.sortinghub_product');
	Route::get('/dofo-orders/download','DOFOController@exportDofoOrders')->name('DOFO.orders-download');
	Route::get('/dofo-orders/access-switch','DOFOController@accessSwitch')->name('DOFO.access-switch');
	Route::post('/dofo/change-access-switch', 'DOFOController@changeAccessSwitch')->name('DOFO.change-access-switch');
	Route::post('/dofo/change-global-access-switch', 'DOFOController@changeGlobalAccessSwitch')->name('DOFO.change-global-access-switch');
	Route::post('/dofo/update-old-commission', 'DOFOController@updateOldCommission')->name('DOFO.update-old-commission');
	Route::post('/dofo/upload-orders', 'DOFOController@uploadDofoOrders')->name('DOFO.upload-orders');
	Route::post('/dofo/test-excel-order', 'DOFOController@testExcelDofoOrders')->name('DOFO.test-excel-order');
	Route::get('/dofo/delivery_boy','DOFOController@getDofoDeliveryBoy')->name('DOFO.delivery-boy');
	Route::get('/dofo/create_delivery_boy','DOFOController@createDofoDeliveryBoy')->name('DOFO.create-delivery-boy');
	Route::post('/dofo/get_sorting_hub','DOFOController@getSortingHub')->name('DOFO.get-sorting-hub');
	Route::post('/dofo/store/delivery_boy','DOFOController@storeDofoDeliveryBoy')->name('DOFO.store-delivery-boy');
	Route::post('/dofo/upload/delivery_boy','DOFOController@uploadDeliveryBoy')->name('DOFO.upload-delivery-boy');
	Route::post('/dofo/delete-order','DOFOController@deleteDofoOrders')->name('DOFO.delete-order');
	Route::get('/dofo/download-cron-orders','DOFOController@cronDownloadOrders');
	Route::post('/upload-csv-order','DOFOController@uploadCSVorders')->name('DOFO.upload-csv-order');
	Route::get('/create-csv-order','DOFOController@csvJobsOrders');
	Route::get('/show-csv-order','DOFOController@showCSVorders')->name('DOFO.show-csv-order');

	Route::resource('flash_deals','FlashDealController');
    Route::get('/flash_deals/destroy/{id}', 'FlashDealController@destroy')->name('flash_deals.destroy');
	Route::post('/flash_deals/update_status', 'FlashDealController@update_status')->name('flash_deals.update_status');
	Route::post('/flash_deals/update_featured', 'FlashDealController@update_featured')->name('flash_deals.update_featured');
	Route::post('/flash_deals/product_discount', 'FlashDealController@product_discount')->name('flash_deals.product_discount');
	Route::post('/flash_deals/product_discount_edit', 'FlashDealController@product_discount_edit')->name('flash_deals.product_discount_edit');
	//new
	Route::post('/flash_deals/products', 'FlashDealController@flashDealProducts')->name('flash_deals.products');
	Route::get('/flash_deals_product_list','FlashDealController@flash_deal_product_list')->name('admin.flash_deal.product_list');
	Route::get('/remove_from_flash_deal/{sorting_hub}/{product_id}','FlashDealController@removeProductFlashDeal')->name('flash_deals.remove.product');

	Route::get('/finalordershtml', 'OrderController@html_data_table')->name('orders.finalordershtml');
	Route::get('/orders/{id}/showhtml', 'OrderController@showhtml')->name('orders.showhtml');

	Route::get('/orders', 'OrderController@admin_orders')->name('orders.index.admin');
	Route::get('/orders/{unpaid_online}', 'OrderController@admin_orders')->name('orders.unpaid.online');
	Route::get('/today_orders', 'OrderController@sortinghub_today_orders')->name('orders.index.sortinghub');
	Route::get('/orders/{id}/show', 'OrderController@show')->name('orders.show');
	Route::get('/sales/{id}/show', 'OrderController@sales_show')->name('sales.show');
	Route::get('/orders/destroy/{id}', 'OrderController@destroy')->name('orders.destroy');
	Route::get('/sales', 'OrderController@sales')->name('sales.index');
	Route::get('/remove-order-product/{id}', 'OrderController@removeOrderProduct')->name('orders.removeorderproduct');

	//Archived Order START
    Route::get('/order/archived','OrderArchiveController@archiveOrders')->name('archived.orders');
    //Archived Order END
    //Razorpayx start
	Route::get('/razorpay/getcontactlist','TransferMoneyController@viewContactInfo')->name('razorpayx.getcontactlist');
	Route::get('/razorpay/addaccount/{cont_id}','TransferMoneyController@addAccount')->name('razorpayx.addAccount');
	Route::get('/razorpay/transfermoney/{cont_id}','TransferMoneyController@transferMoney')->name('razorpayx.transfermoney');
	//Razorpayx end

	Route::get('/order/assign','AssignOrderController@assignOrderList')->name('assign.orders');
	
	Route::get('/recurringorders', 'OrderController@admin_recurring_orders')->name('orders.recurring.admin');
	Route::get('/recurringrefunds', 'OrderController@admin_recurring_refunds')->name('orders.recurringrefund.admin');
	Route::get('/admin/refundrecurring/{id}', 'OrderController@transfer_amount')->name('orders.transfer');

	Route::get('/productorders', 'OrderController@admin_productorders')->name('orders.index.adminproduct');

	// 27-09-2021
	Route::get('/referralorders', 'OrderController@admin_referralorders')->name('orders.index.adminreferral');

	//21may2021
	Route::get('/distributororders/{id}/show', 'OrderController@show_distributor_order')->name('distributororders.show');
	Route::post('/distributororders/{id}/show', 'OrderController@show_distributor_order_by_date')->name('distributororders.showbydate');
	Route::any('/sortinghuborders/show', 'SortingHubController@sortingHubPurchaseReport')->name('sortinghuborders.showbydate');

	Route::get('/peer_commission/{id}/show', 'OrderController@show_peer_commission')->name('peer_commission.show');
	Route::post('/peer_commission/{id}/show', 'OrderController@show_peer_commission_by_date')->name('peer_commission.showbydate');
	Route::post('/load_slot','OrderController@load_slots')->name('order.load_slots');
	Route::resource('links','LinkController');
	Route::get('/links/destroy/{id}', 'LinkController@destroy')->name('links.destroy');

	Route::resource('generalsettings','GeneralSettingController');
	Route::get('/logo','GeneralSettingController@logo')->name('generalsettings.logo');
	Route::post('/logo','GeneralSettingController@storeLogo')->name('generalsettings.logo.store');
	Route::get('/color','GeneralSettingController@color')->name('generalsettings.color');
	Route::post('/color','GeneralSettingController@storeColor')->name('generalsettings.color.store');

	Route::resource('seosetting','SEOController');

	Route::post('/pay_to_seller', 'CommissionController@pay_to_seller')->name('commissions.pay_to_seller');

	//Reports
	Route::get('/stock_report', 'ReportController@stock_report')->name('stock_report.index');
	Route::get('/in_house_sale_report', 'ReportController@in_house_sale_report')->name('in_house_sale_report.index');
	Route::get('/seller_report', 'ReportController@seller_report')->name('seller_report.index');
	Route::get('/seller_sale_report', 'ReportController@seller_sale_report')->name('seller_sale_report.index');
	Route::get('/wish_report', 'ReportController@wish_report')->name('wish_report.index');

	//25-11-2021
	Route::get('/sku_data', 'ReportController@sku_data_report')->name('sku_data.index');
	Route::get('/sale_data', 'ReportController@sale_data_report')->name('sale_data.index');
	Route::get('/sku_data/export', 'ReportController@sku_data_export')->name('report.sku_data_export');
	Route::get('/sale_data/export', 'ReportController@sale_data_export')->name('report.sale_data_export');
	Route::get('/invoice_data', 'ReportController@invoice_data_report')->name('invoice_data.index');
	Route::post('/invoice/all', 'InvoiceController@all_invoice_download')->name('all_invoice.download');

	Route::get('/peerpartner_data', 'ReportController@peerpartner_data_report')->name('peerpartner_data.index');
	Route::get('/peer_commissions/{id}/show', 'ReportController@show_masterpeer_commission')->name('peer_commissions.show');
	Route::post('/peer_commissions/{id}/show', 'ReportController@show_masterpeer_commission_by_date')->name('peer_commissions.showbydate');
	Route::get('/peers_commissions/{id}/show', 'ReportController@show_peers_commission')->name('peers_commissions.show');
	Route::post('/peers_commissions/{id}/show', 'ReportController@show_peers_commission_by_date')->name('peers_commissions.showbydate');
	Route::get('/customerpeer_commissions/{id}/show', 'ReportController@show_customerpeer_commission')->name('customerpeer_commissions.show');
	Route::post('/customerpeer_commissions/{id}/show', 'ReportController@show_customerpeer_commission_by_date')->name('customerpeer_commissions.showbydate');

	Route::get('/no_of_order_report', 'RealdataController@no_of_order_report')->name('no_of_order.index');

	//Coupons
	Route::resource('coupon','CouponController');
	Route::post('/coupon/get_form', 'CouponController@get_coupon_form')->name('coupon.get_coupon_form');
	Route::post('/coupon/get_form_edit', 'CouponController@get_coupon_form_edit')->name('coupon.get_coupon_form_edit');
	Route::get('/coupon/destroy/{id}', 'CouponController@destroy')->name('coupon.destroy');

	//Reviews
	Route::get('/reviews', 'ReviewController@index')->name('reviews.index');
	Route::post('/reviews/published', 'ReviewController@updatePublished')->name('reviews.published');

	//Support_Ticket
	Route::get('support_ticket/','SupportTicketController@admin_index')->name('support_ticket.admin_index');
	Route::get('support_ticket/{id}/show','SupportTicketController@admin_show')->name('support_ticket.admin_show');
	Route::post('support_ticket/reply','SupportTicketController@admin_store')->name('support_ticket.admin_store');

	//Pickup_Points
	Route::resource('pick_up_points','PickupPointController');
	Route::get('/pick_up_points/destroy/{id}', 'PickupPointController@destroy')->name('pick_up_points.destroy');


	Route::get('orders_by_pickup_point','OrderController@order_index')->name('pick_up_point.order_index');
	Route::get('/orders_by_pickup_point/{id}/show', 'OrderController@pickup_point_order_sales_show')->name('pick_up_point.order_show');

	Route::get('invoice/admin/{order_id}', 'InvoiceController@admin_invoice_download')->name('admin.invoice.download');

	//conversation of seller customer
	Route::get('conversations','ConversationController@admin_index')->name('conversations.admin_index');
	Route::get('conversations/{id}/show','ConversationController@admin_show')->name('conversations.admin_show');

    Route::post('/sellers/profile_modal', 'SellerController@profile_modal')->name('sellers.profile_modal');
    Route::post('/sellers/approved', 'SellerController@updateApproved')->name('sellers.approved');

	Route::resource('attributes','AttributeController');
	Route::get('/attributes/destroy/{id}', 'AttributeController@destroy')->name('attributes.destroy');


	Route::get('/attributes/attributeval/{id}', 'AttributeController@attributeadd')->name('attributes.attributeadd');
	Route::post('/attributes/storeoptionval/{id}', 'AttributeController@storeoptionval')->name('attributes.storeoptionval');
	Route::get('/attributes/editoptionattribute/{id}', 'AttributeController@editoptionattribute')->name('attributes.editoptionattribute');
	Route::post('/attributes/updateoptionattribute/{id}', 'AttributeController@updateoptionattribute')->name('attributes.updateoptionattribute');
	Route::get('/attributes/distroyoptionattribute/{id}', 'AttributeController@distroyoptionattribute')->name('attributes.distroyoptionattribute');

	Route::post('/attributes/check', 'AttributeController@attributesCheck')->name('attributes.check');


	Route::get('/products/getlist', 'ProductController@getlist')->name('products.getlist');
	Route::resource('addons','AddonController');
	Route::post('/addons/activation', 'AddonController@activation')->name('addons.activation');

	Route::get('/customer-bulk-upload/index', 'CustomerBulkUploadController@index')->name('customer_bulk_upload.index');
	Route::post('/bulk-user-upload', 'CustomerBulkUploadController@user_bulk_upload')->name('bulk_user_upload');
	Route::post('/bulk-customer-upload', 'CustomerBulkUploadController@customer_bulk_file')->name('bulk_customer_upload');
	Route::get('/user', 'CustomerBulkUploadController@pdf_download_user')->name('pdf.download_user');
	//Customer Package
	Route::resource('customer_packages','CustomerPackageController');
	Route::get('/customer_packages/destroy/{id}', 'CustomerPackageController@destroy')->name('customer_packages.destroy');
	//Classified Products
	Route::get('/classified_products', 'CustomerProductController@customer_product_index')->name('classified_products');
	Route::post('/classified_products/published', 'CustomerProductController@updatePublished')->name('classified_products.published');

	//Shipping Configuration
	Route::get('/shipping_configuration', 'BusinessSettingsController@shipping_configuration')->name('shipping_configuration.index');
	Route::post('/shipping_configuration/update', 'BusinessSettingsController@shipping_configuration_update')->name('shipping_configuration.update');

	Route::resource('pages', 'PageController');
	Route::get('/pages/destroy/{id}', 'PageController@destroy')->name('pages.destroy');

	Route::resource('countries','CountryController');
	Route::post('/countries/status', 'CountryController@updateStatus')->name('countries.status');
	Route::get('/search_history_list','SearchController@search_list')->name('search_history.list');
	Route::post('/download_csv','SearchController@downloadCSV')->name('search_history.download');
	Route::post('/delete_search','SearchController@delete_search')->name('search_history.delete');
	Route::resource('sortingmanager','SortingHubManagerController');
	Route::get('/sortingmanager/login/{id}', 'SortingHubManagerController@login')->name('sortingmanager.login');
	Route::get('/sortingmanager/destroy/{id}', 'SortingHubManagerController@destroy')->name('sortingmanager.destroy');
	Route::post('/download_file_product_by_category','ProductMapController@download_file_product_by_category')->name('sorting_hub.download_file_product_by_category');
	Route::post('/download_file_product_by_sku','ProductMapController@download_file_product_by_sku')->name('sorting_hub.download_file_product_by_sku');

	Route::get('/add_to_order/{id}','OrderController@addToOrder')->name('admin.add_to_order');
	Route::post('/products_add_to_order','OrderController@get_product_by_hub_category')->name('admin.products_add_to_order');
	Route::post('/products_added_to_order','OrderController@load_products_added_to_order')->name('admin.load_products_added_to_order');
	Route::post('/store_added_to_order','OrderController@storeAddToOrder')->name('admin.store_added_to_order');
	
	//25-09-2021
	Route::get('/orders/export', 'OrderController@orders_export')->name('orders.index.exportproduct');
	Route::get('/orders/productexport', 'OrderController@orders_productexport')->name('orders.index.exportproductwise');
	Route::get('/export/orderbydate','TestController@orders_export')->name('export.orders');
	Route::get('/export/orderbyproductwise','TestController@orders_productexport')->name('export.ordersbyproductwise');
	Route::get('/finalorders/export', 'OrderController@final_orders_export')->name('finalorders.exportproduct');
	Route::get('/finalorders/productexportfinal', 'OrderController@orders_productexport_final')->name('finalorders.exportproductwise');
	// recurring
	Route::get('/orders/recurringexport', 'OrderController@orders_recurring_export')->name('orders.index.recurring_exportproduct');

	//27-09-2021
	Route::get('/orders/allexport', 'OrderController@orders_export_all')->name('orders.index.exportallproduct');

	//12-11-2021
	Route::get('/opening', 'OpeningController@index')->name('opening.index');
	Route::get('/opening/delete/{id}', 'OpeningController@delete_opening')->name('opening.delete');
	Route::get('/opening/create', 'OpeningController@create')->name('opening.create');
	Route::post('/opening/store', 'OpeningController@store')->name('opening.store');
	Route::get('/opening/edit/{id}', 'OpeningController@edit')->name('opening.edit');
	Route::post('/opening/update/{id}', 'OpeningController@update')->name('opening.update');

	//Razorpay Cron
	Route::get('/check-order-payment','RazorpayController@checkRazorpayOrderPayment');

	//Delivery Slot
	Route::get('/delivery/slot/details','DeliverySlotController@index')->name('deliveryslot.index');
	Route::get('/delivery/slot/delete/{id}','DeliverySlotController@deleteSlot')->name('deliveryslot.delete');
	Route::get('/delivery/slot/create','DeliverySlotController@createDeliverySlot')->name('deliveryslot.create');
	Route::post('/delivery/slot/store','DeliverySlotController@storeSlot')->name('deliveryslot.store');
	Route::get('/delivery/slot/edit/{id}','DeliverySlotController@editSlot')->name('deliveryslot.edit');
	Route::post('/delivery/slot/update','DeliverySlotController@updateSlot')->name('deliveryslot.updateSlot');
	Route::post('/generate/report','ReportController@generateReport')->name('report.generate');


	//Order According to Order Status
	Route::get('/orders/{order_status}/{order_status_id}','OrderStatusController@newOrders')->name('orders.new-orders');
	Route::post('/orders/change_order_status','OrderStatusController@changeOrderStatus')->name('orders.change-order-status');
	Route::post('/orders/assign-suborder/','OrderStatusController@assignOrder')->name('orders.assign-sub-order');
	Route::get('/invoice/suborder/{order_id}', 'InvoiceController@subOrderInvoiceDownload')->name('orders.suborderinvoice.download');

});
