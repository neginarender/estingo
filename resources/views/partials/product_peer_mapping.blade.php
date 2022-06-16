@if(count($products) > 0)
    <div class="col-sm-12">
        <input type="checkbox" id="selectall">Select all</input>
 
        <table class="table table-bordered">
    		<thead>
    			<tr>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('S.No')}}</label>
                    </td>
    				<td class="" width="30%">
    					<label for="" class="control-label">{{translate('Product')}}</label>
    				</td>
                    <td class="" width="15%">
                        <label for="" class="control-label">{{translate('Buying Price')}}</label>
                    </td>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('MRP')}}</label>
                    </td>
                    <td class="">
    					<label for="" class="control-label">{{translate('image')}}</label>
    				</td>
    				
                    <td class="text-center">
                        <label for="" class="control-label">{{translate('Action')}}</label>
                    </td>
                    <td class="text-center">
                        <label for="" class="control-label">{{translate('Flash Deal')}}</label>
                    </td>
    			</tr>
    		</thead>
    		<tbody>
                 @foreach ($products as $key => $product)
                @php
                 $variant_prices = App\ProductStock::where('product_id', $product->id)->latest('price')->first();

                 if(!empty($sortinghub_id)){
                 
                    $products =  App\MappingProduct::where(['sorting_hub_id'=>$sortinghub_id,'product_id'=>$product->id])->latest()->first();
                    $unit_price = $products['purchased_price'];
                    $variant_price = $products['selling_price'];
                    if($unit_price == 0 || $variant_price == 0){
                        $unit_price = $product->unit_price;
                        $variant_price = $variant_prices['price'];
                    }  

                }else{
                        $unit_price = $product->unit_price;
                        $variant_price = $variant_prices['price'];
                }


                @endphp 
                		<tr>
                            <td>
                                <label for="" class="control-label">{{ $key+1 }}</label>
                            </td>
                			<td>
                				<label for="" class="control-label">{{$product->name. ' ('.@$product->stocks[0]->variant. ')'}}
                                    <span style="display: block; text-align: left;">Sku -{{@$product->stocks[0]->sku}}</span>
                                </label>
                			</td>
                            <td>
                                 {{$unit_price}}
                            </td>
                            <td>
                                {{$variant_price}}
                            </td>     
                            <td>
                                <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image">
                            </td>
                           
                			<td align="center">
                				<input type="checkbox" class="form-control selectedId" name="products[]" value="{{$product->id}}" style="width: 16px;">
                			</td>

                            <td align="center">
                                <input type="checkbox" class="form-control selectedDeal" name="flash_deal[]" value="{{$product->id}}" style="width: 16px;" price = {{$variant_price}} >
                            </td>
                            
                		</tr>
                @endforeach
            </tbody>
        </table>
        
    </div>
@endif
<script type="text/javascript">
$(document).ready(function () {
    $('#selectall').click(function () {
        $('.selectedId').prop('checked', this.checked);
    });

    $('.selectedId').change(function () {
        var check = ($('.selectedId').filter(":checked").length == $('.selectedId').length);
        $('#selectall').prop("checked", check);
    });
});
</script>
