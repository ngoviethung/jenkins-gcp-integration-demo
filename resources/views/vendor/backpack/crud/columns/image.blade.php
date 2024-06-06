{{-- image column type --}}
@php
  $value = data_get($entry, $column['name']);
  $prefix = $column['prefix'] ?? '';

  if (is_array($value)) {
    $value = json_encode($value);
  }

  if (preg_match('/^data\:image\//', $value)) { // base64_image
    $href = $src = $value;
  } elseif (isset($column['disk'])) { // image from a different disk (like s3 bucket)
    $href = $src = Storage::disk($column['disk'])->url($prefix.$value);
  } else { // plain-old image, from a local disk
    $href = $src = asset( $prefix . $value);
  }

@endphp

<span>
  @if( empty($value) )
    -
  @else
    <a href="{{ $href }}" target="_blank">
      <img src="{{ $src }}" style="
          height: {{ isset($column['height']) ? $column['height'] : "auto" }};
          max-height: {{ isset($column['max-height']) ? $column['max-height'] : "" }};
          width: {{ isset($column['width']) ? $column['width'] : "auto" }};
          max-width: {{ isset($column['max-width']) ? $column['max-width'] : "" }};
          background-color: {{ isset($column['background-color']) ? $column['background-color'] : "" }};"
      />
    </a>
  @endif
</span>
