<select class="form-control city_id demo-select2" name="city_ids[]" multiple data-selected-text-format="count" data-actions-box="true">
						@if($state_ids!="")
                         @foreach (\App\City::where('status', 1)->whereIn('state_id',$state_ids)->get() as $key => $city)
                            <option value="{{$city->id}}" @if(in_array($city->id, $cities)) Selected @endif>{{$city->name}}</option>
                        @endforeach
                        @endif
                      </select>