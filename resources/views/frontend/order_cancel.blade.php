@extends('frontend.layouts.app')
<style>
.error{
    color:red;
    font-size:12px;
}
</style>
@section('content')

    <section class="gry-bg py-4 profile">

        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                    @if(Auth::user()->user_type == 'seller')
                        @include('frontend.inc.seller_side_nav')
                    @elseif(Auth::user()->user_type == 'customer')
                        @include('frontend.inc.customer_side_nav')
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-12 d-flex align-items-center">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{__('Cancel Order')}}
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <form class="" action="{{route('order.cancel', $order->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}" />
                            <div class="form-box bg-white mt-4">
                                <div class="form-box-content p-3">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Order No.')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control mb-3" name="order_no" placeholder="{{__('Order No.')}}" value="{{ $order->code }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Order Amount')}} <span class="required-star">*</span></label>
                                        </div>
                                        <?php $refund_amount = $order->grand_total;
                                        if($order->payment_type=='razorpay' && !empty($order->wallet_amount))
                                        {
                                            $refund_amount = $order->grand_total+$order->wallet_amount;
                                        }
                                        ?>
                                        <div class="col-md-10">
                                            <input type="number" class="form-control mb-3" name="name" placeholder="{{__('Amount')}}" value="{{ $refund_amount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Cancel Reason')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control mb-3" name="reason" placeholder="Cancel Reason"></textarea>
                                            @if($errors->has('reason'))
                                                <div class="error">{{ $errors->first('reason') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Refund Reason')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea name="reason" rows="8" class="form-control mb-3"></textarea>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <div class="form-box mt-4 text-right">
                                <button type="submit" class="btn btn-styled btn-base-1">{{ __('Cancel Order') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
