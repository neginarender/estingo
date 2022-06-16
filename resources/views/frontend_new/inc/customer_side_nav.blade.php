<div class="sidebar sidebar--style-3 no-border stickyfill col-lg-3 d-none d-lg-block p-0">
    <div class="widget mb-0">
        <div class="widget-profile-box text-center p-3">
            @php
           //dd($user);
            @endphp
            @if ($user->avatar_original != null)
                <div class="image" style="background-image:url('{{Storage::disk('s3')->url($user->avatar_original)}}')"></div>
            @else
                <img src="{{ static_asset('frontend/images/user.png') }}" class="image rounded-circle">
            @endif
            <div class="name">{{ $user->name }}</div>
     
        </div>

        <div class="sidebar-widget-title py-3">
            <span>{{translate('Menu')}}</span>
        </div>
        <div class="widget-profile-menu py-3">
            <ul class="categories categories--style-3">
                <li>
                    
                    @if($user && $user->type == 'partner' && $user->peer_partner == '1' && $user->verification_status == 1)
                    <a href="{{ route('sampledashboard') }}" class="{{ areActiveRoutesHome(['sampledashboard'])}}">
                        <i class="la la-dashboard"></i>
                        <span class="category-name">
                            {{translate('Dashboard')}}
                        </span>
                    </a>
                    @else
                    <a href="{{ route('phoneapi.dashboard') }}" class="{{ areActiveRoutesHome(['phoneapi.dashboard'])}}">
                        <i class="fa fa-dashboard"></i>
                        <span class="category-name">
                            {{translate('Dashboard')}}
                        </span>
                    </a>
                    @endif
                </li>

                @if(\App\BusinessSetting::where('type', 'classified_product')->first()->value == 1)
                <li>
                    <a href="{{ route('customer_products.index') }}" class="{{ areActiveRoutesHome(['customer_products.index', 'customer_products.create', 'customer_products.edit'])}}">
                        <i class="la la-diamond"></i>
                        <span class="category-name">
                            {{translate('Classified Products')}}
                        </span>
                    </a>
                </li>
                @endif
                @php
                    $delivery_viewed = App\Order::where('user_id', $user->id)->where('delivery_viewed', 0)->get()->count();
                    $payment_status_viewed = App\Order::where('user_id', $user->id)->where('payment_status_viewed', 0)->get()->count();
                    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
                    $club_point_addon = \App\Addon::where('unique_identifier', 'club_point')->first();
                @endphp
                <li>
                    <a href="{{ route('phoneapi.purchase_history') }}" class="{{ areActiveRoutesHome(['phoneapi.purchase_history'])}}">
                        <i class="fa fa-file-text"></i>
                        <span class="category-name">
                            {{translate('Purchase History')}} @if($delivery_viewed > 0 || $payment_status_viewed > 0)<span class="ml-2" style="color:green"><strong>({{ translate('New Notifications') }})</strong></span>@endif
                        </span>
                    </a>
                </li>
                 <li>
                    <a href="{{ route('phoneapi.futureorder') }}" class="{{ areActiveRoutesHome(['phoneapi.futureorder'])}}">
                        <i class="fa fa-file-text"></i>
                        <span class="category-name">
                            {{translate('Future Orders')}} 
                        </span>
                    </a>
                </li>
                <!-- <li>
                    <a href="{{ route('digital_purchase_history.index') }}" class="{{ areActiveRoutesHome(['digital_purchase_history.index'])}}">
                        <i class="la la-download"></i>
                        <span class="category-name">
                            {{translate('Downloads')}}
                        </span>
                    </a>
                </li> -->

                @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                    <li>
                        <a href="{{ route('customer_refund_request') }}" class="{{ areActiveRoutesHome(['customer_refund_request'])}}">
                            <i class="fa fa-file-text"></i>
                            <span class="category-name">
                                {{translate('Sent Refund Request')}}
                            </span>
                        </a>
                    </li>
                @endif

                @if($user->type == 'partner' && $user->peer_partner == 1 && $user->verification_status == 1)
                <li>
                    <a href="{{ route('partner.referral.history') }}" class="{{ areActiveRoutesHome(['partner.referral.history'])}}">
                        <i class="fa fa-file-text"></i>
                        <span class="category-name">
                            {{translate('Peer Partner Referrals')}}
                        </span>
                    </a>
                </li>
                @endif

                <li>
                    <a href="{{ route('phoneapi.wishlists') }}" class="{{ areActiveRoutesHome(['phoneapi.wishlists'])}}">
                        <i class="fa fa-heart-o"></i>
                        <span class="category-name">
                            {{translate('Wishlist')}}
                        </span>
                    </a>
                </li>
                @if (\App\BusinessSetting::where('type', 'conversation_system')->first()->value == 1)
                    @php
                        $conversation = \App\Conversation::where('sender_id', $user->id)->where('sender_viewed', 0)->get();
                    @endphp
                    <li>
                        <a href="{{ route('conversations.index') }}" class="{{ areActiveRoutesHome(['conversations.index', 'conversations.show'])}}">
                            <i class="fa fa-comment"></i>
                            <span class="category-name">
                                {{translate('Conversations')}}
                                @if (count($conversation) > 0)
                                    <span class="ml-2" style="color:green"><strong>({{ count($conversation) }})</strong></span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('phoneapi.profile') }}" class="{{ areActiveRoutesHome(['phoneapi.profile'])}}">
                        <i class="fa fa-user"></i>
                        <span class="category-name">
                            {{translate('Manage Profile')}}
                        </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('redeem-gift-card') }}" class="{{ areActiveRoutesHome(['redeem-gift-card'])}}">
                        <i class="fa fa-user"></i>
                        <span class="category-name">
                            {{translate('Redeem a gift card')}}
                        </span>
                    </a>
                </li>

                @if (\App\BusinessSetting::where('type', 'wallet_system')->first()->value == 1)
                    <li>
                        <a href="{{ route('phoneapi.wallet') }}" class="{{ areActiveRoutesHome(['phoneapi.wallet'])}}">
                            <i class="fa fa-inr"></i>
                            <span class="category-name">
                                {{translate('My Wallet')}}
                            </span>
                        </a>
                    </li>
                @endif

                @if ($club_point_addon != null && $club_point_addon->activated == 1)
                    <li>
                        <a href="{{ route('earnng_point_for_user') }}" class="{{ areActiveRoutesHome(['earnng_point_for_user'])}}">
                            <i class="fa fa-inr"></i>
                            <span class="category-name">
                                {{translate('Earning Points')}}
                            </span>
                        </a>
                    </li>
                @endif

                @if (\App\Addon::where('unique_identifier', 'affiliate_system')->first() != null && \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated && $user->affiliate_user != null && $user->affiliate_user->status)
                    <li>
                        <a href="{{ route('affiliate.user.index') }}" class="{{ areActiveRoutesHome(['affiliate.user.index', 'affiliate.payment_settings'])}}">
                            <i class="fa fa-inr"></i>
                            <span class="category-name">
                                {{translate('Affiliate System')}}
                            </span>
                        </a>
                    </li>
                @endif
                @php
                    $support_ticket = DB::table('tickets')
                                ->where('client_viewed', 0)
                                ->where('user_id', $user->id)
                                ->count();
                @endphp
                <li>
                    <a href="{{ route('phoneapi.supportticket') }}" class="{{ areActiveRoutesHome(['phoneapi.supportticket'])}}">
                        <i class="fa fa-support"></i>
                        <span class="category-name">
                            {{translate('Support Ticket')}} @if($support_ticket > 0)<span class="ml-2" style="color:green"><strong>({{ $support_ticket }} {{ translate('New') }})</strong></span></span>@endif
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        @if (\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
            <div class="widget-seller-btn pt-4">
                <a href="{{ route('shops.create') }}" class="btn btn-anim-primary w-100">{{translate('Be A Seller')}}</a>
            </div>
        @endif
    </div>
</div>
