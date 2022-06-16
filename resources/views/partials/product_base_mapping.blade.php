@if(count($products) > 0)
    <div class="col-sm-12">
        <input type="checkbox" id="selectall">Select all</input>
 
        <table class="table table-bordered">
    		<thead>
    			<tr>
                    <td class="" width="10%">
                        <label for="" class="control-label">{{translate('S.No')}}</label>
                    </td>
    				<td class="" width="60%">
    					<label for="" class="control-label">{{translate('Product')}}</label>
    				</td>
                    <td class="">
    					<label for="" class="control-label">{{translate('image')}}</label>
    				</td>
    				
                    <td class="text-center">
                        <label for="" class="control-label">{{translate('Action')}}</label>
                    </td>
    			</tr>
    		</thead>
    		<tbody>
                @foreach ($products as $key => $product)
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
                                <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image">
                            </td>
                           
                			<td align="center">
                				<input type="checkbox" class="form-control selectedId" name="products[]" value="{{$product->id}}" style="width: 16px;">
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
