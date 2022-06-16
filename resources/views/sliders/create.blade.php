<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">{{translate('Slider Information')}}</h3>
    </div>

    <!--Horizontal Form-->
    <!--===================================================-->
    @if(!in_array(Auth::user()->user_type, ['admin']))
    <form class="form-horizontal" action="{{ route('sorthinghub.storeBanner') }}" method="POST" enctype="multipart/form-data">
    @else
    <form class="form-horizontal" action="{{ route('sliders.store') }}" method="POST" enctype="multipart/form-data">
    @endif
        @csrf
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('URL')}}</label>
                <div class="col-sm-9">
                    <input type="text" id="url" name="url" placeholder="http://example.com/" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <label class="control-label">{{translate('Slider Images')}}</label>
                    <strong>(1200px*380px)</strong>
                </div>
                <div class="col-sm-9">
                    <div id="photos">

                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-3">
                    <label class="control-label">{{translate('Mobile View Slider')}}</label>
                    <strong>(800px*600px)</strong>
                </div>
                <div class="col-sm-9">
                    <div id="mobile_photos">

                    </div>
                </div>
            </div>
            <!-- 07-10-2021 -->
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('Link Type')}}</label>
                <div class="col-sm-9">
                    <select name="link_type" class="form-control" required>
                        <option value="default">Default</option>
                        <option value="category">Category</option>
                        <option value="subcategory">Sub category</option>
                        <option value="subsubcategory">Sub sub category</option>
                        <option value="flashdeal">Flash Deal</option>
                        <!-- <option value="search">Search</option> -->
                    </select>
                </div>
            </div>
        </div>
        <div class="panel-footer text-right">
            <button class="btn btn-purple" type="submit">{{translate('Save')}}</button>
        </div>
    </form>
    <!--===================================================-->
    <!--End Horizontal Form-->

</div>

<script type="text/javascript">
    $(document).ready(function(){
        $("#photos").spartanMultiImagePicker({
            fieldName:        'photos[]',
            maxCount:         1,
            rowHeight:        '200px',
            groupClassName:   'col-md-4 col-sm-9 col-xs-6',
            maxFileSize:      '',
            dropFileLabel : "Drop Here",
            allowedExt: "png|jpg|jpeg|webp",
            onExtensionErr : function(index, file){
                console.log(index, file,  'extension err');
                alert('Please only input png or jpg or webp type file')
            },
            onSizeErr : function(index, file){
                console.log(index, file,  'file size too big');
                alert('File size too big');
            }
        });

        $("#mobile_photos").spartanMultiImagePicker({
            fieldName:        'mobile_photos',
            maxCount:         1,
            rowHeight:        '200px',
            groupClassName:   'col-md-4 col-sm-9 col-xs-6',
            maxFileSize:      '',
            dropFileLabel : "Drop Here",
            allowedExt: "png|jpg|jpeg|webp",
            onExtensionErr : function(index, file){
                console.log(index, file,  'extension err');
                alert('Please only input png or jpg or webp type file')
            },
            onSizeErr : function(index, file){
                console.log(index, file,  'file size too big');
                alert('File size too big');
            }
        });
    });

</script>
