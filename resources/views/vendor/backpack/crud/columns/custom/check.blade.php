{{-- checkbox with loose false/null/0 checking --}}
@php
$checkValue = data_get($entry, $column['name']);

$checkedIcon = data_get($column, 'icons.checked', 'fa-check-square');
$uncheckedIcon = data_get($column, 'icons.unchecked', 'fa-square-o');

$exportCheckedText = data_get($column, 'labels.checked', trans('backpack::crud.yes'));
$exportUncheckedText = data_get($column, 'labels.unchecked', trans('backpack::crud.no'));

$icon = $checkValue == false ? $uncheckedIcon : $checkedIcon;
$text = $checkValue == false ? $exportUncheckedText : $exportCheckedText;
@endphp

@if($checkValue == false)
   <img src="https://static.thenounproject.com/png/60253-200.png" width="26px">
@else
    <span>
        <i class="fa {{ $checkedIcon }}" style="color:blue; font-size: 24px" ></i>
    </span>
@endif


<span class="sr-only">{{ $text }}</span>
