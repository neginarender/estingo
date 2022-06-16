<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">{{translate('Banner Information')}}</h3>
    </div>

    <!--Horizontal Form-->
    <!--===================================================-->
    <form class="form-horizontal" action="{{ route('master_banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('URL')}}</label>
                <div class="col-sm-9">
                    <input type="text" placeholder="{{translate('URL')}}" id="url" name="url" class="form-control" required>
                </div>
            </div>
            
            {{-- <div class="form-group">
                <label class="col-sm-3" for="url">{{translate('Banner Position')}}</label>
                <div class="col-sm-9">
                    <select class="form-control demo-select2" name="position" required>
                        <option value="1">{{translate('Banner Position 1')}}</option>
                        <option value="2">{{translate('Banner Position 2')}}</option>
                    </select>
                </div>
            </div> --}}
            <div class="form-group">
                <div class="col-sm-3">
                    <label class="control-label">{{translate('Banner Images')}}</label>
                    <strong>({{ translate('850px*420px') }})</strong>
                </div>
                <div class="col-sm-9">
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

        $('.demo-select2').select2();

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
