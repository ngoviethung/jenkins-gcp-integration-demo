@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      $crud->entity_name_plural => url($crud->route),
      trans('backpack::crud.add') => false,
    ];

    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
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
            <div class="parent-front-layer"></div>
            <div class="parent-left-layer"></div>
            <div class="parent-mid-layer"></div>
            <div class="parent-right-layer"></div>
            <div class="parent-back-layer"></div>
            @php
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
                    if($k == 0){
                        $left_hand_image = $skin->left_hand_image;
                        $right_hand_image = $skin->right_hand_image;
                        $body_image = $skin->body_image;
                    }
                }
                $url_media = env('APP_URL').'/';
                $scale = 0.75;

            @endphp
            <img class="full-model mid-layer" src="{{ $url_media.'/'.$body_image }}"
                 style=" position: absolute;
                            transform-origin: left top;
                             transform: scale({{$scale}}) translate(-50%, -50%);
                              left: {{$body_image_pos_x}}px; top: {{$body_image_pos_y}}px; " />

            <img class="full-model left-layer" src="{{ $url_media.'/'.$left_hand_image }}"
                 style=" position: absolute;
                            transform-origin: left top;
                            transform: scale({{$scale}}) translate(-50%, -50%);
                            left: {{$left_hand_image_pos_x}}px; top: {{$left_hand_image_pos_y}}px; " />

            <img class="full-model right-layer" src="{{ $url_media.'/'.$right_hand_image  }}"
                 style=" position: absolute;
                            transform-origin: left top;
                            transform: scale({{$scale}}) translate(-50%, -50%);
                         left: {{$right_hand_image_pos_x}}px; top: {{$right_hand_image_pos_y}}px; " />

        </div>
    </div>

@endsection


<style>

    #preview-container {
        top: 0;
        right: 0;
        z-index: -999;
        width: 535.875px;
        height: 1000.5px;
        background: url('/uploads/background/beach_108.jpg');
        text-align: center;
        position: fixed;
        background-size: 100% 1000.5px;
        background-repeat: no-repeat;
    }

</style>

