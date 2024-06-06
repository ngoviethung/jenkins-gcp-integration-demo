@php
    use App\Models\Type;
    $type = data_get($entry, 'type');
    $type_code = $type->code;
    $app_id = env('APP_ID');
    $topics = data_get($entry, 'topics');
    $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['name'])->pluck('name')->toArray();

    if(in_array($type, $type_scale_1_4)) {
        $scale_item = 1/20;
    }else{
        $scale_item = 1/5;
    }
    //model
    $model = DB::connection('mysql2')->table('models')->where('app_id', $app_id)->first();
    $url_media = env('URL_MEDIA');

    $scale_model = $model->scale;
    $body_image = $model->body_image;
    $left_hand_image = $model->left_hand_image;
    $right_hand_image = $model->right_hand_image;
    $body_image_pos_x = $model->body_image_pos_x;
    $body_image_pos_y = $model->body_image_pos_y;
    $left_hand_image_pos_x = $model->left_hand_image_pos_x;
    $left_hand_image_pos_y = $model->left_hand_image_pos_y;
    $right_hand_image_pos_x = $model->right_hand_image_pos_x;
    $right_hand_image_pos_y = $model->right_hand_image_pos_y;

@endphp

@push('crud_columns_scripts')
    /* @todo Tim xem push js vao columns ntn ? */
    {{--    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">--}}
    <link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">

@endpush
<style>
    .preview-container {
        margin: auto;
        width: 150px;
        height: 266.8px;
        /*border: thin black solid;*/
        background: grey;
        text-align: center;
        position: relative;
    }
    .full-model {
        transform-origin: left top;
        position: absolute;
    }
</style>
<div>
    <div class="preview-container">
        <div class="parent-front-layer"></div>
        <div class="parent-left-layer"></div>
        <div class="parent-mid-layer"></div>
        <div class="parent-right-layer"></div>
        <div class="parent-back-layer"></div>

        <img class="full-model mid-layer" src="{{ $url_media.'/'.$body_image }}"
             style="transform: scale({{$scale_model / 5}}) translate(-50%, -50%); top: {{$body_image_pos_y / 5}}px; left: {{$body_image_pos_x / 5}}px;" />
        <img class="full-model left-layer" src="{{ $url_media.'/'.$left_hand_image }}"
             style="transform: scale({{$scale_model / 5}}) translate(-50%, -50%); top: {{$left_hand_image_pos_y / 5}}px; left: {{$left_hand_image_pos_x / 5}}px;" />
        <img class="full-model right-layer" src="{{ $url_media.'/'.$right_hand_image }}"
             style="transform: scale({{$scale_model / 5}}) translate(-50%, -50%); top: {{$right_hand_image_pos_y / 5}}px; left: {{$right_hand_image_pos_x / 5}}px;" />

        @php

            @endphp
        @switch($type_code)
            @case('hair')
                @php

                    $hair_items = data_get($entry, 'hair_items');
                    if($hair_items){
                        $hair_items = json_decode($hair_items);
                    }

                @endphp
                @foreach($hair_items as $k => $item)
                    @if($item && $k == 0)
                        @php

                            $image = $item->image;
                            $back_image = $item->back_image;
                            $mid_image = $item->mid_image;
                            $thumbnail = $item->thumbnail;

                            $image_pos_x = $item->image_pos_x  * $scale_item;
                            $image_pos_y = $item->image_pos_y  * $scale_item;
                            $back_image_pos_x = $item->back_image_pos_x  * $scale_item;
                            $back_image_pos_y = $item->back_image_pos_y  * $scale_item;
                            $mid_image_pos_x = $item->mid_image_pos_x  * $scale_item;
                            $mid_image_pos_y = $item->mid_image_pos_y  * $scale_item;

                        @endphp
                        @if($image)
                            <img  class="item-model front-layer" src="{{ url($image) }}"
                                  style="
                                transform-origin: left top;
                                left: {{ $image_pos_x }}px;
                                top: {{ $image_pos_y}}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                             "
                            />
                        @endif
                        @if($back_image)
                            <img  class="item-model back-layer" src="{{ url($back_image) }}"
                                  style="
                                transform-origin: left top;
                                left: {{ $back_image_pos_x }}px;
                                top: {{ $back_image_pos_y}}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                             "
                            />
                        @endif
                        @if($mid_image)
                            <img  class="item-model mid-layer" src="{{ url($mid_image) }}"
                                  style="
                                transform-origin: left top;
                                left: {{ $mid_image_pos_x }}px;
                                top: {{ $mid_image_pos_y}}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                             "
                            />
                        @endif
                    @endif

                @endforeach
                @break
            @case('makeup')
                @php

                    $image_pos_x = data_get($entry, 'image_pos_x') * $scale_item;
                    $image_pos_y = data_get($entry, 'image_pos_y')  * $scale_item;
                    $makeup_items = data_get($entry, 'makeup_items');

                    if($makeup_items){
                        $makeup_items = json_decode($makeup_items);
                    }
                    $k = 0;
                @endphp
                @if(isset($makeup_items[0]))
                    @foreach($makeup_items[0] as $image)
                        @if($image && $k == 0)
                            @php
                                $k++;
                            @endphp
                            <img  class="item-model mid-layer" src="{{ url($image) }}"
                                  style="
                                        transform-origin: left top;
                                        left: {{ $image_pos_x }}px;
                                        top: {{ $image_pos_y}}px;
                                         position: absolute;
                                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                                 "
                            />
                        @endif
                    @endforeach
                @endif
                @break
            @default

                @php

                    $image = data_get($entry, 'image');
                    $image_pos_x = data_get($entry, 'image_pos_x') * $scale_item;
                    $image_pos_y = data_get($entry, 'image_pos_y') * $scale_item;

                    $left_image = data_get($entry, 'left_image');
                    $left_image_pos_x = data_get($entry, 'left_image_pos_x') * $scale_item;
                    $left_image_pos_y = data_get($entry, 'left_image_pos_y') * $scale_item;

                    $right_image = data_get($entry, 'right_image');
                    $right_image_pos_x = data_get($entry, 'right_image_pos_x') * $scale_item;
                    $right_image_pos_y = data_get($entry, 'right_image_pos_y') * $scale_item;

                    $back_image = data_get($entry, 'back_image');
                    $back_image_pos_x = data_get($entry, 'back_image_pos_x') * $scale_item;
                    $back_image_pos_y = data_get($entry, 'back_image_pos_y') * $scale_item;

                    $mid_image = data_get($entry, 'mid_image');
                    $mid_image_pos_x = data_get($entry, 'mid_image_pos_x') * $scale_item;
                    $mid_image_pos_y = data_get($entry, 'mid_image_pos_y') * $scale_item;


                @endphp
                @if($image)
                    <img  class="item-model front-layer" src="{{ url($image) }}"
                          style="
                            transform-origin: left top;
                             left: {{ $image_pos_x }}px;
                             top: {{ $image_pos_y }}px;
                             position: absolute;
                             transform: scale({{$scale_item}}) translate(-50%, -50%);"
                    />
                @endif

                @if($back_image)
                    <img  class="item-model back-layer" src="{{ url($back_image) }}"
                          style="
                        transform-origin: left top;
                        left: {{ $back_image_pos_x }}px;
                        top: {{ $back_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                    />
                @endif

                @if($left_image)
                    <img  class="item-model left-layer" src="{{ url($left_image) }}"
                          style="
                        transform-origin: left top;
                        left: {{ $left_image_pos_x }}px;
                         top: {{ $left_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                    />
                @endif

                @if($right_image)
                    <img  class="item-model right-layer" src="{{ url($right_image) }}"
                          style="
                        transform-origin: left top;
                        left: {{ $right_image_pos_x }}px;
                         top: {{ $right_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                    />
                @endif
                @if($mid_image)
                    <img  class="item-model mid-layer" src="{{ url($mid_image) }}"
                          style="
                        transform-origin: left top;
                        left: {{ $mid_image_pos_x }}px;
                         top: {{ $mid_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                    />
                @endif

        @endswitch

    </div>
</div>
