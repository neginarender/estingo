
@extends('frontend_new.layouts.app')
@section('content')


        <!-- main content start -->
        <section class="gry-bg py-4 profile">
          <div class="container">
               <div class="row cols-xs-space cols-sm-space cols-md-space">
                                 @include('frontend_new.inc.customer_side_nav')

                <div class="col-lg-9">
                    <!-- Page title -->
                    <div class="page-title">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-12">
                                <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                    Manage Profile
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li><a href="user-dashboard.html">Dashboard</a></li>
                                        <li class="active"><a >Manage Profile</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  content -->
                    <form class="" action="{{route('phoneapi.updateuserinfo')}}" method="POST" >
                           <div class="form-box bg-white mt-4">


                            <div class="form-box-title px-3 py-2">
                                Basic info
                            </div>
                            <div class="form-box-content p-3">
                                 @if(session()->has('messagee'))
                                                <div class="alert alert-success">
                                                    {{ session()->get('messagee') }}
                                                </div>
                                            @endif
                                <div class="row">

                                   <input type="hidden" name="_token" value="{{csrf_token()}}" />

                                    <div class="col-md-2">
                                        <label>Your Name</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control mb-3" placeholder="Your Name" name="name" value="{{ $user->name }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Your Phone</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control mb-3" placeholder="Your Phone" name="phone" value="{{ $user->phone }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Photo</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="file" name="photo" id="file-3" class="custom-input-file custom-input-file--4" data-multiple-caption="{count} files selected" accept="image/*">
                                        <label for="file-3" class="mw-100 mb-3">
                                            <span></span>
                                            <strong>
                                                <i class="fa fa-upload"></i>
                                                Choose image
                                            </strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Your Password</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="password" class="form-control mb-3" placeholder="New Password" value="" name="new_password">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Confirm Password</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="password" class="form-control mb-3" placeholder="Confirm Password" name="confirm_password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-styled btn-base-1">Update Profile</button>
                        </div>

                        <div class="form-box bg-white mt-4">
                            <div class="form-box-title px-3 py-2">
                                Addresses
                            </div>
                            <div class="form-box-content p-3">
                                <div class="row gutters-10" id="address">

                                    @foreach($addres as $address)
                                    <div class="col-lg-6">
                                        <div class="border p-3 pr-5 rounded mb-3 position-relative">
                                            <div>
                                                <span class="alpha-6">Name:</span>
                                                <span class="strong-500 ml-2">{{$address->name}}</span>
                                            </div>
                                            <div>
                                                <span class="alpha-6">Address:</span>
                                                <span class="strong-500 ml-2">{{$address->address}}</span>
                                            </div>
                                            <div>
                                                <span class="alpha-6">Postal Code:</span>
                                                <span class="strong-500 ml-2">{{$address->postal_code}}</span>
                                            </div>
                                            <div>
                                                <span class="alpha-6">City:</span>
                                                <span class="strong-500 ml-2">{{$address->city}}</span>
                                            </div>
                                            <div>
                                                <span class="alpha-6">Country:</span>
                                                <span class="strong-500 ml-2">India</span>
                                            </div>
                                            <div>
                                                <span class="alpha-6">Phone:</span>
                                                <span class="strong-500 ml-2">{{$address->phone}}</span>
                                            </div>
                                             <div class="position-absolute right-0 bottom-0 pr-2 pb-3">
                                                @if($address->set_default == 1)
                                                    <span class="badge badge-primary bg-base-1">Default</span>
                                                @endif
                                                </div>
                                                <div class="dropdown position-absolute right-0 top-0">
                                                <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item" href="" onclick="removeAddres({{ $address->id }})">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                      <div class="col-lg-6 mx-auto">
                                         <a href="{{route('phoneapi.shippingadress')}}">
                                            <div class="border p-3 rounded mb-3 c-pointer text-center bg-light">
                                                <i class="fa-3x">+</i>
                                                <div class="alpha-7">Add New Address</div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>

                    <form action="{{route('phoneapi.updateemail')}}" method="POST">
                             <div class="form-box bg-white mt-4">
                                <div class="form-box-title px-3 py-2">
                                    Change your email
                                </div>
                                <div class="form-box-content p-3">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Your Email</label>
                                        </div>
                                        <div class="col-md-10">
                                            @error('email')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                            @if(session()->has('message'))
                                                <div class="alert alert-success">
                                                    {{ session()->get('message') }}
                                                </div>
                                            @endif
                                            <div class="input-group mb-3">
                                             <input type="hidden" name="_token" value="{{csrf_token()}}" />

                                              <input type="email" class="form-control" placeholder="Your Email" name="email" value="{{ $user->email }}">
                                              <div class="input-group-append">
                                                 <button type="button" class="btn btn-outline-secondary new-email-verification" onclick="emailverify('{{ $user->email }}')">
                                                    
                                                     <span class="">Verify</span>
                                                 </button>
                                              </div>
                                            </div>
                                            <button class="btn btn-styled btn-base-1" type="submit">Update Email</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    
                        
                </div>
               

                </div>
            </div>
        </section>



@endsection
@section('script')
    <script type="text/javascript">
        function emailverify(id){
             // alert(id);
            $.post('{{ route('phoneapi.emailverify') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                // $('#wishlist'+id).hide();
                 // window.location.reload();
                showFrontendAlert('success', 'Email already verified ');

            })
        }

    </script>
@endsection
@section('script')
    <script type="text/javascript">
        function removeAddres(id){

        
            $.post('{{ route('address.delete') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#address').html(data);
                // $('#wishlist'+id).hide();
                window.location.reload();
                showFrontendAlert('success', 'Shipping information has been deleted');
            })
        }
    </script>
@endsection

@section('script')
    <script type="text/javascript">
        function verify(id){

            alert("hello");

        
            $.post('{{ route('address.delete') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#address').html(data);
                // $('#wishlist'+id).hide();
                window.location.reload();
                showFrontendAlert('success', 'Shipping information has been deleted');
            })
        }
    </script>
@endsection
