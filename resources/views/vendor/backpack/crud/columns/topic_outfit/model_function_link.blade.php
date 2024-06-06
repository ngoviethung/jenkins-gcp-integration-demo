{{-- custom return value --}}
@php
	$value = $entry->{$column['function_name']}(...($column['function_parameters'] ?? []));
    $topic_id = data_get($entry, 'id');
    $category = $column['function_parameters'][0];


@endphp

<a href="{{ url("admin/outfit?topic=$topic_id&category=$category") }}">
	{!! (array_key_exists('prefix', $column) ? $column['prefix'] : '').str_limit($value, array_key_exists('limit', $column) ? $column['limit'] : 40, "[...]").(array_key_exists('suffix', $column) ? $column['suffix'] : '') !!}
</a>
