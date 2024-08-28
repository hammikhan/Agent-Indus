
@if (@$passenger)
<option selected>Select {{ $type }}</option>
    @foreach ($passenger as $item)
        <option value="{{ $item['id'] }}">{{ $item['firstName'] }} {{ $item['lastName'] }}</option>
    @endforeach
@endif