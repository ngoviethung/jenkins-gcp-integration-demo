<!-- select -->
@php
    use App\Models\Type;
@endphp
@if(isset($crud->entry))
    @php

        $type_scale_1_4 = Type::where('scale', 0.25)->get(['id'])->pluck('id')->toArray();

        $pos_x = $crud->entry->pos_x;
        $pos_y = $crud->entry->pos_y;

        if(in_array($crud->entry->type_id, $type_scale_1_4)) {
            $pos_x = $pos_x / 4;
            $pos_y = $pos_y / 4;
        }

    @endphp
    @push('crud_fields_scripts')
        {{--    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">--}}
        <link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/blitzer/jquery-ui.min.css">
        <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="{{ url('js/jqueryui.dialog.fullmode.js') }}" ></script>
        <style>
            .dialog-full-mode {
                z-index: 9999;
                height: 600px!important;
                overflow: auto;
            }

            #preview-container {
                z-index: 0;
                /*background: white;*/
                margin: auto;
                width: 750px;
                height: 1334px;
                /*border: thin black solid;*/
                background: url('<?php echo url("uploads/background/party_8-1-min.jpg")?>');
                text-align: center;
                position: relative;
            }
            #full-model {
                position: absolute;
                transform-origin: left top;
            }

            .item-model {
                position: absolute;
                transform-origin: left top;
            }
            #item-model {
                position: absolute;
                top: {{ $pos_y }}px;
                left: {{ $pos_x }}px;

                @if(in_array($crud->entry->type_id, $type_scale_1_4))
transform: scale(0.25) translate(-50%, -50%);
                transform-origin: left top;
                /* IE6–IE9 */
                @else
transform: translate(-50%, -50%);
            @endif
}

            .model-control-item {
                float: left;
                margin-left: 20px;
                border:thin #ddd solid;
                border-radius: 5px;
                background: #F0F0F0;
                padding:10px;
                cursor: pointer;
            }

            .model-control-item:hover {
                background: grey;
            }

            .model-control-item img {
                width: 30px;
            }

            #model-control {
                text-align: center;
                margin-top: 20px;
                margin-bottom: 20px;
            }

        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('.model-control-item > img').on('click', function (e) {
                    let src = $(this).attr('src');
                    $('#full-model').attr('src', src)
                });
            })
        </script>
    @endpush
    @include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    <div id="model-control">
        @foreach(\App\Models\CharacterModel::all() as $characterModel)
            <div class="model-control-item" >
                <img src="{{ url($characterModel->image) }}" />
            </div>
        @endforeach
        <div class="clearfix"></div>
    </div>
    <div id="preview-container">

        @php

            $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();

            $default_items = json_decode($model->default_items);
            $items = [];
            foreach ($default_items as $item){
                $items[$item->type] = $item;
            }

            $url_media = env('URL_MEDIA');


            $order_type = DB::table('types')->get(['id', 'order'])->pluck('order', 'id')->toArray();



        @endphp

        <img id="full-model" src="{{ $url_media.$model->image }}" style=" transform: scale({{$model->scale}}) translate(-50%, -50%); top: {{$model->pos_y}}px; left: {{$model->pos_x}}px;" />

        @if($crud->entry->type_id != 20)
            <img class="item-model" src="{{ $url_media.$items['Lens']->image }}"
                 style=" transform: scale(0.25) translate(-50%, -50%);
                     top: {{ $items['Lens']->pos_y / 4}}px;
                     left: {{ $items['Lens']->pos_x / 4}}px;
                     z-index: {{ $order_type[20] }};">
        @endif

        @if($crud->entry->type_id != 17)

            <img class="item-model" src="{{ $url_media.$items['Eyebrows']->image }}"
                 style=" transform: scale(0.25) translate(-50%, -50%);
                     top: {{ $items['Eyebrows']->pos_y / 4}}px;
                     left: {{ $items['Eyebrows']->pos_x / 4}}px;
                     z-index: {{ $order_type[17] }};">
        @endif
        @if($crud->entry->type_id != 21)

            <img class="item-model" src="{{ $url_media.$items['Lips']->image }}"
                 style=" transform: scale(0.25) translate(-50%, -50%);
                     top: {{ $items['Lips']->pos_y / 4}}px;
                     left: {{ $items['Lips']->pos_x / 4}}px;
                     z-index: {{ $order_type[21] }};">
        @endif

        @if($crud->entry->type_id != 1 && $crud->entry->type_id != 2 && $crud->entry->type_id != 3)
            <img class="item-model" src="{{ $url_media.$items['DressSet']->image }}"
                 style=" transform: scale(1) translate(-50%, -50%);
                     top: {{ $items['DressSet']->pos_y }}px;
                     left: {{ $items['DressSet']->pos_x }}px;
                     z-index: {{ $order_type[1] }};">

        @endif

        @if($crud->entry->type_id != 19)

            <img class="item-model" src="{{ $url_media.$items['Hair']->image }}"
                 style=" transform: scale(0.25) translate(-50%, -50%);
                     top: {{ $items['Hair']->pos_y / 4 }}px;
                     left: {{ $items['Hair']->pos_x /4 }}px;
                     z-index: {{ $order_type[19] }};">
        @endif

        <img id="item-model" src="{{ url($crud->entry->image) }}" style=" z-index: {{ $order_type[$crud->entry->type_id] }};" />

        @if($crud->entry->thumb_top != '' && in_array($crud->entry->type_id, [1, 2, 3, 19]))
            <img id="item-model" src="{{ url($crud->entry->thumb_top) }}" style=" z-index: -{{ $order_type[$crud->entry->type_id] }};"/>
        @endif


    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
    @include('crud::fields.inc.wrapper_end')
@endif
