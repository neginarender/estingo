@php
$shortId = "";
if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
            $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
        }
@endphp
<div class="modal-body p-4 added-to-cart">
    <div class="text-center text-success">
        <i class="fa fa-check"></i>
        <h3>Item added to your cart!</h3>
    </div>
    <div class="product-box">
        <div class="block">
            <div class="block-image">
                <img src="{{ static_asset('frontend/images/placeholder.jpg') }}" data-src="{{ my_asset($product->thumbnail_img) }}" class="lazyload" alt="Product Image">
            </div>
            <div class="block-body">
                <h6 class="strong-600">
                    {{ __($product->name) }}
                </h6>
                <div class="row align-items-center no-gutters mt-2 mb-2">
                    <div class="col-sm-2">
                        <div>Price:</div>
                    </div>

                    @php
                    $product_price = 0;
                    $id = $data['id'];
                    $product =  App\Product::findOrFail($id);
                    if(Session::has('referal_discount')){
                                                                        
                    if(!empty($shortId)){
                       
                        $product_price = (peer_discounted_newbase_price($data['id'],$shortId)*$data['quantity']); 
                    }
                    else{
                        
                        $product_price = (peer_discounted_newbase_price($data['id'])*$data['quantity']);
                        }
                    }
                    else{
                        $product_price = $data['price'];
                        //$product_price = ($data['price']+$data['tax']);
                        }
                     
                     @endphp
                    <div class="col-sm-10">
                        <div class="heading-6 text-danger">
                            <strong>
                                {{ single_price($product_price) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center">
        <button class="btn btn-styled btn-base-1 btn-outline mb-3 mb-sm-0" data-dismiss="modal">Back to shopping</button>
        <a href="{{ route('cart') }}" class="btn btn-styled btn-base-1 mb-3 mb-sm-0">Proceed to Checkout</a>
    </div>
</div>
