{{-- image column type --}}
@php
  $value = data_get($entry, $column['name']);
  $prefix = $column['prefix'] ?? '';

  $value = json_decode($value);

@endphp

@if(isset($value[0]))
    @foreach($value[0] as $image)
      <span>
          @if( empty($image) )
          -
        @else
          <a href="{{url($image)}}" target="_blank">
              <img src="{{url($image)}}"
                   style="
                  height: {{ isset($column['height']) ? $column['height'] : "auto" }};
                  max-height: {{ isset($column['max-height']) ? $column['max-height'] : "" }};
                  width: {{ isset($column['width']) ? $column['width'] : "auto" }};
                  max-width: {{ isset($column['max-width']) ? $column['max-width'] : "" }};
                  background-color: {{ isset($column['background-color']) ? $column['background-color'] : "" }};"
              />
            </a>
          <br>
          <br>
        @endif
        </span>

    @endforeach
@endif

