@extends(backpack_view('blank'))

@php
    use App\Models\Template;
        use App\Models\Item;
        use App\Models\Type;
        use App\Models\Position;

        $defaultBreadcrumbs = [
          trans('backpack::crud.admin') => backpack_url('dashboard'),
          $crud->entity_name_plural => url($crud->route),
          trans('backpack::crud.edit') => false,
        ];

        // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
        $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

@endphp

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>{!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$crud->entity_name !!}.</small>

            @if ($crud->hasAccess('list'))
                <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
            @endif
        </h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="{{ $crud->getEditContentClass() }}">
            <!-- Default box -->

            @include('crud::inc.grouped_errors')

            <form method="post"
                  action="{{ url($crud->route.'/'.$entry->getKey()) }}"
                  @if ($crud->hasUploadFields('update', $entry->getKey()))
                  enctype="multipart/form-data"
                @endif
            >
                {!! csrf_field() !!}
                {!! method_field('PUT') !!}

                @if ($crud->model->translationEnabled())
                    <div class="mb-2 text-right">
                        <!-- Single button -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[$crud->request->input('locale')?$crud->request->input('locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                                    <a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?locale={{ $key }}">{{ $locale }}</a>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            <!-- load the view from the application if it exists, otherwise load the one in the package -->
                @if(view()->exists('vendor.backpack.crud.form_content'))
                    @include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
                @else
                    @include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
                @endif

                @include('crud::inc.form_save_buttons')
            </form>
        </div>

        <div id="preview-container">

            <div class="parent-back-layer"></div>
            <div class="parent-right-layer"></div>
            <div class="parent-mid-layer"></div>
            <div class="parent-left-layer"></div>
            <div class="parent-front-layer"></div>

            @php
                $template_id = request()->id;
                $template = Template::where('id', $template_id)->first();
                $background = $template->background;


                $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['code'])->pluck('code')->toArray();
                $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();
                $url_media = env('URL_MEDIA');

                $scale_model = $model->scale; //scale để khớp với tọa độ khi vẽ.
                $scale_item = 3/4; //scale này để điều chỉnh hiển thị trên web, vì có chỗ show 1/20 , 3/4, 1 vv.vv..
                $scale = $scale_model * $scale_item;

                $body_image = $model->body_image;
                $left_hand_image = $model->left_hand_image;
                $right_hand_image = $model->right_hand_image;

                $body_image_pos_x = $model->body_image_pos_x * $scale_item;
                $body_image_pos_y = $model->body_image_pos_y * $scale_item;
                $left_hand_image_pos_x = $model->left_hand_image_pos_x * $scale_item;
                $left_hand_image_pos_y = $model->left_hand_image_pos_y * $scale_item;
                $right_hand_image_pos_x = $model->right_hand_image_pos_x * $scale_item;
                $right_hand_image_pos_y = $model->right_hand_image_pos_y * $scale_item;


                $image_skin = [];
                $skins = DB::table('skins')->get();
                foreach ($skins as $k => $skin){
                    $image_skin[$skin->id] = [
                        'left_hand_image' => $skin->left_hand_image,
                        'right_hand_image' => $skin->right_hand_image,
                        'body_image' => $skin->body_image,
                    ];
                }
                 $skin_id = $template->model;
                if($skin_id){
                    $body_image = $image_skin[$skin_id]['body_image'];
                    $left_hand_image =  $image_skin[$skin_id]['left_hand_image'];
                    $right_hand_image = $image_skin[$skin_id]['right_hand_image'];

                    $url_media = env('APP_URL').'/';
                    $scale = 0.75;
                }

            @endphp

            <img class="full-model left-layer" src="{{ $url_media.'/'.$left_hand_image }}"
                 style=" position: absolute;
                            transform-origin: left top;
                            transform: scale({{$scale}}) translate(-50%, -50%);
                            left: {{$left_hand_image_pos_x}}px; top: {{$left_hand_image_pos_y}}px; " />

            <img class="full-model mid-layer" src="{{ $url_media.'/'.$body_image }}"
                 style=" position: absolute;
                            transform-origin: left top;
                             transform: scale({{$scale}}) translate(-50%, -50%);
                              left: {{$body_image_pos_x}}px; top: {{$body_image_pos_y}}px; " />

            <img class="full-model right-layer" src="{{ $url_media.'/'.$right_hand_image  }}"
                 style=" position: absolute;
                            transform-origin: left top;
                            transform: scale({{$scale}}) translate(-50%, -50%);
                         left: {{$right_hand_image_pos_x}}px; top: {{$right_hand_image_pos_y}}px; " />


            @php
                //items added

                if($template){
                    $arr_item_id = $template->item_id;
                    $arr_item_id = explode(',', $arr_item_id);
                    $arr_parent_child_id = [];
                    foreach ($arr_item_id as &$item_id){
                        $value = explode('-', $item_id);
                        $item_id = $value[0];
                        if(isset($value[1])){
                            $arr_parent_child_id[$item_id] = $value[1];
                        }
                    }

                    $items = Item::with('type:id,order,code')->whereIn('id', $arr_item_id)->get();
                    foreach ($items as $item){
                        $item->child_id = isset($arr_parent_child_id[$item->id]) ? $arr_parent_child_id[$item->id] : null;
                    }

                    $arr_item_checking_id = [];
                    $item_checking_id = $template->item_checking_id;
                    if($item_checking_id){
                        $arr_item_checking_id = json_decode($item_checking_id);
                    }

                    foreach ($arr_item_checking_id as $item_checking){

                        $product = DB::connection('mysql2')->table('products')->find($item_checking->product_id);

                        $type = new Type();
                        $type->id = 1;
                        $type->order = 1;
                        $type->code = $item_checking->type_code;

                        $new_item = new Item();
                        $new_item->id = $product->id;
                        $new_item->image = $product->image;
                        $new_item->left_image = $product->left_image;
                        $new_item->right_image = $product->right_image;
                        $new_item->back_image = $product->back_image;
                        $new_item->mid_image = $product->mid_image;
                        $new_item->image_pos_x = $product->image_pos_x;
                        $new_item->image_pos_y = $product->image_pos_y;
                        $new_item->left_image_pos_x = $product->left_image_pos_x;
                        $new_item->left_image_pos_y = $product->left_image_pos_y;
                        $new_item->right_image_pos_x = $product->right_image_pos_x;
                        $new_item->right_image_pos_y = $product->right_image_pos_y;
                        $new_item->back_image_pos_x = $product->back_image_pos_x;
                        $new_item->back_image_pos_y = $product->back_image_pos_y;
                        $new_item->mid_image_pos_x = $product->mid_image_pos_x;
                        $new_item->mid_image_pos_y = $product->mid_image_pos_y;
                        $new_item->hair_items = $product->hair_items;
                        $new_item->makeup_items = $product->makeup_items;
                        $new_item->type = $type;
                        $new_item->job_id = $product->job_id;
                        $new_item->child_id = isset($item_checking->child_id) ? $item_checking->child_id : null;

                        $items->push($new_item);
                    }
                }
            @endphp

            @if($template_id)

                @foreach($items as $item)
                    @php
                        $type_code = $item->type->code;

                        $position_id = Type::where('code', $type_code)->first()->position_id;
                        $position = Position::find($position_id);
                        $position_code = 'unknow';
                        if($position){
                            $position_code = $position->code;
                        }
                        $z_index = $item->type->order;
                        if(in_array($type_code, $type_scale_1_4)) {
                            $scale_item *= 1/4;
                        }
                    @endphp

                    @switch($type_code)
                        @case('hair')
                            @php
                                $hair_items = $item->hair_items;
                                if($hair_items){
                                    $hair_items = json_decode($hair_items);
                                }
                            @endphp
                            @foreach($hair_items as $k => $color)
                                @if($color && $k == $item->child_id)
                                    @php

                                        $image = $color->image;
                                        $back_image = $color->back_image;
                                        $mid_image = $color->mid_image;
                                        $thumbnail = $color->thumbnail;

                                        $image_pos_x = $color->image_pos_x  * $scale_item;
                                        $image_pos_y = $color->image_pos_y  * $scale_item;
                                        $back_image_pos_x = $color->back_image_pos_x  * $scale_item;
                                        $back_image_pos_y = $color->back_image_pos_y  * $scale_item;
                                        $mid_image_pos_x = $color->mid_image_pos_x  * $scale_item;
                                        $mid_image_pos_y = $color->mid_image_pos_y  * $scale_item;

                                        $item_checking_id = [
                                            'product_id' => $item->id,
                                            'type_code' => $item->type->code
                                        ];
                                        $item_checking_id = json_encode($item_checking_id);
                                        $item_id = 'checking-'. $item_id;

                                    @endphp

                                    @if($back_image)
                                        <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model back-layer img_back_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" children_type_id="{{ $k }}" item_id="{!! $item->id !!}"  src="{{ $item->job_id ? $url_media.$back_image : url($back_image) }}"
                                              style="
                                transform-origin: left top;
                                left: {{ $back_image_pos_x }}px;
                                top: {{ $back_image_pos_y}}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                                 z-index: {{ $z_index }};
                             "
                                        />
                                    @endif
                                    @if($mid_image)
                                        <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model mid-layer img_mid_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" children_type_id="{{ $k }}" item_id="{!! $item->id !!}"  src="{{ $item->job_id ? $url_media.$mid_image : url($mid_image) }}"
                                              style="
                                transform-origin: left top;
                                left: {{ $mid_image_pos_x }}px;
                                top: {{ $mid_image_pos_y}}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                                 z-index: {{ $z_index }};
                             "
                                        />
                                    @endif
                                    @if($image)
                                        <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model front-layer img_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" children_type_id="{{ $k }}" item_id="{!! $item->id !!}"  src="{{ $item->job_id ? $url_media.$image : url($image) }}"
                                             style="
                                transform-origin: left top;
                                left: {{ $image_pos_x }}px;
                                top: {{ $image_pos_y}}px;
                                 position: absolute;
                                 transform: scale({{$scale_item}}) translate(-50%, -50%);
                                 z-index: {{ $z_index }};
                             "
                                        />
                                    @endif
                                @endif

                            @endforeach
                            @break
                        @case('makeup')
                            @php
                                $image_pos_x = $item->image_pos_x * $scale_item;
                                $image_pos_y = $item->image_pos_y  * $scale_item;
                                $makeup_items = $item->makeup_items;

                                if($makeup_items){
                                    $makeup_items = json_decode($makeup_items);
                                }
                                $item_checking_id = [
                                    'product_id' => $item->id,
                                    'type_code' => $item->type->code
                                ];
                                $item_checking_id = json_encode($item_checking_id);
                                $item_id = 'checking-'. $item_id;

                            @endphp
                            @if(isset($makeup_items[0]))
                                @foreach($makeup_items[0] as $k => $image)
                                    @if($image && $k == $item->child_id)
                                        <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif  class="item-model front-layer img_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" children_type_id="{{ $k }}" item_id="{!! $item->id !!}"  src="{{ $item->job_id ? $url_media.$image : url($image) }}"
                                              style="
                                        transform-origin: left top;
                                        left: {{ $image_pos_x }}px;
                                        top: {{ $image_pos_y}}px;
                                         position: absolute;
                                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                                         z-index: {{ $z_index }};
                                 "
                                        />

                                    @endif
                                @endforeach
                            @endif
                            @break
                        @default

                            @php

                                $image = $item->image;
                                $image_pos_x = $item->image_pos_x * $scale_item;
                                $image_pos_y = $item->image_pos_y * $scale_item;

                                $left_image = $item->left_image;
                                $left_image_pos_x = $item->left_image_pos_x * $scale_item;
                                $left_image_pos_y = $item->left_image_pos_y * $scale_item;

                                $right_image = $item->right_image;
                                $right_image_pos_x = $item->right_image_pos_x * $scale_item;
                                $right_image_pos_y = $item->right_image_pos_y * $scale_item;

                                $back_image = $item->back_image;
                                $back_image_pos_x = $item->back_image_pos_x * $scale_item;
                                $back_image_pos_y = $item->back_image_pos_y * $scale_item;

                                $mid_image = $item->mid_image;
                                $mid_image_pos_x = $item->mid_image_pos_x * $scale_item;
                                $mid_image_pos_y = $item->mid_image_pos_y * $scale_item;

                                $item_checking_id = [
                                    'product_id' => $item->id,
                                    'type_code' => $item->type->code
                                ];
                                $item_checking_id = json_encode($item_checking_id);
                                $item_id = 'checking-'. $item_id;


                            @endphp
                            @if($back_image)
                                <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model back-layer img_back_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" item_id="{!! $item->id !!}" position_code="{!! $position_code !!}"  src="{{ $item->job_id ? $url_media.$back_image : url($back_image) }}"
                                     style="
                        transform-origin: left top;
                        left: {{ $back_image_pos_x }}px;
                        top: {{ $back_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                         z-index: {{ $z_index }};"
                                />
                            @endif
                            @if($left_image)
                                <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model left-layer img_mid_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" item_id="{!! $item->id !!}"  position_code="{!! $position_code !!}" src="{{ $item->job_id ? $url_media.$left_image : url($left_image) }}"
                                     style="
                        transform-origin: left top;
                        left: {{ $left_image_pos_x }}px;
                         top: {{ $left_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                         z-index: {{ $z_index }};"
                                />
                            @endif
                            @if($mid_image)
                                <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model mid-layer img_right_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" item_id="{!! $item->id !!}"  position_code="{!! $position_code !!}" src="{{ $item->job_id ? $url_media.$mid_image : url($mid_image) }}"
                                     style="
                        transform-origin: left top;
                        left: {{ $mid_image_pos_x }}px;
                         top: {{ $mid_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                         z-index: {{ $z_index }};"
                                />
                            @endif
                            @if($right_image)
                                <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model right-layer img_left_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" item_id="{!! $item->id !!}"  position_code="{!! $position_code !!}" src="{{ $item->job_id ? $url_media.$right_image : url($right_image) }}"
                                     style="
                        transform-origin: left top;
                        left: {{ $right_image_pos_x }}px;
                         top: {{ $right_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);
                         z-index: {{ $z_index }};"
                                />
                            @endif
                            @if($image)
                                <img @if($item->job_id) checking="1" item_checking_id="{{$item_checking_id}}"@endif class="item-model front-layer img_image_{!! $item->id !!}" type_code="{!! $item->type->code !!}" item_id="{!! $item->id !!}"  position_code="{!! $position_code !!}" src="{{ $item->job_id ? $url_media.$image : url($image) }}"
                                      style="
                            transform-origin: left top;
                             left: {{ $image_pos_x }}px;
                             top: {{ $image_pos_y }}px;
                             position: absolute;
                             transform: scale({{$scale_item}}) translate(-50%, -50%);
                             z-index: {{ $z_index }};"
                                />
                            @endif

                    @endswitch


                @endforeach

            @endif


        </div>

    </div>
@endsection

<style>

    #preview-container {
        background: url({!! url($background) !!});
        top: 0;
        right: 0;
        z-index: -999;
        width: 535.875px;
        height: 1000.5px;
        text-align: center;
        position: fixed;
        background-size: 100% 1000.5px;
        background-repeat: no-repeat;
    }
    .full-model {
        position: absolute;
        transform-origin: left top;
    }

</style>



