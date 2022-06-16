@extends('layouts.app')
@section('content')

  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('Create News')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('sorthinghub.store_news') }}" method="POST" enctype="multipart/form-data">
        @csrf
          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">

               <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('News')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('News')}}" id="news" name="news" value="{{@$getNews['news']}}"  class="form-control" required>
                  </div>
                </div>
              </div>
            </div>
          </div>  
          <div class="panel-footer text-right">
              <button class="btn btn-purple" type="submit">{{translate('Save')}}</button>
          </div>
      </form>

    </div>
  </div>
@endsection