{{-- relationships with pivot table (n-n) --}}
@php
    $results = data_get($entry, $column['name']);
@endphp

<div class="custom-list">
    <?php
        if ($results && $results->count()) {
            $results_array = $results->pluck($column['attribute']);
            ?>

            @foreach($results_array as $index => $name)
                <div class="{{ $index >= 7 ? 'hidden' : '' }}">{{ $name }}</div>
            @endforeach

            @if($results->count() > 7)
                <div class="list-control">
                    <a href="#">See more</a>
                </div>
            @endif
    <?php
        } else {
            echo '<br/>';
        }
    ?>
</div>
<style>
    .custom-list .hidden {
        display: none;
    }
    .custom-list .hidden.open {
        display: block;
    }
</style>
<script type="text/javascript">
    if(window.customList !== true) {
        $(document).on('click','.custom-list .list-control a', function (e) {
            e.preventDefault()
            let text = $(this).text() === 'See more' ? 'Hide' : "See more";
            let link = $(this);
            link.parent().parent().find('.hidden').toggleClass('open')
            link.text(text)
        })
        window.customList = true;
    }
</script>
