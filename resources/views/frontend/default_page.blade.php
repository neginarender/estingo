@extends('frontend.layouts.app')

@section('content')
<style type="text/css">
	.brands section {
    padding: 30px 0 80px;
}  .brands section  img{max-width: 100%}
	.sec-1{background: #FAD6A6;}
	.brands section.sec-2 {
    background: #CEFFF6;
}
.brands section h2, .partners section h2 {
    font-weight: 600;
    line-height: 35px;
    margin-bottom: 20px;
    font-size: 28px;
}
.brands section h5 {
    margin-top: 0;
}
.brands section img.small {
    max-width: 80%;
}
</style>
 <!-- <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"> -->
<!--     <section class="gry-bg py-5">
        <div class="profile">
            <div class="container">
                <div class="row">
                  Custome page
                </div>
            </div>
        </div>
    </section> -->
    <div class="brands">
    <div class="p-header     ">
 			 
	 	<img  style="max-width: 100%" src="{{ static_asset('frontend/images/brands/banner.jpg') }}" alt="{{ env('APP_NAME') }}">
					 
			 
			 
	  </div>	

		  <section class="sec-1" >
		  		<div class="container">
						<div class="row mt-5">
							<div class="col-lg-6 d-block d-md-none">
								 	<img   src="{{ static_asset('frontend/images/brands/img-1.png') }}" alt="{{ env('APP_NAME') }}">
							</div>
							<div class="col-lg-6 col-md-6 ">
								<h5> Shelly's</h5>
								<h2> Shelly’s originally started as a small initiative by Mr Ganguly to market the pickles and chutneys made by his wife. </h2>
								<p>What started as a small facility in and around Shantiniketan in West Bengal with a few products has now become a popular brand all over east India. Shelly’s at present hosts around 71 products many of which like olive jams, palm candy are first of their kind in the market. They create classic Bengali flavours of the bygone times and are known for their traditional handmade taste. At present Shelly’s offer pickles/chutney/kasundi, ready to mix food, ready to cook food, pure spices and ghee and Gobindo Bhog rice. </p>
								<a target="_blank" href="http://13.234.113.69/rozana/" class="btn btn-primary">Shop Now</a>
								 
							</div>

							<div class="col-lg-5 ml-auto col-md-6 d-none d-md-block">
								  
								 	<img   src="{{ static_asset('frontend/images/brands/img-1.png') }}" alt="{{ env('APP_NAME') }}">
							</div>
							
						</div>
					 
						 
					</div>
		  	
		  </section>

		  <section class="sec-2" >
		  		<div class="container">
						<div class="row mt-5">
							<div class="col-lg-5 pt-3 ">
								 	<img   src="{{ static_asset('frontend/images/brands/img-2.png') }}" alt="{{ env('APP_NAME') }}">
							</div>
							<div class="col-lg-6 col-md-6 ml-auto">
								<h5> Thirsty bees</h5>
								<h2> Thirsty Bees is into fruit processing lines and uses novel cold membrane technology to separate water from the juice, unlike the conventional heating process. </h2>
								<p>This retains the natural flavours and preserves the essential nutrients. Their products include canned goods and beverages. At present Kai’s is producing two variants of canned pineapples from handpicked GI tagged ‘Vazhakulam Pineapple’ of Mauritius variety. Mauritius variety pineapples are conical in shape unlike the other varieties, hence the coring may appear irregular. One product comes with cold concentrated pineapple juice, the other comes in sugar syrup. They use Cold Concentration Technology to produce pineapple juice to cushion the slices and present it in a fruit like atmosphere unlike the other similar products that come packed in sugar syrup alone. </p>
								<a target="_blank" href="http://13.234.113.69/rozana/" class="btn btn-primary">Shop Now</a>
								 
							</div>

							 
							
						</div>
					 
						 
					</div>
		  	
		  </section>

		   <section class="sec-1" >
		  		<div class="container">
						<div class="row mt-5">
							<div class="col-lg-6 d-block d-md-none">
								 	 
								 		<img   src="{{ static_asset('frontend/images/brands/banner-img.png') }}" alt="{{ env('APP_NAME') }}">
							</div>
							<div class="col-lg-6 col-md-6 ">
								<h5> Fresh Cartons - Sea Food, Tiger Prawns, Asian Sea Bass Fillet and many other fishes</h5>
								<h2> Our hatcheries based out of Odisha uses the best methodology to make the finest quality prawns. </h2>
								<p>Most of our products are shipped to Europe and other countries. Our production team has many decades of experience in working on highest quality of prawns. Our each product is curated by a team of experts. We now look to serve the Indian market which is bustling and looking for high quality food products produced in India. We maintain highest quality of standards for hygienic. We also deal with various type of freshwater and sea-water fishes/shell fishes. We look to serve you with best quality products. </p>
								<a target="_blank" href="http://13.234.113.69/rozana" class="btn btn-primary">Shop Now</a>
								 
							</div>

							<div class="col-lg-5 ml-auto col-md-6 d-none d-md-block">
								 	<img   src="{{ static_asset('frontend/images/brands/banner-img.png') }}" alt="{{ env('APP_NAME') }}">
							</div>
							
						</div>
					 
						 
					</div>
		  	
		  </section>

		  <section class="sec-2" >
		  		<div class="container">
						<div class="row mt-5">
							<div class="col-lg-5  ">
								 	<img   src="{{ static_asset('frontend/images/brands/img-3.png') }}" alt="{{ env('APP_NAME') }}">
							</div>
							<div class="col-lg-6 col-md-6 ml-auto">
								<h5>Fresh Cartons - Cold Cuts</h5>
								<h2> Cold cuts are now our daily dose of protein and all our needs for a hearty meal. </h2>
								<p>We curate the best meat products with highest standards of hygiene and taste. Our objective is to provide high value products made in a hygienic manner with premium taste. Many of the cold cuts available in the market are made using cheap processing units and questionable quality of raw materials. We use state of the art facility and processing with premium raw materials to make sure we get best tasting healthy products. </p>
								<a target="_blank" href="http://13.234.113.69/rozana" class="btn btn-primary">Shop Now</a>
								 
							</div>

							 
							
						</div>
					 
						 
					</div>
		  	
		  </section>

		   <section class="sec-1 " >
		  		<div class="container">
						<div class="row mt-5 ">
							<div class="col-lg-6 d-block d-md-none text-center">
								 	 
								 	<img   src="{{ static_asset('frontend/images/brands/img-4.png') }}" alt="{{ env('APP_NAME') }}" class="small">
							</div>
							<div class="col-lg-6 col-md-6 ">
								<h5> Indosphere- Naturally Flavoured Honey</h5>
								<h2> We believe that holistic wellness can be achieved only using natural ingredients that you can eat. </h2>
								<p>Indosphere Honey is produced by multifarious bees and is repacked directly from the honeycombs. It is 100% natural and free of any preservatives. Honey has many health benefits, for instance, it acts as an energy booster, is an anti-bacterial, is an anti-oxidant, cough suppressant and many more. Made by bees, collected by humans</p>
								<p> <strong>We collect:</strong> Ban Tulsi Honey, Litchi Honey, Mustard Honey & Eucalyptus Honey </p>
								<a target="_blank" href="http://13.234.113.69/rozana" class="btn btn-primary">Shop Now</a>
							</div>

							<div class="col-lg-5 ml-auto col-md-6 d-none d-md-block">
								 	<img   src="{{ static_asset('frontend/images/brands/img-4.png') }}" alt="{{ env('APP_NAME') }}" class="small">
							</div>
							
						</div>
					 
						 
					</div>
		  	
		  </section>

		  <section class="sec-2" >
		  		<div class="container">
						<div class="row mt-5">
							<div class="col-lg-5 text-center pt-2">
								 <img   src="{{ static_asset('frontend/images/brands/img-5.png') }}" alt="{{ env('APP_NAME') }}" class="small">
							</div>
							<div class="col-lg-6 col-md-6 ml-auto">
								<h5>Janaki Shuchi</h5>
								<h2> We are Janki Organic Foods, a smart Agro company that believes in the goodness of nature. </h2>
								<p>Janki refers to goddess Sita from Ramayana who was born from the mother earth. Our products Shuchi, meaning the taste of ritual purity, is the best mixture of Nature & Tradition.</p>
								<p> For the better health and immunity of the family he started making oil for his family which is 100% Natural, Cholesterol free, and Fats free. We use to share this with our neighbour and friend, soon few doctors started buying the same from us which also proved beneficial to their patients and slowly and gradually it took a shape of Natural Business which over shadowed by the big corporate companies.</p>
								<p> Our products are pure, natural and produced through time-proven methods that retain maximum nutrition without using harsh chemicals. By leveraging our experience, expertise and skills, we ensure the highest quality of products to our customers. We at Janki also prepare a juice of 21 plant leaf which came from Ayurveda and has Medicinal values for human body. </p>
								 <a target="_blank" href="http://13.234.113.69/rozana" class="btn btn-primary">Shop Now</a>
								 
							</div>

							 
							
						</div>
					 
						 
					</div>
		  	
		  </section>


		   <section class="sec-1 " >
		  		<div class="container">
						<div class="row mt-5 ">
							<div class="col-lg-6 d-block d-md-none text-center">
								 <img   src="{{ static_asset('frontend/images/brands/img-6.png') }}" alt="{{ env('APP_NAME') }}" class="small">
							</div>
							<div class="col-lg-6 col-md-6 ">
								<h5> Organic Soul</h5>
								<h2> A collective initiative by the farmers, for your well-being.</h2>
								<p>The idea behind the brand is to provide good quality organic produce at a fair price to the consumer, at the same time giving fair returns to the farming community thereby encouraging them to convert their farms & grow organic crop consistently. </p>
								<p> This brand was started by Garima Jain who thought of partnering with the farmers and making them an integral part of their company. The whole concept came together in an endeavour to provide a good marketing platform to the various farming communities who they dealt with. </p>
								<p>Currently Organic Soul has over 100 farmers whom they have encouraged to convert to organic farming and have paid for their conversion audits as well</p>
								<a target="_blank" href="http://13.234.113.69/rozana" class="btn btn-primary">Shop Now</a>
								 
							</div>

							<div class="col-lg-5 ml-auto col-md-6 d-none d-md-block">
								 	<img   src="{{ static_asset('frontend/images/brands/img-6.png') }}" alt="{{ env('APP_NAME') }}"  >
							</div>
							
						</div>
					 
						 
					</div>
		  	
		  </section>

		</div>
		 
@endsection

