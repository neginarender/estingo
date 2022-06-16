@extends('frontend.layouts.app')


@section('content')
<style type="text/css">
	.career-detail {background: #fcfcfc; padding: 50px 0 150px}
	.career-detail .title  {margin-bottom: 40px}
 	.career-detail .title h4 {font-size: 24px; color: #db3335; font-weight: 600}
 	.career-detail  a.btns {  font-size: 16px; background:#194566; color: #fff; padding: 15px 30px ; display: inline-block;  border-radius: 30px;   }
 	.career-detail .title {padding-bottom: 15px; border-bottom: solid 1px #eee;}
 	.career-detail .info label {font-size: 14px; margin: 0 0 8px}
 	.career-detail .info .value {font-size: 15px; font-weight: bold; margin: 0 0 30px}
 	.career-detail .info label.head {font-size: 15px; margin: 0; font-weight: bold; display: block; margin-top: 25px; margin-bottom: 10px}
 	.career-detail .info .value.v1{font-size: 15px; line-height: 22px}

 	@media (max-width: 640px){
 		.career-detail {padding: 30px 0 50px}
 		.career-detail .title  {margin-bottom: 20px}
 		.career-detail .info .value {margin-bottom: 20px; font-size: 14px}
 		.career-detail .title h4 {font-size: 20px;}
 		.career-detail .info label  {font-size: 13px;}
 		.career-detail .info label.head {margin-top: 10px; font-size: 13px}
 		.career-detail .info .value.v1 {font-size: 14px}
 		.career-detail  a.btns {padding: 10px 20px 12px; font-size: 15px}
 	}
</style>

<div class="career-detail">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="title">
					<h4 >{{$openings->designation}} </h4>
				</div>
				<div class="info">
							<div class="row">
								<div class="col-md-3">
									<label>Number of Positions</label>
									<p class="value">{{$openings->num_position}}</p>
								</div>
								<div class="col-md-3">
									<label>Monthly Take Home Salary</label>
									<p class="value">{{$openings->salary}}</p>
								</div>
								<div class="col-md-6">
									<label>Education Required</label>
									<p class="value">{{$openings->education_req}}</p>
								</div>
								<div class="col-md-3">
									<label>Experience Required</label>
									<p class="value">{{$openings->experience_req}}</p>
								</div>
								<div class="col-md-9">
									<label>Location</label>
									<p class="value">{{$openings->location}} </p>
								</div>

								<div class="col-md-12">
									<label class="head">Job Role</label>
									<p class="value v1">{{$openings->role}}</p>
								</div>

								

							</div>

						</div>



				 <div class="text-center mt-4">	<a href="{{ route('careers.index') }}" class="btns">Apply Now</a>  </div>
			</div>
		</div>
	</div>

 

</div>

@endsection
