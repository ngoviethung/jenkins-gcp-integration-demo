

@include('crud::fields.inc.wrapper_start')

    <label>{!! $field['label'] !!}</label>

    <div id="list-background">
        @php
            $dirname = "uploads/background/";
            $images = glob($dirname."*");

        @endphp
        @foreach($images as $image)
            <a class="background" href="javascript:void(0)" style="margin-right: 10px; margin-top: 10px; display: inline-block">
                <img src="{!! asset($image) !!}" width="130px" />
            </a>

        @endforeach
    </div>
    <input type="hidden" name="background" value="uploads/background/party_8-1-min.jpg">

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
            #list-background {
                overflow-y: hidden;
                display: flex;
                position: relative;
                overflow-x: auto;
                height: 200px;
                max-width: 870px;
                width: 100%;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
       <script>
           $('.background').on('click', function(){
               var image = $(this).find('img').attr('src');
               $('#preview-container').css("background-image", 'url('+ image +  ')');
               $('input[name=background]').val(image);

           })
       </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
