<!--MAIN NAVIGATION-->
<!--===================================================-->
<nav id="mainnav-container">
    <div id="mainnav">

        <!--Menu-->
        <!--================================-->
        <div id="mainnav-menu-wrap">
            <div class="nano">
                <div class="nano-content">
                    <!--Shortcut buttons-->
                    <!--================================-->
                    <div id="mainnav-shortcut" class="hidden">
                        <ul class="list-unstyled shortcut-wrap">
                            <li class="col-xs-3" data-content="My Profile">
                                <a class="shortcut-grid" href="#">
                                    <div class="icon-wrap icon-wrap-sm icon-circle bg-mint">
                                    <i class="demo-pli-male"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="col-xs-3" data-content="Messages">
                                <a class="shortcut-grid" href="#">
                                    <div class="icon-wrap icon-wrap-sm icon-circle bg-warning">
                                    <i class="demo-pli-speech-bubble-3"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="col-xs-3" data-content="Activity">
                                <a class="shortcut-grid" href="#">
                                    <div class="icon-wrap icon-wrap-sm icon-circle bg-success">
                                    <i class="demo-pli-thunder"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="col-xs-3" data-content="Lock Screen">
                                <a class="shortcut-grid" href="#">
                                    <div class="icon-wrap icon-wrap-sm icon-circle bg-purple">
                                    <i class="demo-pli-lock-2"></i>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!--================================-->
                    <!--End shortcut buttons-->


                    <ul id="mainnav-menu" class="list-group">

                        <!--Category name-->
                        {{-- <li class="list-header">Navigation</li> --}}

                        <!--Menu list item-->
                        <li class="{{ areActiveRoutes(['admin.dashboard'])}}">
                            <a class="nav-link" href="{{route('admin.dashboard')}}">
                                <i class="fa fa-home"></i>
                                <span class="menu-title">{{translate('Dashboard')}}</span>
                            </a>
                        </li>

                        @if (\App\Addon::where('unique_identifier', 'pos_system')->first() != null && \App\Addon::where('unique_identifier', 'pos_system')->first()->activated)

                            <li>
                                <a href="#">
                                    <i class="fa fa-print"></i>
                                    <span class="menu-title">{{translate('POS Manager')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['poin-of-sales.index', 'poin-of-sales.create'])}}">
                                        <a class="nav-link" href="{{route('poin-of-sales.index')}}">{{translate('POS Manager')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['poin-of-sales.activation'])}}">
                                        <a class="nav-link" href="{{route('poin-of-sales.activation')}}">{{translate('POS Configuration')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        <!-- Product Menu -->
                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('1', json_decode(Auth::user()->staff->role->permissions)))
                            <li>
                                <a href="#">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span class="menu-title">{{translate('Products')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['brands.index', 'brands.create', 'brands.edit'])}}">
                                        <a class="nav-link" href="{{route('brands.index')}}">{{translate('Brand')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['categories.index', 'categories.create', 'categories.edit'])}}">
                                        <a class="nav-link" href="{{route('categories.index')}}">{{translate('Category')}}</a>
                                    </li>

                                    <li class="{{ areActiveRoutes(['subcategories.index', 'subcategories.create', 'subcategories.edit'])}}">
                                        <a class="nav-link" href="{{route('subcategories.index')}}">{{translate('Subcategory')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['subsubcategories.index', 'subsubcategories.create', 'subsubcategories.edit'])}}">
                                        <a class="nav-link" href="{{route('subsubcategories.index')}}">{{translate('Sub Subcategory')}}</a>
                                    </li>
                                      <li class="{{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                    <a class="nav-link" href="{{route('attributes.index')}}">{{translate('Attribute')}}</a>
                                </li>
                                    <li class="{{ areActiveRoutes(['products.admin', 'products.create', 'products.admin.edit'])}}">
                                        <a class="nav-link" href="{{route('products.admin')}}">{{translate('In House Products')}}</a>
                                    </li>
                                    @if(\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
                                        <li class="{{ areActiveRoutes(['products.seller', 'products.seller.edit'])}}">
                                            <a class="nav-link" href="{{route('products.seller')}}">{{translate('Seller Products')}}</a>
                                        </li>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'classified_product')->first()->value == 1)
                                        <li class="{{ areActiveRoutes(['classified_products'])}}">
                                            <a class="nav-link" href="{{route('classified_products')}}">{{translate('Classified Products')}}</a>
                                        </li>
                                    @endif
                                    <li class="{{ areActiveRoutes(['digitalproducts.index', 'digitalproducts.create', 'digitalproducts.edit'])}}">
                                        <a class="nav-link" href="{{route('digitalproducts.index')}}">{{translate('Digital Products')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['product_bulk_upload.index'])}}">
                                        <a class="nav-link" href="{{route('product_bulk_upload.index')}}">{{translate('Bulk Import')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['product_bulk_export.export'])}}">
                                        <a class="nav-link" href="{{route('product_bulk_export.index')}}">{{translate('Bulk Export')}}</a>
                                    </li>
                                    @php
                                        $review_count = 2;
                                    @endphp
                                    <li class="{{ areActiveRoutes(['reviews.index'])}}">
                                        <a class="nav-link" href="{{route('reviews.index')}}">{{translate('Product Reviews')}}@if($review_count > 0)<span class="pull-right badge badge-info">{{ $review_count }}</span>@endif</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['products.admin', 'products.media', 'products.admin.edit'])}}">
                                        <a class="nav-link" href="{{ route('products.media')}}">{{translate('Upload Media')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('24', json_decode(Auth::user()->staff->role->permissions)))
                            <li>
                                <a href="#">
                                    <i class="fa fa-server"></i>
                                    <span class="menu-title">{{translate('Manage Cluster Hubs')}}</span>
                                    <i class="arrow"></i>
                                </a>
                                <!--Submenu-->
                                <ul class="collapse">
                                   <li class="{{ areActiveRoutes(['clusterhub.index', 'clusterhub.create', 'clusterhub.edit'])}}">
                                    <a class="nav-link" href="{{ route('clusterhub.index') }}">
                                     
                                        <span class="menu-title">{{translate('Clusters List')}}</span>
                                    </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('23', json_decode(Auth::user()->staff->role->permissions)))
                            <li>
                                <a href="#">
                                   <i class="fa fa-clone"></i>
                                    <span class="menu-title">{{translate('Manage Sorting Hubs')}}</span>
                                    <i class="arrow"></i>
                                </a>
                                <!--Submenu-->
                                <ul class="collapse">
                                   <li class="{{areActiveRoutes(['sorthinghub.index', 'sorthinghub.create', 'sorthinghub.edit'])}}">
                                    <a class="nav-link" href="{{ route('sorthinghub.index') }}">
                                        <span class="menu-title">{{translate('Sorting hub List')}}</span>
                                    </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('22', json_decode(Auth::user()->staff->role->permissions)))
                            <li>
                                <a href="#">
                                   <i class="fa fa-truck"></i>
                                    <span class="menu-title">{{translate('Manage Distributors')}}</span>
                                    <i class="arrow"></i>
                                </a>
                                <!--Submenu-->
                                <ul class="collapse">
                                   <li class="{{ areActiveRoutes(['distributor.index', 'distributor.create'])}}">
                                    <a class="nav-link" href="{{route('distributor.index')}}">
                                        <span class="menu-title">{{translate('Distributors List')}}</span>
                                    </a>
                                    </li>
                                    @if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Sorting Hub")
                                     <li class="{{ areActiveRoutes(['clone.distributor'])}}">
                                    <a class="nav-link" href="{{route('clone.distributor')}}">
                                        <span class="menu-title">{{translate('Clone Distributor')}}</span>
                                    </a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                        @endif


                        

                        @if(!in_array(Auth::user()->user_type, ['admin']))
                            @if(in_array('20', json_decode(Auth::user()->staff->role->permissions)))
                                <li>
                                    <a href="#">
                                       <i class="fa fa-cog"></i>
                                        <span class="menu-title">{{translate('Manage Product Mapping')}}</span>
                                        <i class="arrow"></i>
                                    </a>
                                    <!--Submenu-->
                                    <ul class="collapse">
                                    <li class="{ areActiveRoutes(['mapped.product.list', 'mapped.product.edit', 'product-mapping.create'])}}">
                                        <a class="nav-link" href="{{route('sorting_hub.mapping_categories')}}">
                                            <span class="menu-title">{{translate('Category List')}}</span>
                                        </a>
                                        </li>
                                       <li class="{ areActiveRoutes(['mapped.product.list', 'mapped.product.edit', 'product-mapping.create'])}}">
                                        <a class="nav-link" href="{{route('mapped.product.list')}}">
                                            <span class="menu-title">{{translate('Product List')}}</span>
                                        </a>
                                        </li>

                                        <li class="{{ areActiveRoutes(['productmap_bulk_upload.index'])}}">
                                        <a class="nav-link" href="{{route('productmap_bulk_upload.index')}}">{{translate('Product Import')}}</a>
                                    </li>
                                    
                                    </ul>
                                </li>
                            @endif
                        @endif


                        @if(!in_array(Auth::user()->user_type, ['admin']))
                            @if(in_array('20', json_decode(Auth::user()->staff->role->permissions)) || Auth::user()->staff->role->name=="Sorting Hub Manager")
                                <li>
                                    <a href="#">
                                       <i class="fa-users"></i>
                                        <span class="menu-title">{{translate('Manage Delivery Boy')}}</span>
                                        <i class="arrow"></i>
                                    </a>
                                    <!--Submenu-->
                                    <ul class="collapse">
                                       <li class="{ areActiveRoutes(['delivery_boy.index', 'delivery_boy.create', 'delivery_boy.edit'])}}">
                                        <a class="nav-link" href="{{route('delivery_boy.index')}}">
                                            <span class="menu-title">{{translate('Delivery Boy List')}}</span>
                                        </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                <a href="{{route('orders.index.sortinghub')}}">
                                       <i class="fa-users"></i>
                                        <span class="menu-title">{{translate('Today Orders')}}</span>
                                        
                                    </a>
                                </li>
                            @endif
                        @endif

                        @if(!in_array(Auth::user()->user_type, ['admin']))
                            @if(in_array('20', json_decode(Auth::user()->staff->role->permissions)))
                            <li>
                                    <a href="#">
                                       <i class="fa-users"></i>
                                        <span class="menu-title">{{translate('Manage Sorting Manager')}}</span>
                                        <i class="arrow"></i>
                                    </a>
                                    <!--Submenu-->
                                    <ul class="collapse">
                                       <li class="{ areActiveRoutes(['sortingmanager.index', 'sortingmanager.create', 'sortingmanager.edit'])}}">
                                        <a class="nav-link" href="{{route('sortingmanager.index')}}">
                                            <span class="menu-title">{{translate('Sorting Manager List')}}</span>
                                        </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#">
                                       <i class="fa-users"></i>
                                        <span class="menu-title">{{translate('Banner Setting')}}</span>
                                        <i class="arrow"></i>
                                    </a>
                                    <!--Submenu-->
                                    <ul class="collapse">
                                       <li class="{ areActiveRoutes(['sorthinghub.sorting_banner'])}}">
                                        <a class="nav-link" href="{{route('sorthinghub.sorting_banner')}}">
                                            <span class="menu-title">{{translate('Home Banner')}}</span>
                                        </a>
                                        </li>

                                        <li class="{ areActiveRoutes(['sorthinghub.news'])}}">
                                        <a class="nav-link" href="{{route('sorthinghub.news')}}">
                                            <span class="menu-title">{{translate('News')}}</span>
                                        </a>
                                        </li>
                                    </ul>
                                </li>
                                
                            @endif
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('18', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span class="menu-title">{{translate('Manage Peer Partners')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                               <li class="{{ areActiveRoutes(['peer_partner.index'])}}">
                                <a class="nav-link" href="{{ route('peer_partner.index') }}">
                                   
                                    <span class="menu-title">{{translate('Partner list')}}</span>
                                </a>
                            </li>
                            <li class="{{ areActiveRoutes(['admin.create_partner'])}}">
                                <a class="nav-link" href="{{ route('admin.create_partner') }}">
                                   
                                    <span class="menu-title">{{translate('Create Partner')}}</span>
                                </a>
                            </li>
                            <li class="{{ areActiveRoutes(['peer_partner.payout_requests'])}}">
                                <a class="nav-link" href="{{ route('peer_partner.payout_requests') }}">
                                   
                                    <span class="menu-title">{{translate('Payout Requests')}}</span>
                                </a>
                            </li>
                            </ul>
                        </li>
                        @elseif(in_array(Auth::user()->user_type, ['opration']))

                                  <li>
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span class="menu-title">{{translate('Manage Peer Partners')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                               <li class="{{ areActiveRoutes(['peer_partner.index'])}}">
                                <a class="nav-link" href="{{ route('peer_partner.index') }}">
                                   
                                    <span class="menu-title">{{translate('Partner list')}}</span>
                                </a>
                            </li>
                            <li class="{{ areActiveRoutes(['admin.create_partner'])}}">
                                <a class="nav-link" href="{{ route('admin.create_partner') }}">
                                   
                                    <span class="menu-title">{{translate('Create Partner')}}</span>
                                </a>
                            </li>
                            </ul>
                        </li>
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('3', json_decode(Auth::user()->staff->role->permissions)))
                            @php
                                $orders = 2;
                            @endphp
                        <li>
                            <a href="#">
                                 <i class="fa fa-shopping-basket"></i>
                                <span class="menu-title">{{translate('Manage Orders')}}</span>
                                <i class="arrow"></i>
                            </a>
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['orders.index.admin', 'orders.show'])}}">
                                    <a class="nav-link" href="{{ route('orders.index.admin') }}">
                                    
                                        <span class="menu-title">{{translate('Inhouse orders')}} @if($orders > 0)<span class="pull-right badge badge-info">{{ $orders }}</span>@endif</span>
                                    </a>
                                <li>

                                <li class="{{ areActiveRoutes(['assign.orders'])}}">
                                    <a class="nav-link" href="{{ route('assign.orders') }}">
                                    
                                        <span class="menu-title">{{translate('Assign orders')}} @if($orders > 0)<span class="pull-right badge badge-info">{{ $orders }}</span>@endif</span>
                                    </a>
                                <li>

                                <li class="{{ areActiveRoutes(['archived.orders'])}}">
                                    <a class="nav-link" href="{{ route('archived.orders') }}">
                                    
                                        <span class="menu-title">{{translate('Archived orders')}} </span>
                                    </a>
                                <li>
                               <!--  <li class="{{ areActiveRoutes(['orders.finalordershtml']) }}">
                                        <a class="nav-link" href="{{ route('orders.finalordershtml') }}" target="_blank">
                                        
                                            <span class="menu-title  d-block">{{translate('Final Orders')}} </span>
                                        </a>
                                        
                                        
                                    </li>
                            <li class="{{ (request()->segment(3) == 'new_order') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(1),'order_status'=>'new_order']) }}">
                                
                                    <span class="menu-title d-block">{{translate('New Orders')}}  <span class="badge badge-warning" style="float:right">{{orderStatusCount(1)}}</span></span>
                                </a>
                            </li>
                         
                            
                            <li class="{{ (request()->segment(3) == 'accepted') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(2),'order_status'=>'accepted']) }}">
                                
                                    <span class="menu-title d-block">{{translate('Accepted')}}  <span class="badge badge-success" style="float:right">{{orderStatusCount(2)}}</span></span>
                                </a>
                            </li>
                            <li class="{{ (request()->segment(3) == 'processing') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(3),'order_status'=>'processing']) }}">
                                
                                    <span class="menu-title d-block">{{translate('Processing')}}  <span class="badge badge-success" style="float:right">{{orderStatusCount(3)}}</span></span>
                                </a>
                            </li>
                            <li class="{{ (request()->segment(3) == 'assigned') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(4),'order_status'=>'assigned']) }}">
                                
                                    <span class="menu-title d-block">{{translate('Assigned')}}  <span class="badge badge-info" style="float:right">{{orderStatusCount(4)}}</span></span>
                                </a>
                            </li>
                            <li class="{{ (request()->segment(3) == 'dispatched') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(5),'order_status'=>'dispatched']) }}">
                                
                                    <span class="menu-title d-block">{{translate('Dispatched ')}}  <span class="badge badge-primary" style="float:right">{{orderStatusCount(5)}}</span></span>
                                </a>
                            </li>
                            <li class="{{ (request()->segment(3) == 'on_delivery') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(6),'order_status'=>'on_delivery']) }}">
                                
                                    <span class="menu-title d-block">{{translate('On delivery')}}  <span class="badge badge-dark" style="float:right">{{orderStatusCount(6)}}</span></span>
                                </a>
                            </li>
                            <li class="{{ (request()->segment(3) == 'partial_delivered') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(7),'order_status'=>'partial_delivered']) }}">
                                
                                    <span class="menu-title d-block">{{translate('Partial Delivered')}}  <span class="badge badge-dark" style="float:right">{{orderStatusCount(7)}}</span></span>
                                </a>
                            </li>

                            <li class="{{ (request()->segment(3) == 'delivered') ? 'active-link' : '' }}">
                                <a class="nav-link" href="{{ route('orders.new-orders',['order_status_id'=>encrypt(8),'order_status'=>'delivered']) }}">
                                
                                    <span class="menu-title d-block">{{translate('Delivered')}}  <span class="badge badge-dark" style="float:right">{{orderStatusCount(8)}}</span></span>
                                </a>
                            </li> -->
                            <li class="{{ areActiveRoutes(['orders.unpaid.online'])}}">
                                <a class="nav-link" href="{{ route('orders.unpaid.online',['unpaid_online'=>'unpaid_order']) }}">
                                    
                                        <span class="menu-title">{{translate('Unpaid Online Order')}}</span>
                                </a>
                            </li>
                                    <li>
                                    <!-- 27-09-2021 -->
                                    <a class="nav-link" href="{{ route('orders.index.adminreferral') }}">
                                    
                                        <span class="menu-title">{{translate('Referral Code orders')}}</span>
                                    </a>
                                </li>
                                @if(!in_array(Auth::user()->user_type, ['admin']))
                                    @if(Auth::user()->staff->role->name=="Sorting Hub" || Auth::user()->staff->role->name=="Sorting Hub Manager")
                                <li class="{{ areActiveRoutes(['orders.index.admin', 'orders.show'])}}">
                                    <a class="nav-link" href="{{ route('admin.replacement') }}">
                                    
                                        <span class="menu-title">{{translate('Replacement Requests')}}</span>
                                    </a>
                                </li>
                                @endif
                            @endif
                            <li class="{{ areActiveRoutes(['orders.index.admin', 'orders.show'])}}">
                                    <a class="nav-link" href="{{ route('sortinghuborders.showbydate') }}">
                                    
                                        <span class="menu-title">{{translate('Purchase Report')}}</span>
                                    </a>
                                </li>

                                <li class="{{ areActiveRoutes(['orders.recurring.admin']) }}">
                                        <a class="nav-link" href="{{ route('orders.recurring.admin') }}">
                                        
                                            <span class="menu-title  d-block">{{translate('Recurring Orders')}} </span>
                                        </a>                                
                                </li>
                                <li class="{{ areActiveRoutes(['orders.recurringrefund.admin']) }}">
                                        <a class="nav-link" href="{{ route('orders.recurringrefund.admin') }}">
                                        
                                            <span class="menu-title  d-block">{{translate('Recurring Refunds')}} </span>
                                        </a>                                
                                </li>
                    </ul>
                </li>

                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('3', json_decode(Auth::user()->staff->role->permissions)))
                            <li>
                                <a href="#">
                                    <i class="fa fa-motorcycle"></i>
                                    <span class="menu-title">{{translate('Delivery Slot')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['deliveryslot.index','deliveryslot.delete','deliveryslot.create'])}}">
                                        <a class="nav-link" href="{{ route('deliveryslot.index') }}">{{translate('Delivery Slot Detail')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif



                         @if(in_array(Auth::user()->user_type, ['staff']))
                                @php
                                    $orders = 2;
                                @endphp
                               @if(Auth::user()->staff->role->name=="Delivery Boy")
                            <li class="{{ areActiveRoutes(['deliveryboy.order'])}}">
                                <a class="nav-link" href="{{ route('deliveryboy.order') }}">
                                    <i class="fa fa-shopping-basket"></i>
                                    <span class="menu-title">{{translate('Your Orders')}} @if($orders > 0)<span class="pull-right badge badge-info">{{ $orders }}</span>@endif</span>
                                </a>
                            </li> 
                           @endif
                        @endif
<?php /*


                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('2', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="{{ areActiveRoutes(['flash_deals.index', 'flash_deals.create', 'flash_deals.edit'])}}">
                            <a class="nav-link" href="{{ route('flash_deals.index') }}">
                                <i class="fa fa-bolt"></i>
                                <span class="menu-title">{{translate('Flash Deal')}}</span>
                            </a>
                        </li>
                        @endif
 */ ?>

<?php /*                        @if(in_array(Auth::user()->user_type, ['admin']))
                        <li class="{{ areActiveRoutes(['webp.show'])}}">
                            <a class="nav-link" href="{{ route('webp.show') }}">
                                <i class="fa fa-bolt"></i>
                                <span class="menu-title">{{translate('WEBP')}}</span>
                            </a>
                        </li>
                         @endif
*/ ?>
<?php /*                        

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('14', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="{{ areActiveRoutes(['pick_up_point.order_index','pick_up_point.order_show'])}}">
                            <a class="nav-link" href="{{ route('pick_up_point.order_index') }}">
                                <i class="fa fa-money"></i>
                                <span class="menu-title">{{translate('Pick-up Point Order')}}</span>
                            </a>
                        </li>
                        @endif



                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('4', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="{{ areActiveRoutes(['sales.index', 'sales.show'])}}">
                            <a class="nav-link" href="{{ route('sales.index') }}">
                                <i class="fa fa-money"></i>
                                <span class="menu-title">{{translate('Total sales')}}</span>
                            </a>
                        </li>
                        @endif

*/ ?>

                        @if (\App\Addon::where('unique_identifier', 'refund_request')->first() != null)
                            <li>
                                <a href="#">
                                    <i class="fa fa-refresh"></i>
                                    <span class="menu-title">{{translate('Refund Request')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['refund_requests_all', 'reason_show'])}}">
                                        <a class="nav-link" href="{{route('refund_requests_all')}}">{{translate('Refund Requests')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['paid_refund'])}}">
                                        <a class="nav-link" href="{{route('paid_refund')}}">{{translate('Approved Refund')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['refund_time_config'])}}">
                                        <a class="nav-link" href="{{route('refund_time_config')}}">{{translate('Refund Configuration')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if((in_array(Auth::user()->user_type, ['admin']) || in_array('5', json_decode(Auth::user()->staff->role->permissions))) && \App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
                        <li>
                            <a href="#">
                                <i class="fa fa-user-plus"></i>
                                <span class="menu-title">{{translate('Sellers')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['sellers.index', 'sellers.create', 'sellers.edit', 'sellers.payment_history','sellers.approved','sellers.profile_modal'])}}">
                                    @php
                                        $sellers = \App\Seller::where('verification_status', 0)->where('verification_info', '!=', null)->count();
                                        //$withdraw_req = \App\SellerWithdrawRequest::where('viewed', '0')->get();
                                    @endphp
                                    <a class="nav-link" href="{{route('sellers.index')}}">{{translate('Seller List')}} @if($sellers > 0)<span class="pull-right badge badge-info">{{ $sellers }}</span> @endif</a>
                                </li>
                                <li class="{{ areActiveRoutes(['withdraw_requests_all'])}}">
                                    <a class="nav-link" href="{{ route('withdraw_requests_all') }}">{{translate('Seller Withdraw Requests')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['sellers.payment_histories'])}}">
                                    <a class="nav-link" href="{{ route('sellers.payment_histories') }}">{{translate('Seller Payments')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['business_settings.vendor_commission'])}}">
                                    <a class="nav-link" href="{{ route('business_settings.vendor_commission') }}">{{translate('Seller Commission')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['seller_verification_form.index'])}}">
                                    <a class="nav-link" href="{{route('seller_verification_form.index')}}">{{translate('Seller Verification Form')}}</a>
                                </li>
                                @if (\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated)
                                    <li class="{{ areActiveRoutes(['seller_packages.index', 'seller_packages.create', 'seller_packages.edit'])}}">
                                        <a class="nav-link" href="{{ route('seller_packages.index') }}">{{translate('Seller Packages')}}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('6', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-user-plus"></i>
                                <span class="menu-title">{{translate('Customers')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['customers.index'])}}">
                                    <a class="nav-link" href="{{ route('customers.index') }}">{{translate('Customer list')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['customer_packages.index', 'customer_packages.create', 'customer_packages.edit'])}}">
                                    <a class="nav-link" href="{{ route('customer_packages.index') }}">{{translate('Classified Packages')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['customer_packages.index', 'customer_packages.create', 'customer_packages.edit'])}}">
                                    <a class="nav-link" href="{{ route('search_history.list') }}">{{translate('Search Key')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['razorpayx.getcontactlist','razorpayx.transfermoney','razorpayx.addAccount','razorpayx.show.createcontact','razorpayx.view.transfermoney'])}}">
                                    <a class="nav-link" href="{{ route('razorpayx.getcontactlist') }}">{{translate('Trasfer Money')}}</a>
                                </li>
                             <?php /*   <li class="{{ areActiveRoutes(['razorpayx.getwithdrawrequest'])}}">
                                    <a class="nav-link" href="{{ route('razorpayx.getwithdrawrequest') }}">{{translate('Withdraw Request')}}</a>
                                </li> */?>
                            </ul>
                        </li>
                        @endif
                        @php
                            $conversation = \App\Conversation::where('receiver_id', Auth::user()->id)->where('receiver_viewed', '1')->get();
                        @endphp
                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('16', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="{{ areActiveRoutes(['conversations.admin_index', 'conversations.admin_show'])}}">
                            <a class="nav-link" href="{{ route('conversations.admin_index') }}">
                                <i class="fa fa-comment"></i>
                                <span class="menu-title">{{translate('Conversations')}}</span>
                                @if (count($conversation) > 0)
                                    <span class="pull-right badge badge-info">{{ count($conversation) }}</span>
                                @endif
                            </a>
                        </li>
                        @endif


                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('17', json_decode(Auth::user()->staff->role->permissions)) || auth()->user()->staff->role->name == "Sorting Hub")
                         <li>
                            <a href="#">
                                <i class="fa fa-file"></i>
                                <span class="menu-title">{{translate('Reports')}}</span>
                                <i class="arrow"></i>
                            </a>
                            <ul class="collapse">
                            @if(in_array(Auth::user()->user_type, ['admin']) || in_array('17', json_decode(Auth::user()->staff->role->permissions))) 
                                <li class="{{ areActiveRoutes(['stock_report.index'])}}">
                                    <a class="nav-link" href="{{ route('stock_report.index') }}">{{translate('Stock Report')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['in_house_sale_report.index'])}}">
                                    <a class="nav-link" href="{{ route('in_house_sale_report.index') }}">{{translate('In House Sale Report')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['seller_report.index'])}}">
                                    <a class="nav-link" href="{{ route('seller_report.index') }}">{{translate('Seller Report')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['seller_sale_report.index'])}}">
                                    <a class="nav-link" href="{{ route('seller_sale_report.index') }}">{{translate('Seller Based Selling Report')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['wish_report.index'])}}">
                                    <a class="nav-link" href="{{ route('wish_report.index') }}">{{translate('Product Wish Report')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['sku_data.index'])}}">
                                    <a class="nav-link" href="{{ route('sku_data.index') }}">{{translate('SKU Data')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['sale_data.index'])}}">
                                    <a class="nav-link" href="{{ route('sale_data.index') }}">{{translate('Sale Data')}}</a>
                                </li>
                                @endif
                                <li class="{{ areActiveRoutes(['invoice_data.index'])}}">
                                    <a class="nav-link" href="{{ route('invoice_data.index') }}">{{translate('Invoice Data')}}</a>
                                </li>
                            </ul>
                        </li> 
                        @endif
<?php /*
                         @if(in_array(Auth::user()->user_type, ['admin']) || in_array('32', json_decode(Auth::user()->staff->role->permissions)))
                       <li>
                            <a href="#">
                                <i class="fa fa-file"></i>
                                <span class="menu-title">{{translate('Master Reports')}}</span>
                                <i class="arrow"></i>
                            </a>

                            
                            <ul class="collapse">
                                 <li class="{{ areActiveRoutes(['peerpartner_data.index'])}}">
                                        <a class="nav-link" href="{{ route('peerpartner_data.index') }}">{{translate('Peer Partner Report')}}</a>
                                    </li>
                            </ul>
                        </li>
                        @endif



                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('7', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-envelope"></i>
                                <span class="menu-title">{{translate('Messaging')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['newsletters.index'])}}">
                                    <a class="nav-link" href="{{route('newsletters.index')}}">{{translate('Newsletters')}}</a>
                                </li>

                                @if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null)
                                    <li class="{{ areActiveRoutes(['sms.index'])}}">
                                        <a class="nav-link" href="{{route('sms.index')}}">{{translate('SMS')}}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @endif



                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('8', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-briefcase"></i>
                                <span class="menu-title">{{translate('Business Settings')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['activation.index'])}}">
                                    <a class="nav-link" href="{{route('activation.index')}}">{{translate('Activation')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['payment_method.index'])}}">
                                    <a class="nav-link" href="{{ route('payment_method.index') }}">{{translate('Payment method')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['file_system.index'])}}">
                                    <a class="nav-link" href="{{ route('file_system.index') }}">{{translate('File System Configuration')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['smtp_settings.index'])}}">
                                    <a class="nav-link" href="{{ route('smtp_settings.index') }}">{{translate('SMTP Settings')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['google_analytics.index'])}}">
                                    <a class="nav-link" href="{{ route('google_analytics.index') }}">{{translate('Google Analytics')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['google_recaptcha.index'])}}">
                                    <a class="nav-link" href="{{ route('google_recaptcha.index') }}">{{translate('Google reCAPTCHA')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['facebook_chat.index'])}}">
                                    <a class="nav-link" href="{{ route('facebook_chat.index') }}">{{translate('Facebook Chat & Pixel')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['social_login.index'])}}">
                                    <a class="nav-link" href="{{ route('social_login.index') }}">{{translate('Social Media Login')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['currency.index'])}}">
                                    <a class="nav-link" href="{{route('currency.index')}}">{{translate('Currency')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['languages.index', 'languages.create', 'languages.store', 'languages.show', 'languages.edit'])}}">
                                    <a class="nav-link" href="{{route('languages.index')}}">{{translate('Languages')}}</a>
                                </li>
                            </ul>
                        </li>
                        @endif

*/ ?>

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('9', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-desktop"></i>
                                <span class="menu-title">{{translate('Frontend Settings')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['home_settings.index', 'home_banners.index', 'sliders.index', 'home_categories.index', 'home_banners.create', 'home_categories.create', 'home_categories.edit', 'sliders.create'])}}">
                                    <a class="nav-link" href="{{route('home_settings.index')}}">{{translate('Home')}}</a>
                                </li>
                                <li>
                                    <a href="#">
                                        <span class="menu-title">{{translate('Policy Pages')}}</span>
                                        <i class="arrow"></i>
                                    </a>

                                    <!--Submenu-->
                                    <ul class="collapse">

                                        <li class="{{ areActiveRoutes(['sellerpolicy.index'])}}">
                                            <a class="nav-link" href="{{route('sellerpolicy.index', 'seller_policy')}}">{{translate('Seller Policy')}}</a>
                                        </li>
                                        <li class="{{ areActiveRoutes(['returnpolicy.index'])}}">
                                            <a class="nav-link" href="{{route('returnpolicy.index', 'return_policy')}}">{{translate('Return Policy')}}</a>
                                        </li>
                                        <li class="{{ areActiveRoutes(['supportpolicy.index'])}}">
                                            <a class="nav-link" href="{{route('supportpolicy.index', 'support_policy')}}">{{translate('Support Policy')}}</a>
                                        </li>
                                        <li class="{{ areActiveRoutes(['terms.index'])}}">
                                            <a class="nav-link" href="{{route('terms.index', 'terms')}}">{{translate('Terms & Conditions')}}</a>
                                        </li>
                                        <li class="{{ areActiveRoutes(['privacypolicy.index'])}}">
                                            <a class="nav-link" href="{{route('privacypolicy.index', 'privacy_policy')}}">{{translate('Privacy Policy')}}</a>
                                        </li>
                                    </ul>

                                </li>
                                <li class="{{ areActiveRoutes(['pages.index', 'pages.create', 'pages.edit'])}}">
                                    <a class="nav-link" href="{{route('pages.index')}}">{{translate('Custom Pages')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['links.index', 'links.create', 'links.edit'])}}">
                                    <a class="nav-link" href="{{route('links.index')}}">{{translate('Useful Link')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['generalsettings.index'])}}">
                                    <a class="nav-link" href="{{route('generalsettings.index')}}">{{translate('General Settings')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['generalsettings.logo'])}}">
                                    <a class="nav-link" href="{{route('generalsettings.logo')}}">{{translate('Logo Settings')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['generalsettings.color'])}}">
                                    <a class="nav-link" href="{{route('generalsettings.color')}}">{{translate('Color Settings')}}</a>
                                </li>
                                 <li class="{{ areActiveRoutes(['brandslider.index', 'brandslider.create', 'brandslider.edit'])}}">
                                        <a class="nav-link" href="{{route('brandslider.index')}}">{{translate('Brand Slider')}}</a>
                                </li>
                            </ul>
                        </li>
                        @endif
<?php /*
                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('12', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-gear"></i>
                                <span class="menu-title">{{translate('E-commerce Setup')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">

                                <li class="{{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                    <a class="nav-link" href="{{route('attributes.index')}}">{{translate('User hierarchy')}}</a>
                                </li>

                                {{--<li class="{{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit'])}}">
                                    <a class="nav-link" href="{{route('attributes.index')}}">{{translate('Attribute')}}</a>
                                </li>--}}

                                <li class="{{ areActiveRoutes(['coupon.index','coupon.create','coupon.edit'])}}">
                                    <a class="nav-link" href="{{route('coupon.index')}}">{{translate('Coupon')}}</a>
                                </li>
                                <li>
                                    <li class="{{ areActiveRoutes(['pick_up_points.index','pick_up_points.create','pick_up_points.edit'])}}">
                                        <a class="nav-link" href="{{route('pick_up_points.index')}}">{{translate('Pickup Point')}}</a>
                                    </li>
                                </li>
                                <li>
                                    <li class="{{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                        <a class="nav-link" href="{{route('shipping_configuration.index')}}">{{translate('Shipping Configuration')}}</a>
                                    </li>
                                </li>
                                <li>
                                    <li class="{{ areActiveRoutes(['countries.index','countries.edit','countries.update'])}}">
                                        <a class="nav-link" href="{{route('countries.index')}}">{{translate('Shipping Countries')}}</a>
                                    </li>
                                </li>
                            </ul>
                        </li>
                        @endif

*/ ?>

                        @if (\App\Addon::where('unique_identifier', 'affiliate_system')->first() != null)
                            <li>
                                <a href="#">
                                    <i class="fa fa-link"></i>
                                    <span class="menu-title">{{translate('Affiliate System')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['affiliate.configs'])}}">
                                        <a class="nav-link" href="{{route('affiliate.configs')}}">{{translate('Affiliate Configurations')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['affiliate.index'])}}">
                                        <a class="nav-link" href="{{route('affiliate.index')}}">{{translate('Affiliate Options')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['affiliate.users', 'affiliate_users.show_verification_request', 'affiliate_user.payment_history'])}}">
                                        <a class="nav-link" href="{{route('affiliate.users')}}">{{translate('Affiliate Users')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['refferals.users'])}}">
                                        <a class="nav-link" href="{{route('refferals.users')}}">{{translate('Refferal Users')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['affiliate.withdraw_requests'])}}">
                                        <a class="nav-link" href="{{route('affiliate.withdraw_requests')}}">{{translate('Affiliate Withdraw Request')}}</a>
                                    </li>

                                </ul>
                            </li>
                        @endif

                        @if (\App\Addon::where('unique_identifier', 'offline_payment')->first() != null)
                            <li>
                                <a href="#">
                                    <i class="fa fa-bank"></i>
                                    <span class="menu-title">{{translate('Offline Payment System')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['manual_payment_methods.index', 'manual_payment_methods.create', 'manual_payment_methods.edit'])}}">
                                        <a class="nav-link" href="{{ route('manual_payment_methods.index') }}">{{translate('Manual Payment Methods')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['offline_wallet_recharge_request.index'])}}">
                                        <a class="nav-link" href="{{ route('offline_wallet_recharge_request.index') }}">{{translate('Offline Wallet Rechage')}}</a>
                                    </li>
                                    @if (\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated)
                                        <li class="{{ areActiveRoutes(['offline_seller_package_payment_request.index'])}}">
                                            <a class="nav-link" href="{{ route('offline_seller_package_payment_request.index') }}">{{translate('Offline Seller Package Payment')}}</a>
                                        </li>
                                    @endif
                                    @if(\App\BusinessSetting::where('type', 'classified_product')->first()->value == 1)
                                        <li class="{{ areActiveRoutes(['offline_customer_package_payment_request.index'])}}">
                                            <a class="nav-link" href="{{ route('offline_customer_package_payment_request.index') }}">{{translate('Offline Customer Package Payment')}}</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if (\App\Addon::where('unique_identifier', 'paytm')->first() != null)
                            <li>
                                <a href="#">
                                    <i class="fa fa-mobile"></i>
                                    <span class="menu-title">{{translate('Paytm Payment Gateway')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['paytm.index'])}}">
                                        <a class="nav-link" href="{{route('paytm.index')}}">{{translate('Set Paytm Credentials')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null)
                            <li>
                                <a href="#">
                                    <i class="fa fa-btc"></i>
                                    <span class="menu-title">{{translate('Club Point System')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['club_points.configs'])}}">
                                        <a class="nav-link" href="{{route('club_points.configs')}}">{{translate('Club Point Configurations')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['set_product_points', 'product_club_point.edit'])}}">
                                        <a class="nav-link" href="{{route('set_product_points')}}">{{translate('Set Product Point')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['club_points.index', 'club_point.details'])}}">
                                        <a class="nav-link" href="{{route('club_points.index')}}">{{translate('User Points')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null)
                            <li>
                                <a href="#">
                                    <i class="fa fa-mobile"></i>
                                    <span class="menu-title">{{translate('OTP System')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['otp.configconfiguration'])}}">
                                        <a class="nav-link" href="{{route('otp.configconfiguration')}}">{{translate('OTP Configurations')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['otp_credentials.index'])}}">
                                        <a class="nav-link" href="{{route('otp_credentials.index')}}">{{translate('Set OTP Credentials')}}</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

<?php /*

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('13', json_decode(Auth::user()->staff->role->permissions)))
                            @php
                                $support_ticket = DB::table('tickets')
                                            ->where('viewed', 0)
                                            ->select('id')
                                            ->count();
                            @endphp
                        <li class="{{ areActiveRoutes(['support_ticket.admin_index', 'support_ticket.admin_show'])}}">
                            <a class="nav-link" href="{{ route('support_ticket.admin_index') }}">
                                <i class="fa fa-support"></i>
                                <span class="menu-title">{{translate('Support Ticket')}} @if($support_ticket > 0)<span class="pull-right badge badge-info">{{ $support_ticket }}</span>@endif</span>
                            </a>
                        </li>
                        @endif 
*/ ?>

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('11', json_decode(Auth::user()->staff->role->permissions)))
                        <li class="{{ areActiveRoutes(['seosetting.index'])}}">
                            <a class="nav-link" href="{{ route('seosetting.index') }}">
                                <i class="fa fa-search"></i>
                                <span class="menu-title">{{translate('SEO Setting')}}</span>
                            </a>
                        </li>
                        @endif


                           @if(in_array(Auth::user()->user_type, ['admin']) || in_array('6', json_decode(Auth::user()->staff->role->permissions)))
                      
                        <li class="{{ areActiveRoutes(['customers.index'])}}">
                                <a class="nav-link" href="{{ route('callceter.callcenterall') }}">
                                    <i class="fa fa-user-plus"></i>
                                    <span class="menu-title">{{translate('Create Sub Admin Users
')}}</span>
                                </a>
                            </li>
                        @endif

                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('10', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-user"></i>
                                <span class="menu-title">{{translate('Staffs')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['staffs.index', 'staffs.create', 'staffs.edit'])}}">
                                    <a class="nav-link" href="{{ route('staffs.index') }}">{{translate('All staffs')}}</a>
                                </li>
                                <li class="{{ areActiveRoutes(['roles.index', 'roles.create', 'roles.edit'])}}">
                                    <a class="nav-link" href="{{route('roles.index')}}">{{translate('Staff permissions')}}</a>
                                </li>
                            </ul>
                        </li>

                            @php
                            $authPerson = ['mkumar122043@gmail.com','akashnator@gmail.com'];
                            in_array(Auth::user()->email,$authPerson)
                            @endphp

                            @if(in_array(Auth::user()->email,$authPerson))
                            <li>
                                <a href="#">
                                    <i class="fa fa-user"></i>
                                    <span class="menu-title">{{translate('DOFO')}}</span>
                                    <i class="arrow"></i>
                                </a>

                                <!--Submenu-->
                                <ul class="collapse">
                                    <li class="{{ areActiveRoutes(['DOFO.index','DOFO.create'])}}">
                                        <a class="nav-link" href="{{ route('DOFO.index') }}">{{translate('DOFO List')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['DOFO.orders'])}}">
                                    <a class="nav-link" href="{{ route('DOFO.orders') }}">{{translate('DOFO Orders')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['DOFO.create-order'])}}">
                                    <a class="nav-link" href="{{ route('DOFO.create-order') }}">{{translate('Create Bulk DOFO Orders')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['DOFO.access-switch'])}}">
                                    <a class="nav-link" href="{{ route('DOFO.access-switch') }}">{{translate('Access Switch')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['DOFO.delivery-boy','DOFO.create-delivery-boy'])}}">
                                    <a class="nav-link" href="{{ route('DOFO.delivery-boy') }}">{{translate('DOFO Delivery Boy')}}</a>
                                    </li>
                                    <li class="{{ areActiveRoutes(['DOFO.show-csv-order'])}}">
                                    <a class="nav-link" href="{{ route('DOFO.show-csv-order') }}">{{translate('DOFO CSV Orders')}}</a>
                                    </li>
                                </ul>
                            </li>
                            @endif
                        @endif


                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('15', json_decode(Auth::user()->staff->role->permissions)))
                            <li class="{{ areActiveRoutes(['addons.index', 'addons.create'])}}">
                                <a class="nav-link" href="{{ route('addons.index') }}">
                                    <i class="fa fa-wrench"></i>
                                    <span class="menu-title">{{translate('Addon Manager')}}</span>
                                </a>
                            </li>
                        @endif



                        <!-- 12-11-2021 - openings  -->
                        @if(in_array(Auth::user()->user_type, ['admin']))
                            <li class="{{ areActiveRoutes(['opening.index', 'opening.create'])}}">
                                <a class="nav-link" href="{{ route('opening.index') }}">
                                    <i class="fa fa-wrench"></i>
                                    <span class="menu-title">{{translate('Openings')}}</span>
                                </a>
                            </li>
                        @endif


                        <!-- 04-03-2022 - Real time data START-->
                        @if(in_array(Auth::user()->user_type, ['admin']) || in_array('17', json_decode(Auth::user()->staff->role->permissions)))
                        <li>
                            <a href="#">
                                <i class="fa fa-file"></i>
                                <span class="menu-title">{{translate('Real Time Data Reports')}}</span>
                                <i class="arrow"></i>
                            </a>

                            <!--Submenu-->
                            <ul class="collapse">
                                <li class="{{ areActiveRoutes(['no_of_order.index'])}}">
                                    <a class="nav-link" href="{{ route('no_of_order.index') }}">{{translate('No of Order Report')}}</a>
                                </li>
                            </ul>
                        </li>
                        @endif
                        <!-- Real time data END -->
                    </ul>
                </div>
            </div>
        </div>
        <!--================================-->
        <!--End menu-->

    </div>
</nav>
