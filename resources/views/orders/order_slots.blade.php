
                                    
<div class="row">
                                            
                                                @if(count($todaySlot) == 0) 
                                                <input type="hidden" value="0" name="slot_flag" id="slot_flag">  
                                                <div class="col-md-4">
                                                    <label  ><input type="radio" id="today_slot_grocery" name="delivery_date" value="{{date('Y-m-d',strtotime($todayDate))}}" disabled>
                                                    <del>{{$todayDate}} (Today)</del></label>
                                                </div>
                                                
                                                @else
                                                <div class="col-md-4">
                                                   <label > <input type="radio" id="today_slot_grocery" name="delivery_date" value="{{date('Y-m-d',strtotime($todayDate))}}" checked>
                                                     <strong>{{$todayDate}} (Today)</strong></label>
                                                </div>

                                                @endif
                                                 <div class="col-md-6">
                                                   <label> <input type="radio" id="tommorow_slot_grocery" name="delivery_date" value="{{date('Y-m-d',strtotime($tommorowDate))}}" @if(count($todaySlot) == 0) checked="checked" @endif>
                                                     <strong> {{$tommorowDate}} (Tommorow)</strong></label>
                                                </div>
                                            </div>
                                            
                                            <div class="row" id="today_avail_slot_grocery">
                                                @if(count($todaySlot) != 0)  
                                                
                                                    @foreach($todaySlot as $key => $value)
                                                    <div class="col-md-3 pt-2 pt-md-3  col-sm-4 col-6 ">
                                                     <label class="d_time" >
                                                        <input @if($key == 0) checked="checked" @endif type="radio" class="delivery_slot" id="deliveryGrocerySlot_{{$key}}" name="delivery_slot_today" value="{{ dateFormatConvert($value['delivery_time']) }}">
                                                     <span> {{dateFormatConvert($value['delivery_time'])}}</span>
                                                     </label>
                                                    </div>
                                                    
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="row" id="tommorow_avail_slot_grocery" style="display: none;">
                                                @php
                                                $i = 0;
                                                @endphp
                                                @if(count($availSlotTom) != 0)
                                                    
                                                @foreach($availSlotTom as $key => $value)

                                                <div class="col-md-3 pt-2 pt-md-3 col-sm-4 col-6">
                                                    <label class="d_time">
                                                        <input @if($key == 0) checked="checked" @endif type="radio" class="delivery_slot_grocery_tom" id="deliveryGrocerySlotTom_{{$key}}" name="delivery_slot_tom" value="{{dateFormatConvert($value['delivery_time'])}}">
                                                    <span> {{dateFormatConvert($value['delivery_time'])}}</span>
                                                </label>
                                                </div>
                                                @endforeach 
                                                @endif
                                                
                                            </div>
</div>

<script type="text/javascript">
$('#today_slot_grocery').click(function(){
                $('#today_avail_slot_grocery').show();
                $('#tommorow_avail_slot_grocery').hide();
                $('.delivery_slot_grocery_tom').prop('checked', false);
                $('#deliveryGrocerySlot_0').prop('checked', true);
            });

            $('#tommorow_slot_grocery').click(function(){
                $('#tommorow_avail_slot_grocery').show();
                $('#today_avail_slot_grocery').hide();
                $('.delivery_slot').prop('checked', false);
                $('#deliveryGrocerySlotTom_0').prop('checked', true);
            });
</script>