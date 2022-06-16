@extends('layouts.app')

@section('content')
<style>
    .newmedia {
       word-wrap:normal;
       width: 150px; 

    }
 </style>   
<!-- dd($products); -->
<div class="row">
        <div class="col-lg-12 pull-right">
            <a href="{{ route('products.createmedia')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New image')}}</a>
        </div>
    </div><br>
<div class="panel">
    <!--Panel heading-->
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{ translate('Manage Media') }}</h3>
    </div>


    <div class="panel-body">
       <!--  <table class="table table-striped res-table mar-no" cellspacing="0" width="100%" id="example"> -->
        <table id="example" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th width="10%">#</th>
                                    
                    <th width="50%">{{translate('Image')}}</th> 
                    <th width="20%">{{translate('URL')}}</th>   
                    <th width="20%">{{translate('Thumbnail')}}</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($gallery as $key => $product)
                    <tr>
                        <td width="10%">{{ ($key+1)}}</td>
                         <td width="20%">
                            
                                <div class="media-left">
                                    <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image">
                                </div>
                               
                           
                        </td>
                        <td width="50%" class="newmedia"> <div class="media-body newmedia">{{ __($product->photos) }}</div></td>
                        <td width="20%"> <div class="media-body" >{{ __($product->thumbnail_img) }}</div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $gallery->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection


@section('script')
    <script type="text/javascript">

        $(document).ready(function() {
            $('#example').DataTable( {
                "scrollX": true
            } );
        } );

        

    </script>
@endsection
