<div class="row">
           <div class="col-md-2">
           <img loading="lazy" class="img-md" src="{{ $image }}" alt="Image" style="width: 50px !important;"> </div>
           <div class="col-md-10">
            <strong>{{ $product_name }}</strong>
            <p>{{ $variant }}</p>
           </div>  
       </div>
       <div class="row" style="margin-top:20px;">
       <div class="col-md-2">
            <label>Distributors</label>
        </div>
        <div class="col-md-8">
            <select class="form-control demo-select2" name="distributors[]" id="distributors" multiple>
                @forelse($distributors as $key => $distributor)
				<option value="{{$distributor->id}}" @if(in_array($distributor->id,$mapped_distributors)) selected @endif>{{$distributor->name}}</option>
				@empty
				@endforelse                      
            </select>
        </div>
       </div>