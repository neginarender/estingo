@foreach($mapped_cities as $key => $city)
    <option></option>
    <option value="{{ $city->id }}">{{ $city->name }}</option>
@endforeach