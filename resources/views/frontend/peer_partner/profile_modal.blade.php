<div class="panel">
    <div class="panel-body">
        <div class="">
            <!-- Simple profile -->
            <div class="text-center">
                <div class="">
                    @if(isset($partner->user->avatar_original))
                        <img src="{{Storage::disk('s3')->url($partner->user->avatar_original)}}" class="img-lg img-circle" alt="Profile Picture">
                        @else
                        <i class="fa fa-user fa-3x"></i>
                    @endif
                </div>
                <h4 class="text-lg text-overflow mar-no">{{ $partner->user->name }}</h4>
            </div>
            <hr>

            <!-- Profile Details -->
            <p class=" text-main text-sm text-uppercase text-bold">{{translate('Peer partner details')}}</p>

            <p>{{translate('Name')}} : {{ $partner->name }}</p>
            <p>{{translate('Email')}}: {{ $partner->email }}</p>
            <p>{{translate('Phone')}}: {{ $partner->phone }}</p>
            <p>{{translate('Address')}}: {{ $partner->address }}</p>

            <p class="pad-ver text-main text-sm text-uppercase text-bold">{{translate('Social media information')}}</p>

            @if($partner->facebook == 1)
                 <p><strong>Facebook</strong></p>
                 <p><a href="{{$partner->facebook_page}}" target="_blank">{{translate('page name')}} : {{ $partner->facebook_page }}</a></p>
                 <p>{{translate('Followers/Likers')}} : {{ $partner->facebook_follower }}</p>
            @endif
           
            @if($partner->instagram == 1)
                 <p><strong>Instagram</strong></p>
                 <p><a href="{{$partner->instagram_page}}" target="_blank">{{translate('page name')}} : {{ $partner->instagram_page }}</a></p>
                 <p>{{translate('Followers/Likers')}} : {{ $partner->instagram_follower }}</p>
            @endif

            @if($partner->linkedin == 1)
                 <p><strong>LinkedIn</strong></p>
                 <p><a href="//{{$partner->linkedin_page}}" target="_blank">{{translate('Profile name')}} : {{ $partner->linkedin_page }}</a></p>
                 <p>{{translate('Followers/Likers')}} : {{ $partner->linkedin_follower }}</p>
            @endif

            <hr>

           <!--  @if($partner->verification_status == 1)
                <p><strong>Order Discount: </strong>&nbsp;{{ $partner->discount }}%</p>
                <p><strong>Commision Rate: </strong>&nbsp;{{ $partner->commission }}%</p>
            @endif -->

        </div>
    </div>
</div>
