<div class="panel">
    <div class="panel-body">
        <div class="">
            <!-- Simple profile -->
            <div class="text-center">
                <div class="pad-ver">
                    <img src="{{Storage::disk('s3')->url($map_products->thumbnail_img)}}" class="img-lg img-circle" alt="Profile Picture">
                </div>
                <h4 class="text-lg text-overflow mar-no">{{$map_products->name}}</h4>
                <p class="text-sm text-muted">{{ $map_products->category->name }}</p>
            </div>
            <hr>

            <?php
            $ids = json_decode($map_products->distributor_id);
            $distributors = App\Distributor::whereIn('id', $ids)->get(); ?>

            <p class="pad-ver text-main text-sm text-uppercase text-bold">{{translate('Distributors')}}</p>

            @if(!empty($distributors))
            <div class="table-responsive">
                <table class="table table-striped mar-no">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($distributors as $key => $distributor)
                    <tr>
                        <td>{{ $distributor->name }}</td>
                        <td>{{ $distributor->phone }}</td>
                        <td>{{ $distributor->address }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>
</div>
