@extends(backpack_view('blank'))

@php
    use App\Models\Outfit;
        use App\Models\Item;
        use App\Models\Type;

        $defaultBreadcrumbs = [
          trans('backpack::crud.admin') => backpack_url('dashboard'),
          $crud->entity_name_plural => url($crud->route),
          trans('backpack::crud.edit') => false,
        ];

        // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
        $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

    $type_scale_1_4 = Type::where('scale', 0.25)->get(['id'])->pluck('id')->toArray();


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
            @php

                /*
                use App\Models\Item;

                    $lens_default = Item::where('type_id', 20)->first();
                    $eyebrows_default = Item::where('type_id', 17)->first();
                    $lips_default = Item::where('type_id', 21)->first();
                    $dress_set_default = Item::where('type_id', 1)->first();
                    $hair_default = Item::where('type_id', 19)->first();
     */

            @endphp

            @php
                /*
                    $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();
                    $url_media = env('URL_MEDIA');
                    $scale = 3/4;

                    $default_items = json_decode($model->default_items);
                    $items = [];
                    foreach ($default_items as $item){
                        $items[$item->type] = $item;
                    }
    */

            @endphp

            @php
                //items added



                $outfit_id = request()->id;
                $background = '/uploads/background/bg_wedding_1.jpg';

                $outfit = Outfit::where('id', $outfit_id)->first();
                if($outfit){
                    $arr_item_id = $outfit->item_id;
                    $arr_item_id = explode(',', $arr_item_id);
                    $items = Item::with('type:id,order')->whereIn('id', $arr_item_id)->get();

                    //item checking added
                    $arr_item_checking_id = $outfit->item_checking_id;
                    if(!empty($arr_item_checking_id)){
                        $arr_item_checking_id = explode(',', $arr_item_checking_id);
                    }else{
                        $arr_item_checking_id = [];
                    }

                    $background = $outfit->background;

                }


            @endphp

            @if($outfit_id)

                @foreach($items as $item)
                    @php

                        $pos_x = $item->pos_x;
                        $pos_y = $item->pos_y;
                        $z_index = $item->type->order;
                        if(in_array($item->type_id, $type_scale_1_4)) {
                           $pos_x = $pos_x / 4;
                           $pos_y = $pos_y / 4;
                       }
                    @endphp
                    <img type_id="{!! $item->type_id !!}" item_id="{!! $item->id !!}" class="item-model {!! $item->id !!}" src="{{ asset('/') }}{!! $item->image !!}"
                         style="
                         @if(in_array($item->type_id, $type_scale_1_4))
                             transform: scale(0.1875) translate(-50%, -50%);
                             left: {{ $pos_x / (4/3) }}px;
                             top: {{ ($pos_y  / (4/3) )}}px;
                             z-index: {{$z_index}};

                         @else
                             transform: scale(0.75) translate(-50%, -50%);
                             left: {{ $pos_x / (4/3) }}px;
                             top: {{ ($pos_y  / (4/3) )}}px;
                             z-index: {{$z_index}};

                         @endif
                             "
                    >
                    @if($item->thumb_top != '')
                        <img class="item-model {!! $item->id !!}" src="{{ asset('/') }}{!! $item->thumb_top !!}"
                             style="
                             @if(in_array($item->type_id, $type_scale_1_4))
                                 transform: scale(0.1875) translate(-50%, -50%);
                                 left: {{ $pos_x / (4/3) }}px;
                                 top: {{ ($pos_y  / (4/3) )}}px;
                                 z-index: -{{$z_index}};

                             @else
                                 transform: scale(0.75) translate(-50%, -50%);
                                 left: {{ $pos_x / (4/3) }}px;
                                 top: {{ ($pos_y  / (4/3) )}}px;
                                 z-index: -{{$z_index}};

                             @endif
                                 "
                        >
                    @endif
                @endforeach

                @foreach($arr_item_checking_id as $item_checking_id)
                    @php

                        $arr_value = explode('_SEPERATE_', $item_checking_id);

                        $type = $arr_value[0];
                        $pos_x = $arr_value[1];
                        $pos_y = $arr_value[2];
                        $pos_x_origin = $arr_value[1];
                        $pos_y_origin = $arr_value[2];
                        $z_index = $arr_value[3];
                        $image = $arr_value[4];

                        $item_id = NULL;
                        if(isset($arr_value[6])){
                            $item_id = $arr_value[6];
                        }

                        $thumb_top = '';
                        if(isset($arr_value[5])){
                            $thumb_top = $arr_value[5];
                        }

                        if(in_array($type, $type_scale_1_4)) {
                           $pos_x = $pos_x / 4;
                           $pos_y = $pos_y / 4;
                       }

                    @endphp

                    @if($thumb_top != 'null' && $thumb_top != '')
                        <img checking="1" type_id="{!! $type !!}"  item_id="{!! $item_id !!}" class="item-model" src="{{ asset('/') }}{!! $thumb_top !!}"
                             style="
                                 @if(in_array($type, $type_scale_1_4))
                                     transform: scale(0.1875) translate(-50%, -50%);
                                     left: {{ $pos_x / (4/3) }}px;
                                     top: {{ ($pos_y  / (4/3) )}}px;
                                     z-index: -{{$z_index}};

                                 @else
                                     transform: scale(0.75) translate(-50%, -50%);
                                     left: {{ $pos_x / (4/3) }}px;
                                     top: {{ ($pos_y  / (4/3) )}}px;
                                     z-index: -{{$z_index}};

                                 @endif
                                     "
                        >
                    @endif

                    <img checking="1" type_id="{!! $type !!}"  item_id="{!! $item_id !!}"
                         item_value="{!! $type !!}_SEPERATE_{!! $pos_x_origin !!}_SEPERATE_{!! $pos_y_origin !!}_SEPERATE_{!! $z_index !!}_SEPERATE_{!! $image !!}_SEPERATE_{!! $thumb_top !!}_SEPERATE_{!! $item_id !!}"

                         class="item-model {!! $item_id !!}" src="{{ asset('/') }}{!! $image !!}"
                         style="
                         @if(in_array($type, $type_scale_1_4))
                             transform: scale(0.1875) translate(-50%, -50%);
                             left: {{ $pos_x / (4/3) }}px;
                             top: {{ ($pos_y  / (4/3) )}}px;
                             z-index: {{$z_index}};

                         @else
                             transform: scale(0.75) translate(-50%, -50%);
                             left: {{ $pos_x / (4/3) }}px;
                             top: {{ ($pos_y  / (4/3) )}}px;
                             z-index: {{$z_index}};

                         @endif
                             "
                    >

                @endforeach


            @endif



            {{--<img id="full-model" src="{{ $url_media.$model->image }}" style=" transform: scale({{$model->scale * $scale}}) translate(-50%, -50%); top: {{$model->pos_y * $scale}}px; left: {{$model->pos_x * $scale}}px;" />

            --}}


        </div>

    </div>
@endsection

<style>

    .col-md-8 {
        flex: 0 0 58.666667% !important;
    }
    .bold-labels{
        max-width: 100% !important;
    }

    #preview-container {
        z-index: -999;
        width: 532.5px;
        height: 1000.5px;
        background: url({!! url($background) !!});
        text-align: center;
        position: relative;
        background-size: 100% 1000.5px;
        background-repeat: no-repeat;
    }
    #full-model {
        position: absolute;
        transform-origin: left top;
    }

</style>



