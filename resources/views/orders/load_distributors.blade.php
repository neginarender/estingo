                @forelse($distributors as $key => $distributor)
                <address style="border: 1px solid #d3d3d3; padding: 10px;border-radius: 2px;">
                    <strong class="text-main">{{ $distributor->name }}</strong>
                    <br />
                    {{ $distributor->phone }}<br>
                   {{ $distributor->address }}
                </address>
                @empty
                <address>
                    No Distributors Found for this product
                </address>
                @endforelse