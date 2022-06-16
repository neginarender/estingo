@extends('layouts.app')
@section('content')
  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('Edit Job Vaccancy')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('opening.update', $openings->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">
              <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Designation')}}</label>
                  <div class="col-lg-9">
                      <input type="text" id="designation" name="designation" value="{{$openings->designation}}" class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Job Role')}}</label>
                  <div class="col-lg-9">
                      <input type="text" value="{{$openings->role}}" id="role" name="role"  class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Number of Positions')}}</label>
                   <div class="col-lg-9">
                      <input type="text" value="{{$openings->num_position}}" id="num_position" name="num_position"  class="form-control" required>
                  </div>
                </div>

                <!-- checkBox -->
                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Location')}}</label>
                   <div class="col-lg-9">
                    @php 
                        $location = explode(',',$openings->location);
                    @endphp
                    @foreach($jobLocations as $key => $value)
                      <input type="checkbox" id="location.{{$key}}" name="location[]" value="{{$value->id}}"
                      <?php 
                          if(in_array($value->id,$location)){
                              echo 'checked';
                          }
                      ?>
                      > {{$value->city}}
                    @endforeach
                  </div>
                </div>
                <!-- checkBox -->

                <!-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Location')}}</label>
                   <div class="col-lg-9">
                      <input type="text" value="{{$openings->location}}" id="location" name="location"  class="form-control" required>
                  </div>
                </div> -->

                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Monthly Take Home Salary')}}</label>
                   <div class="col-lg-9">
                      <input type="text" value="{{$openings->salary}}" id="salary" name="salary"  class="form-control" required>
                  </div>
                </div>
                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Education Required')}}</label>
                   <div class="col-lg-9">
                      <input type="text" value="{{$openings->education_req}}" id="education_req" name="education_req"  class="form-control" required>
                  </div>
                </div>
                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Experience Required')}}</label>
                   <div class="col-lg-9">
                      <input type="text" value="{{$openings->experience_req}}" id="experience_req" name="experience_req"  class="form-control" required>
                  </div>
                </div>

              </div>
            </div>
          </div>  

          <div class="panel-footer text-right">
              <button class="btn btn-purple" type="submit">{{translate('Update')}}</button>
          </div>
      </form>

    </div>
  </div>

@endsection