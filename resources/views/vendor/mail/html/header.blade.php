<tr>
<td class="header">
<a href="{{ $url }}">
    @php
        $generalsetting = \App\GeneralSetting::first();
    @endphp
    @if($generalsetting->logo != null)
        <img src="{{ Storage::disk('s3')->url($generalsetting->logo) }}" alt="{{ env('APP_NAME') }}">
    @else
        <img src="{{ static_asset('frontend/images/logo/logo.png') }}" alt="{{ env('APP_NAME') }}">
    @endif
</a>
</td>
</tr>
