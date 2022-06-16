<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">{{translate('Banner Information')}}</h3>
    </div>

    <!--Horizontal Form-->
    <!--===================================================-->
    <form class="form-horizontal" action="{{ route('master_banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" value="PATCH">
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('URL')}}</label>
                <div class="col-sm-9">
                    <input type="text" placeholder="{{translate('URL')}}" id="url" name="url" value="{{ $banner->link }}" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <label class="control-label">{{translate('Banner Images')}}</label>
                    <strong>({{ translate('850px*420px') }})</strong>
                </div>
                <div class="col-sm-9">
                    @if ($banner->photo != null)
                        <div class="col-md-4 col-sm-4 col-xs-6">
                            <div class="img-upload-preview">
                                <img loading="lazy"  src="{{ my_asset($banner->photo) }}" alt="" class="img-responsive">
                                <input type="hidden" name="previous_photo" value="{{ $banner->photo }}">
                                <button type="button" class="btn btn-danger close-btn remove-files"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    @endif
                    <div id="photo">

                    </div>
                </div>
            </div>
            <!-- 07-10-2021 -->
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('Link Type')}}</label>
                <div class="col-sm-9">
                    <select name="link_type" class="form-control" required>
                        <option value="">Select Link Type</option>
                        <option value="category" <?php if($banner->link_type == 'category'){echo 'selected';}?>>Category</option>
                        <option value="subcategory" <?php if($banner->link_type == 'subcategory'){echo 'selected';}?>>Sub category</option>
                        <option value="subsubcategory" <?php if($banner->link_type == 'subsubcategory'){echo 'selected';}?>>Sub sub category</option>
                        <option value="flashdeal" <?php if($banner->link_type == 'flashdeal'){echo 'selected';}?>>Flash Deal</option>
                        <!-- <option value="search" <?php //if($banner->link_type == 'search'){echo 'selected';}?>>Search</option> -->
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
        });

        $("#photo").spartanMultiImagePicker({
            fieldName:        'photo',
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
