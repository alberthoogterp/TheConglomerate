@foreach ($jsondata as $key => $value)
    @if (!is_scalar($value))
    <div class="jsoninputsection" id="jsoninputsection_{{$key}}" data-type="{{$value instanceof \stdClass? 'object' : 'array'}}">
        <div class="inputsection" id="inputsection_{{$key}}">
            <button class='dropdownbutton' id='button_{{$key}}' onclick="changeDropdownbuttonState('{{$key}}')"></button>
            <input class="jsoninput" type='text' value='{{$key}}' size='{{strlen($key)}}'>
            <button class="addbutton" data-key="{{$key}}"></button><br>
        </div>
        <div class="section" id="section_{{ $key }}">
            @include('partials.jsonsubobject',['jsondata'=>$value])
        </div>
    </div>
    @else
        <div class="jsoninputsection" id="jsoninputsection_{{$key}}" data-type="value">
            <div class="inputsection" id="inputsection_{{$key}}">
                <input class="jsoninput" type='text' style="margin-left:2em"  value='{{$key}}' size='{{strlen($key)}}'>:
                <input class="jsoninput" type='text' value='{{$value}}' size='{{strlen($value)}}'>
                <button class="addbutton" data-key="{{$key}}"></button><br>
            </div>
        </div>
    @endif
@endforeach