@extends('frontend.layouts.app')

@section('content')
 <style type="text/css">
 	.career-page {padding-bottom: 0}
 	.career-page  .form-group {position: relative;}
 	.career-page  .form-group i {position: absolute; right: 10px; color:#194566; font-size: 22px; top: 10px}
 	.career-page  .form-group .file {display: none;}
 	.career-page  .form-group .label{cursor: pointer;}
 	.career-page  .form-group span {padding-left: 10px; display: none; color:#EF252D; font-weight: 600 }
 	.jobs {padding: 50px 0  }
 	 
 	.filters .form-control{background: none; border:solid 1px #ccc; border-radius: 0}
 	.filters label {font-size: 16px; font-weight: 600}
 	.filters .txt {margin-top: 35px}
 	.jobs .table tr td {font-size: 14px; color: #333; vertical-align: top; border-color: #999}
 	.jobs .table tr th {font-weight: 600; font-size: 14px;vertical-align: top; border-color: #999}
 		.jobs .filters h3 span {font-size: 14px; font-weight: 500}
 	.searchbox {position: relative;}
 	.searchbox .btnn {position: absolute; border:0; background: 0; right: 10px; top: 10px; font-size: 18px; outline: none;}
 	.jobs .table tr td:nth-child(3) {width: 30%}

 	@media (max-width: 800px){
 		.jobs .table {width: 1000px}
 		.jobs .table tr td,.jobs .table tr th {font-size: 13px}
 		.jobs .table tr td:nth-child(3) {width: auto}
 	}
 </style>

<div class="career-page"> 
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<h1>Grow with us!</h1>
				@if ($errors->any())
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<form action = "{{route('careers.store')}}" method = "post" enctype="multipart/form-data">
				@csrf
					<div  class="form-group">
						<input type="text" placeholder="Name" name="name" class="form-control">
					</div>
					<div  class="form-group">
						<input type="text" placeholder="Contact Number" name="mobile" id="mobile" class="form-control" required>
					</div>
					<div  class="form-group">
						<input type="text" placeholder="Email" name="email" class="form-control" id = "email" required>
					</div>
					<div  class="form-group">
						<!-- <i class="fa fa-search"></i> -->
						<input type="text" placeholder="Area of Expertise" name="expertise" class="form-control" required>
					</div>
					<div  class="form-group">
						 
						<select class="form-control" name="designation_id">
							<option class="d-none">Applying for</option>
							@foreach($openings as $key => $opening)
								<option value="{{$opening->id}}">{{$opening->designation}}</option>
							@endforeach
						</select>
					</div>
					<div  class="form-group">
						<label class="font-weight-600 text-dark label d-block"><i class="fa fa-upload"></i>Upload your Resume here
						<input type="file" name="cv" class="form-control file" required></label>
						<span id="file"></span>
					</div>
					<div  class="form-group">
						<input type="text" placeholder="Write about yourself in 150 words" name="intro" class="form-control">
					</div>
				

					<div  class="form-group text-center">
						<button class="btn" type="submit">Submit</button>
					</div>
					 
				</form>

			</div>
		</div>
	</div>

		<div class="jobs bg-white">
			<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="filters">
						<form name="career_search" id="career_search" action="{{route('careers.search')}}" method="POST">
						<div class="row">
							@csrf
							<div class="col-md-3">
								<label class="bold">Filters</label>
								<select class="form-control mt-2" name="location" id="joblocation">
									<option class="d-none">Select location</option>
									<!-- <option>NCR, Bangalore and Lucknow</option>-->
									<option value="all">All </option> 
									@foreach($location as $key => $value)
										<option value="{{$value->id}}">{{$value->city}}</option>
									@endforeach
								</select>
							</div>
							<!-- <div class="col-md-6 ml-auto  ">
								<div class="searchbox">
									<button class="btnn"><i class="fa fa-search"></i></button>
									<input type="text" placeholder="Search jobs.." class="form-control  txt" name="text_search"> 
								</div>
							</div> -->
						
							<div class="col-md-12  ">
								<h3 class="mt-4">Open Positions <span>({{count(App\Opening::where('status','1')->get())}} positions available)</span></h3>
							</div>

						</div>
						</form>
					</div>
					<div class="table-responsive mt-4">
						<table class="table table-striped table-bordered">
							<thead>
								<th>S.No.</th>
								<th>Designation</th>
								<th>Job Role</th>
								<th>Number of Positions</th>
								<th>Location</th>
								<th>Monthly Take Home Salary</th>
								<th>Education Required</th>
								<th>Experience Required</th>
							</thead>
							<tbody>
								@if(count($openings) > 0)
									@foreach($openings as $key => $opening)
									<tr>
										<td>{{$key + 1}}</td>
										<td>{{$opening->designation}} </td>
										<td>{{$opening->role}}</td>
										<td>{{$opening->num_position}}</td>
										<!-- <td>{{$opening->location}} </td> -->
										<td>
										@php
										$name = array();
										$arr = explode(',',$opening->location);
											$locations = App\JobLocation::whereIn('id',$arr)->select('city')->get();
											foreach($locations as $key=>$value){
												array_push($name,$value->city);
											}
											$city_name = implode(',',$name);

										@endphp
										{{$city_name}}
										</td>
										<td>{{$opening->salary}}</td>
										<td>{{$opening->education_req}}</td>
										<td>{{$opening->experience_req}}</td>
									</tr>
									@endforeach
								@else
									<tr>
										<td colspan="8" style="text-align:center;">No position available</td>
									<tr>
								@endif
							</tbody>
						</table>
						<div class="clearfix">
			                <div class="pull-right">
			                   {{ $openings->appends(request()->input())->links() }}
			                </div>
			            </div>

					</div>
				</div>
				<!-- <div class="col-md-6">
					<div class="card">
						<div class="title">
							<h4 >Logistics Supervisor </h4>
							<a href="" class="btns">View Detail</a> 
						</div>
						<div class="info">
							<div class="row">
								<div class="col-md-7">
									<label>Number of Positions</label>
									<p class="value">1</p>
								</div>
								<div class="col-md-5">
									<label>Monthly Take Home Salary</label>
									<p class="value">50-80k</p>
								</div>
								<div class="col-md-7">
									<label>Education Required</label>
									<p class="value">Graduation. Anyone with SCM specialization will be added advantage</p>
								</div>
								<div class="col-md-5">
									<label>Experience Required</label>
									<p class="value">7-10 years</p>
								</div>
								<div class="col-md-12">
									<label>Location</label>
									<p class="value">NCR, Bangalore and Lucknow </p>
								</div>

							</div>

						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="title">
							<h4 >Logistics Supervisor </h4>
							<a href="" class="btns">View Detail</a> 
						</div>
						<div class="info">
							<div class="row">
								<div class="col-md-7">
									<label>Number of Positions</label>
									<p class="value">1</p>
								</div>
								<div class="col-md-5">
									<label>Monthly Take Home Salary</label>
									<p class="value">50-80k</p>
								</div>
								<div class="col-md-7">
									<label>Education Required</label>
									<p class="value">Graduation. Anyone with SCM specialization will be added advantage</p>
								</div>
								<div class="col-md-5">
									<label>Experience Required</label>
									<p class="value">7-10 years</p>
								</div>
								<div class="col-md-12">
									<label>Location</label>
									<p class="value">NCR, Bangalore and Lucknow </p>
								</div>

							</div>

						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="title">
							<h4 >Logistics Supervisor </h4>
							<a href="" class="btns">View Detail</a> 
						</div>
						<div class="info">
							<div class="row">
								<div class="col-md-7">
									<label>Number of Positions</label>
									<p class="value">1</p>
								</div>
								<div class="col-md-5">
									<label>Monthly Take Home Salary</label>
									<p class="value">50-80k</p>
								</div>
								<div class="col-md-7">
									<label>Education Required</label>
									<p class="value">Graduation. Anyone with SCM specialization will be added advantage</p>
								</div>
								<div class="col-md-5">
									<label>Experience Required</label>
									<p class="value">7-10 years</p>
								</div>
								<div class="col-md-12">
									<label>Location</label>
									<p class="value">NCR, Bangalore and Lucknow </p>
								</div>

							</div>

						</div>
					</div>
				</div> -->


			</div>

		</div>	
	</div>
</div>

@endsection

@section('script')
<script type="text/javascript">
 	$('#joblocation').on('change', function() {
        $('#career_search').submit();
    });
</script>
@endsection


