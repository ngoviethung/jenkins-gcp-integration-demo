
@php
    $models = DB::table('models')->orderBy('id', 'ASC')->get();
    $model_default = $models->first();
@endphp
@include('crud::fields.inc.wrapper_start')

    <label>{!! $field['label'] !!}</label>

    <div id="list-model">

        @foreach($models as $model)
            <a image="{!! $model->image !!}" class="model" href="javascript:void(0)" style="margin-right: 50px; margin-top: 10px; display: inline-block">
                <img src="{!! asset($model->image) !!}" width="60px" />
            </a>

        @endforeach
    </div>
    <input type="hidden" name="model" value="{!! $model_default->image !!} ">
@include('crud::fields.inc.wrapper_end')




{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}


@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include select2 css-->
        <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

        <style>
            #list-model {
                overflow-y: hidden;
                display: flex;
                position: relative;
                overflow-x: auto;
                height: 220px;
                max-width: 870px;
                width: 100%;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>
            $('.model').on('click', function(){
                var image = $(this).attr('image');
                $('#full-model').attr("src", '/' + image);
                $('input[name=model]').val(image);

            })
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
