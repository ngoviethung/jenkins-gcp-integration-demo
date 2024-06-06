
@extends(backpack_view('blank'))

@php
    use App\Models\Type;

    $router_name = Route::currentRouteName();

    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      $crud->entity_name_plural => url($crud->route),
      trans('backpack::crud.preview') => false,
    ];
    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;



@endphp

@section('header')
    <section class="container-fluid d-print-none">
        <a href="javascript: window.print();" class="btn float-right"><i class="fa fa-print"></i></a>
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>{!! $crud->getSubheading() ?? mb_ucfirst(trans('backpack::crud.preview')).' '.$crud->entity_name !!}.</small>
            @if ($crud->hasAccess('list'))
                <small class=""><a href="{{ url($crud->route) }}" class="font-sm"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
            @endif
        </h2>
    </section>
@endsection
@section('content')

    <div class="row">
        <div class="{{ $crud->getShowContentClass() }}">
            <!-- select -->
            @if(isset($crud->entry))

                @php
                    $app_id = env('APP_ID');
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

                    $order_type = DB::table('types')->get(['code', 'order'])->pluck('order', 'code')->toArray();
                    $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['name'])->pluck('name')->toArray();
                   $type = $crud->entry->type;
                   $type_code = $type->code;
                   $z_index = $order_type[$type_code];

                   if(in_array($type_code, $type_scale_1_4)) {
                        $scale_item = 1/4;
                    }else{
                        $scale_item = 1;
                    }

                @endphp

                <div id="preview-container">
                    <div class="parent-front-layer"></div>
                    <div class="parent-left-layer"></div>
                    <div class="parent-mid-layer"></div>
                    <div class="parent-right-layer"></div>
                    <div class="parent-back-layer"></div>
                        @php
                            /*
                            $default_items = json_decode($model->default_items);
                            $items = [];
                            foreach ($default_items as $item){
                                $items[$item->type] = $item;
                            }
                            */
                        @endphp

                    {{-- Show default items --}}
                        <img class="full-model mid-layer" src="{{ $url_media.'/'.$body_image }}"
                             style=" position: absolute;
                            transform-origin: left top;
                             transform: scale({{$scale_model}}) translate(-50%, -50%);
                              left: {{$body_image_pos_x}}px; top: {{$body_image_pos_y}}px; " />

                        <img class="full-model left-layer" src="{{ $url_media.'/'.$left_hand_image }}"
                             style=" position: absolute;
                            transform-origin: left top;
                            transform: scale({{$scale_model}}) translate(-50%, -50%);
                            left: {{$left_hand_image_pos_x}}px; top: {{$left_hand_image_pos_y}}px; " />

                        <img class="full-model right-layer" src="{{ $url_media.'/'.$right_hand_image  }}"
                         style=" position: absolute;
                            transform-origin: left top;
                            transform: scale({{$scale_model}}) translate(-50%, -50%);
                         left: {{$right_hand_image_pos_x}}px; top: {{$right_hand_image_pos_y}}px; " />

                        @if($type_code == 'makeup')
                            @php
                                $skins = DB::table('skins')->get();

                            @endphp
                            @foreach($skins as $skin)
                                @php
                                    $body_image = $skin->body_image;
                                    $left_hand_image = $skin->left_hand_image;
                                    $right_hand_image = $skin->right_hand_image;
                                    $class = 'skin-'.$skin->code;
                                @endphp
                                @if($body_image)
                                    <img class="full-model {{$class}} mid-layer" src="{{ url($body_image) }}"
                                         style=" position: absolute;
                                    transform-origin: left top;
                                     transform: scale({{$scale_model}}) translate(-50%, -50%);
                                      left: {{$body_image_pos_x}}px; top: {{$body_image_pos_y}}px;
                                      display: none;
                                      " />
                                @endif

                                @if($left_hand_image)
                                    <img class="full-model {{$class}} left-layer" src="{{ url($left_hand_image) }}"
                                         style=" position: absolute;
                                    transform-origin: left top;
                                    transform: scale({{$scale_model}}) translate(-50%, -50%);
                                    left: {{$left_hand_image_pos_x}}px; top: {{$left_hand_image_pos_y}}px;
                                    display: none;
                                     " />
                                @endif
                                @if($right_hand_image)
                                    <img class="full-model {{$class}} right-layer" src="{{ url($right_hand_image) }}"
                                         style=" position: absolute;
                                    transform-origin: left top;
                                    transform: scale({{$scale_model}}) translate(-50%, -50%);
                                 left: {{$right_hand_image_pos_x}}px; top: {{$right_hand_image_pos_y}}px;
                                 display: none;

                                 " />
                                @endif
                            @endforeach
                        @endif


                    {{-- Show main items --}}
                    @switch($type_code)
                        @case('hair')
                            @php
                                    $hair_items = $crud->entry->hair_items;
                                   if($hair_items){
                                       $hair_items = json_decode($hair_items);
                                   }
                            @endphp
                            @foreach($hair_items as $k => $item)
                                @if($item)

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

                                        $class = "id-$k item";
                                        if($k == 0){
                                            $display = 'block';
                                        }else{
                                            $display = 'none';
                                        }

                                    @endphp
                                    @if($image)
                                    <img class="{{$class}} item-model front-layer"  src="{{ url($image) }}" style="z-index: {{ $z_index }};
                                         transform-origin: left top;
                                         left: {{ $image_pos_x }}px;
                                         top: {{ $image_pos_y }}px;
                                         position: absolute;
                                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                                         display: {{$display}};

                                    "/>
                                    @endif
                                    @if($back_image)
                                        <img class="{{$class}} item-model back-layer"  src="{{ url($back_image) }}" style="z-index: {{ $z_index }};
                                            transform-origin: left top;
                                            left: {{ $back_image_pos_x }}px;
                                            top: {{ $back_image_pos_y}}px;
                                             position: absolute;
                                             transform: scale({{$scale_item}}) translate(-50%, -50%);
                                            display: {{$display}};

                                        " />
                                    @endif
                                    @if($mid_image)
                                        <img class="{{$class}} item-model mid-layer"  src="{{ url($mid_image) }}" style="z-index: {{ $z_index }};
                                            transform-origin: left top;
                                            left: {{ $mid_image_pos_x }}px;
                                             top: {{ $mid_image_pos_y}}px;
                                             position: absolute;
                                             transform: scale({{$scale_item}}) translate(-50%, -50%);
                                             display: {{$display}};


                                    " />
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
                                @foreach($makeup_items[0] as $key => $image)
                                    @if($image)
                                        @php
                                            $class = "skin-$key item";
                                            if($k == 0){
                                                $display = 'block';
                                            }else{
                                                $display = 'none';
                                            }
                                            $k++;
                                        @endphp
                                        <img  class="{{$class}} item-model mid-layer" src="{{ url($image) }}"
                                              style="
                                                transform-origin: left top;
                                                left: {{ $image_pos_x }}px;
                                                top: {{ $image_pos_y}}px;
                                                 position: absolute;
                                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                                                 display: {{$display}};
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
                                <img class="item-model front-layer" src="{{ url($image) }}" style="z-index: {{ $z_index }};
                                 transform-origin: left top;
                                 left: {{ $image_pos_x }}px;
                                 top: {{ $image_pos_y }}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                            "/>
                            @endif
                            @if($back_image)
                                <img class="item-model back-layer" src="{{ url($back_image) }}" style="z-index: {{ $z_index }};
                                    transform-origin: left top;
                                    left: {{ $back_image_pos_x }}px;
                                    top: {{ $back_image_pos_y}}px;
                                     position: absolute;
                                     transform: scale({{$scale_item}}) translate(-50%, -50%);

                                " />
                            @endif
                            @if($left_image)
                                <img class="item-model left-layer" src="{{ url($left_image) }}" style="z-index: {{ $z_index }};
                                    transform-origin: left top;
                                    left: {{ $left_image_pos_x }}px;
                                     top: {{ $left_image_pos_y}}px;
                                     position: absolute;
                                     transform: scale({{$scale_item}}) translate(-50%, -50%);
                                " />
                            @endif
                            @if($right_image)
                                <img class="item-model right-layer" src="{{ url($right_image) }}" style="z-index: {{ $z_index }};
                                    transform-origin: left top;
                                    left: {{ $right_image_pos_x }}px;
                                     top: {{ $right_image_pos_y}}px;
                                     position: absolute;
                                     transform: scale({{$scale_item}}) translate(-50%, -50%);

                                " />
                            @endif
                            @if($mid_image)
                                <img class="item-model mid-layer" src="{{ url($mid_image) }}" style="z-index: {{ $z_index }};
                                    transform-origin: left top;
                                    left: {{ $mid_image_pos_x }}px;
                                     top: {{ $mid_image_pos_y}}px;
                                     position: absolute;
                                     transform: scale({{$scale_item}}) translate(-50%, -50%);

                                " />
                            @endif

                    @endswitch

                    </div>

            @endif

        </div>
        @if($type_code == 'hair')
            <div class="col-md-1" style="margin-left: -100px; padding: 0;">
            @php
                $hair_items = $crud->entry->hair_items;
               if($hair_items){
                   $hair_items = json_decode($hair_items);
               }
            @endphp
            @foreach($hair_items as $k => $item)
                @if($item)
                    @php
                        $thumbnail = $item->thumbnail;
                    @endphp
                        <div class="row" style="margin: 100px 0">
                            <a data-id="{{$k}}" class="hair-thumbnail" href="javascript:void(0)">
                                <img src="{{url($thumbnail)}}" style="width: 100%; background-color: white;">
                            </a>
                        </div>
                @endif
            @endforeach
            </div>
         @endif
        @if($type_code == 'makeup')
            <div class="col-md-1" style="margin-left: -100px; padding: 0;">
                @php
                    $makeup_items = $crud->entry->makeup_items;
                   if($makeup_items){
                       $makeup_items = json_decode($makeup_items);
                   }
                   $arr_skin = \App\Models\Skin::get()->pluck('code', 'id')->toArray();


                @endphp
                @if(isset($makeup_items[0]))
                    @foreach($makeup_items[0] as $key =>  $image)
                        @if($image)
                            @php
                                $ids = explode('_', $key);
                                $id = $ids[2];
                                if(isset($arr_skin[$id])){
                                    $code = $arr_skin[$id];
                                }else{
                                    $code = '';
                                }

                            @endphp
                            <div class="row" style="margin: 30px 0">
                                <a data-id="{{$key}}" data-code="{{$code}}" class="makeup-thumbnail" href="javascript:void(0)">
                                    <img src="{{url($image)}}" style="width: 100%; background-color: white;">
                                </a>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        @endif

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js" ></script>
<script>
$(document).ready(function () {

    $(document).on('click', '.hair-thumbnail', function(){
        $('.hair-thumbnail > img').css('background-color', 'white');
        $(this).find('img').css('background-color', 'wheat');
        var id = $(this).data("id");
        var class_name = 'id-' + id
        $('.item').css('display', 'none');
        $('.' + class_name).css('display', 'block');

    });

    $(document).on('click', '.makeup-thumbnail', function(){
        $('.makeup-thumbnail > img').css('background-color', 'white');
        $(this).find('img').css('background-color', 'wheat');
        var code = $(this).data("code");
        var class_name = 'skin-' + code
        $('.full-model').css('display', 'none');
        $('.' + class_name).css('display', 'block');

    });


    $(document).on('click', '#accept', function(){

    if (confirm('Are you accept?')) {
        var id = {{ $crud->entry->id}}

        $.ajax({
            url: '/api/sync',
            type: 'GET',
            data: {
                id: id,
            },
            success: function (response) {
                if (response.code != 200) {
                    alert("Can't to sync data!")
                }else{
                    window.location.replace("/admin/checking");
                }
            },
            error: function (e) {
                alert("Error sync data")
            }
        });
    }
    });


    $(document).on('click', '#repair', function(){

    var note = prompt("Please enter your note:");
    if (note == "") {
        alert("The note field is required.")
    }else if(note == null){
        alert("Cancelled repair.")
    } else {
        var id = {{ $crud->entry->id}}
        $.ajax({
            url: '/api/repair',
            type: 'GET',
            data: {
                id: id,
                note: note
            },
            success: function (response) {
                if (response.code != 200) {
                    alert("Can't to repair checking!")
                }else{
                    window.location.replace("/admin/checking");
                }

            },
            error: function (e) {
                alert("Error repair checking")
            }
        });
    }

    });

});
</script>


@endsection
@section('after_styles')
<link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
<style>

    #preview-container {
        z-index: -9999;
        /*background: white;*/
        margin: auto;
        width: 714.5px;
        height: 1334px;
        /*border: thin black solid;*/
        background: url('<?php echo url("uploads/background/bg_wedding_3.jpg")?>');
        text-align: center;
        position: relative;
    }


</style>


<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css') }}">

@endsection

@section('after_scripts')
<script src="{{ asset('packages/backpack/crud/js/crud.js') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js') }}"></script>
@endsection



