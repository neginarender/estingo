@extends('layouts.app')

@section('content')

<table class="table table-striped">
  <caption>Access Switch</caption>
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Sorting Hub Name</th>
      <th scope="col">Status</th>
      <th scope="col">Switch</th>
    </tr>
  </thead>
  <tbody>
  <tr>
      <th scope="row"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></th>
      <td>Global</td>
      <td>Active</td>
      <td><label class="switch">
      <input onchange="updateGlobalSwitch(this)" value="{{$global->id}}" type="checkbox" @if($global->access_switch == 1) checked @endif>
      <span class="slider round"></span></label>
      </td>
  </tr>

  <tr>
      <th scope="row"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></th>
      <td>Peer Partner</td>
      <td>Active</td>
      <td><label class="switch">
      <input onchange="updateGlobalSwitch(this)" value="{{$peer->id}}" type="checkbox" @if($peer->access_switch == 1) checked @endif>
      <span class="slider round"></span></label>
      </td>
  </tr>
  <tr>
      <th scope="row"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></th>
      <td>Delivery Boy</td>
      <td>Active</td>
      <td><label class="switch">
      <input onchange="updateGlobalSwitch(this)" value="{{$delivery_boy->id}}" type="checkbox" @if($delivery_boy->access_switch == 1) checked @endif>
      <span class="slider round"></span></label>
      </td>
  </tr> 
  @foreach($sorting_hubs as $key=>$value)
    
    <tr>
      <th scope="row">{{$key+1}}</th>
      <td>{{$value->user->name}}</td>
      <td>@if($value->status == 1) Active @else Inactive @endif</td>
      <td><label class="switch">
      <input onchange="update_switch(this)" value="{{$value->id}}" type="checkbox"  @if($value->access_switch == 1) checked @endif>
      <span class="slider round"></span></label>
      </td>
    </tr>
   @endforeach 
  </tbody>
</table>

<form class="row g-3" action = {{route('DOFO.update-old-commission')}} method = "post">
@csrf
  <div class="col-auto">
    <label for="inputPassword2" class="visually-hidden">Order Code</label>
    <input type="text" class="form-control " id="inputPassword2" placeholder="CODE" name="code">
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary mb-3">Add Commission</button>
  </div>
</form>


<div class="panel-body">
            <form class="form-horizontal" action="{{ route('DOFO.upload-peer') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" class="form-control" name="peer_upload" required>
                </div>
                <div class="form-group">
                    <div class="col-lg-12">
                        <button class="btn btn-primary" type="submit">{{translate('Upload Peer CSV')}}</button>
                    </div>
                </div>
            </form>
</div>

@endsection
<script type = "">
    function update_switch(el){
                if(el.checked){
                    var status = 1;
                }
                else{
                    var status = 0;
                }
                console.log(status);
                $.post('{{ route('DOFO.change-access-switch') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                    if(data == 1){
                        showAlert('success', 'Access Switch  updated successfully');
                    }
                    else{
                        showAlert('danger', 'Something went wrong');
                    }
                });
    }



    function updateGlobalSwitch(el){
        if(el.checked){
            var status = 1;
        }else{
            var status = 0;
        }

        $.post('{{ route('DOFO.change-global-access-switch') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                    if(data == 1){
                        showAlert('success', 'Access Switch  updated successfully');
                    }
                    else{
                        showAlert('danger', 'Something went wrong');
                    }
        });


    }

</script>