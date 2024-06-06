@extends(backpack_view('blank'))

@php
    use App\Models\Template;
    use App\Models\Item;
    use App\Models\Type;

    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      $crud->entity_name_plural => url($crud->route),
      trans('backpack::crud.add') => false,
    ];

    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
    $type_scale_1_4 = Type::where('scale', 0.25)->get(['id'])->pluck('id')->toArray();


@endphp

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>{!! $crud->getSubheading() ?? trans('backpack::crud.add').' '.$crud->entity_name !!}.</small>

            @if ($crud->hasAccess('list'))
                <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
            @endif
        </h2>
    </section>
@endsection

@section('content')

    <div class="row">

        <div class="{{ $crud->getCreateContentClass() }}">
            <!-- Default box -->

            @include('crud::inc.grouped_errors')

            <form method="post"
                  action="{{ url($crud->route) }}"
                  @if ($crud->hasUploadFields('create'))
                      enctype="multipart/form-data"
                @endif

            >
                {!! csrf_field() !!}

                <!-- load the view from the application if it exists, otherwise load the one in the package -->
                @if(view()->exists('vendor.backpack.crud.form_content'))
                    @include('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
                @else
                    @include('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
                @endif

                @include('crud::inc.form_save_buttons_template')

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


            {{--<img id="full-model" src="{{ $url_media.$model->image }}" style=" transform: scale({{$model->scale * $scale}}) translate(-50%, -50%); top: {{$model->pos_y * $scale}}px; left: {{$model->pos_x * $scale}}px;" />

            <img class="item-model" src="{{ $url_media.$items['Lens']->image }}" type_id="20" item_id="lens_default" style="z-index: -1; transform: scale(0.1875) translate(-50%, -50%); top: {{ $items['Lens']->pos_y / 4 * $scale }}px; left: {{ $items['Lens']->pos_x / 4 * $scale }}px;">
            <img class="item-model" src="{{ $url_media.$items['Eyebrows']->image }}" type_id="17" item_id="eyebrows_default" style="z-index: 2; transform: scale(0.1875) translate(-50%, -50%); top: {{ $items['Eyebrows']->pos_y / 4 * $scale }}px; left: {{ $items['Eyebrows']->pos_x / 4 * $scale }}px;">
            <img class="item-model" src="{{ $url_media.$items['Lips']->image }}" type_id="21" item_id="lips_default" style="z-index: 2; transform: scale(0.1875) translate(-50%, -50%); top: {{ $items['Lips']->pos_y / 4 * $scale }}px; left: {{ $items['Lips']->pos_x / 4 * $scale }}px;">
            <img class="item-model" src="{{ $url_media.$items['DressSet']->image }}" type_id="1" item_id="dress_set_default" style="z-index: 100;  transform: scale(0.75) translate(-50%, -50%); top: {{ $items['DressSet']->pos_y * $scale }}px; left: {{ $items['DressSet']->pos_x * $scale }}px;">
            <img class="item-model" src="{{ $url_media.$items['Hair']->image }}"  type_id="19" item_id="hair_default" style="z-index: 2; transform: scale(0.1875) translate(-50%, -50%); top: {{ $items['Hair']->pos_y / 4 * $scale }}px; left: {{ $items['Hair']->pos_x / 4 * $scale }}px;">
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
        background: url('/uploads/background/bg_wedding_1.jpg');
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

