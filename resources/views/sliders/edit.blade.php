<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">{{translate('Slider Information')}}</h3>
    </div>

    <!--Horizontal Form-->
    <!--===================================================-->
    <form class="form-horizontal" action="{{ route('sliders.update_slider',$slider->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('URL')}}</label>
                <div class="col-sm-9">
                    <input type="text" id="url" name="url" placeholder="http://example.com/" class="form-control" value="{{ $slider->link }}" required>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <label class="control-label">{{translate('Slider Images')}}</label>
                    <strong>(1200px*380px)</strong>
                </div>
                <div class="col-sm-9">
                    @if ($slider->photo != null)
                        <div class="col-md-4 col-sm-4 col-xs-6">
                            <div class="img-upload-preview">
                                <img loading="lazy"  src="{{ my_asset($slider->photo) }}" alt="" class="img-responsive">
                                <input type="hidden" name="previous_photo" value="{{ $slider->photo }}">
                                <button type="button" class="btn btn-danger close-btn remove-files"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    @endif
                    <div id="photo" style="display: none;">

                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-3">
                    <label class="control-label">{{translate('Mobile View Photo')}}</label>
                    <strong>(800px*600px)</strong>
                </div>
                <div class="col-sm-9">
                    @if ($slider->mobile_photo != null)
                        <div class="col-md-4 col-sm-4 col-xs-6">
                            <div class="img-upload-preview">
                                <img loading="lazy"  src="{{ my_asset($slider->mobile_photo) }}" alt="" class="img-responsive">
                                <input type="hidden" name="previous_mobile_photo" value="{{ $slider->mobile_photo }}">
                                <button type="button" class="btn btn-danger close-btn remove-photo"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    @endif
                    <div id="mobile_photo"  style="display: none;">

                    </div>
                </div>
            </div>
            <!-- 07-10-2021 -->
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('Link Type')}}</label>
                <div class="col-sm-9">
                    <select name="link_type" class="form-control" required>
                        <option value="default" <?php if($slider->link_type == 'default'){echo 'selected';}?>>Default</option>
                        <option value="category" <?php if($slider->link_type == 'category'){echo 'selected';}?>>Category</option>
                        <option value="subcategory" <?php if($slider->link_type == 'subcategory'){echo 'selected';}?>>Sub category</option>
                        <option value="subsubcategory" <?php if($slider->link_type == 'subsubcategory'){echo 'selected';}?>>Sub sub category</option>
                        <option value="flashdeal" <?php if($slider->link_type == 'flashdeal'){echo 'selected';}?>>Flash Deal</option>
                        <!-- <option value="search" <?php //if($slider->link_type == 'search'){echo 'selected';}?>>Search</option> -->
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
        $('.remove-files').on('click', function(){
            $(this).parents(".col-md-4").remove();
            $("#photo").removeAttr('style');
        });

        $('.remove-photo').on('click', function(){
            $(this).parents(".col-md-4").remove();
            $("#mobile_photo").removeAttr('style');
        });

        $("#photo").spartanMultiImagePicker({
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

        $("#mobile_photo").spartanMultiImagePicker({
            fieldName:        'mobile_photo',
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
