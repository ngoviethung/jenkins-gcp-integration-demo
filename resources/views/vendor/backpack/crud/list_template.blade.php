@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      $crud->entity_name_plural => url($crud->route),
      trans('backpack::crud.list') => false,
    ];
    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

use App\Models\Template;
use App\Models\Item;
use App\Models\Topic;
use App\Models\BackpackUser;
use App\Models\Type;

$topic_id = app('request')->get('topic');
$user_id = app('request')->get('user');

$query = Template::with('user');
if(isset($user_id)){
    $query->where('admin_id', $user_id);
}
if(isset($topic_id)){
    $query->where('topic_id', $topic_id);
}
$templates = $query->orderBy('created_at', 'DESC')->paginate(9);

$topics = Topic::all();
$users = BackpackUser::role('Stylist')->get(['name', 'id']);


@endphp

@section('header')
    <div class="container-fluid">
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small id="datatable_info_stack">{!! $crud->getSubheading() ?? '' !!}</small>
        </h2>
    </div>
@endsection

@section('content')
    <!-- Default box -->
    <div class="row">

        <!-- THE ACTUAL CONTENT -->
        <div class="{{ $crud->getListContentClass() }}">
            <div class="">

                <div class="row mb-0">
                    <div class="col-2">
                        @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
                            <div class="hidden-print {{ $crud->hasAccess('create')?'with-border':'' }}">

                                @include('crud::inc.button_stack', ['stack' => 'top'])

                            </div>
                        @endif
                    </div>

                    <div class="col-2">
                        <div class="form-group">

                            <select name="topic" class="form-control" style="width: 100%">
                                <option value="">--  Select Topic  --</option>
                                @foreach($topics as $topic)
                                    <option value="{!! $topic->id !!}" @if($topic_id == $topic->id) selected @endif>{!! $topic->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-2">
                        <div class="form-group">

                            <select name="user" class="form-control" style="width: 100%">
                                <option value="">--  Select Creator  --</option>
                                @foreach($users as $user)
                                    <option value="{!! $user->id !!}" @if($user_id == $user->id) selected @endif>{!! $user->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                {{-- Backpack List Filters --}}
                @if ($crud->filtersEnabled())
                    @include('crud::inc.filters_navbar')
                @endif

                <div class="overflow-hidden mt-2">
                    <div class="row">
                        @php
                            $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['code'])->pluck('code')->toArray();
                            $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();
                            $url_media = env('URL_MEDIA');
                            $scale_model = $model->scale;
                            $scale_item = 1/2; //scale tren web de thu nho lai.
                            $scale_model = $scale_model * $scale_item;

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
                        @endphp
                        @foreach($templates as $template)
                            @php

                                $skin_id = $template->model;
                                if($skin_id){
                                    $url_media = env('APP_URL').'/';
                                    $scale_model = $scale_item;

                                    $body_image = $image_skin[$skin_id]['body_image'];
                                    $left_hand_image =  $image_skin[$skin_id]['left_hand_image'];
                                    $right_hand_image = $image_skin[$skin_id]['right_hand_image'];
                                }
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

                                $items = Item::with('type:id,order,code,position_id')->whereIn('id', $arr_item_id)->get();
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
                                    $type_code = $item_checking->type_code;
                                    $type = Type::where('code', $type_code)->first();

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

                            @endphp
                            <div class="col-lg-4 col-sm-4 col-12 item" >
                                <div >
                                    <div  class="preview-container" id="{!! $template->id !!}" style="background-image: url('{!! asset($template->background) !!}');">

                                        <div class="parent-front-layer"></div>
                                        <div class="parent-left-layer"></div>
                                        <div class="parent-mid-layer"></div>
                                        <div class="parent-right-layer"></div>
                                        <div class="parent-back-layer"></div>


                                        <img class="full-model mid-layer" src="{{ $url_media.'/'.$body_image }}"
                                             style="transform: scale({{$scale_model}}) translate(-50%, -50%); top: {{$body_image_pos_y}}px; left: {{$body_image_pos_x}}px;" />
                                        <img class="full-model left-layer" src="{{ $url_media.'/'.$left_hand_image }}"
                                             style="transform: scale({{$scale_model}}) translate(-50%, -50%); top: {{$left_hand_image_pos_y}}px; left: {{$left_hand_image_pos_x}}px;" />
                                        <img class="full-model right-layer" src="{{ $url_media.'/'.$right_hand_image }}"
                                             style="transform: scale({{$scale_model}}) translate(-50%, -50%); top: {{$right_hand_image_pos_y}}px; left: {{$right_hand_image_pos_x}}px;" />

                                        @foreach($items as $item)
                                            @php

                                                $type_code = $item->type->code;
                                                $z_index = $item->type->order;
                                                $scale_item = 1/2;
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

                                                                @endphp
                                                                @if($image)
                                                                    <img  class="item-model front-layer" src="{{ $item->job_id ? $url_media.$image : url($image) }}"
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
                                                                    <img  class="item-model back-layer" src="{{ $item->job_id ? $url_media.$back_image : url($back_image) }}"
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
                                                                    <img  class="item-model mid-layer" src="{{ $item->job_id ? $url_media.$mid_image : url($mid_image) }}"
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
                                                            $image_pos_x = $item->image_pos_x * $scale_item;
                                                            $image_pos_y = $item->image_pos_y  * $scale_item;
                                                            $makeup_items = $item->makeup_items;

                                                            if($makeup_items){
                                                                $makeup_items = json_decode($makeup_items);
                                                            }

                                                        @endphp
                                                        @if(isset($makeup_items[0]))
                                                            @foreach($makeup_items[0] as $k => $image)
                                                                @if($image && $k == $item->child_id)
                                                                    @php
                                                                        $k++;
                                                                    @endphp
                                                                    <img  class="item-model mid-layer" src="{{ $item->job_id ? $url_media.$image : url($image) }}"
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


                                                        @endphp
                                                        @if($image)
                                                            <img  class="item-model front-layer" src="{{ $item->job_id ? $url_media.$image : url($image) }}"
                                                                  style="
                            transform-origin: left top;
                             left: {{ $image_pos_x }}px;
                             top: {{ $image_pos_y }}px;
                             position: absolute;
                             transform: scale({{$scale_item}}) translate(-50%, -50%);"
                                                            />
                                                        @endif

                                                        @if($back_image)
                                                            <img  class="item-model back-layer" src="{{ $item->job_id ? $url_media.$back_image : url($back_image) }}"
                                                                  style="
                        transform-origin: left top;
                        left: {{ $back_image_pos_x }}px;
                        top: {{ $back_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                                                            />
                                                        @endif

                                                        @if($left_image)
                                                            <img  class="item-model left-layer" src="{{ $item->job_id ? $url_media.$left_image : url($left_image) }}"
                                                                  style="
                        transform-origin: left top;
                        left: {{ $left_image_pos_x }}px;
                         top: {{ $left_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                                                            />
                                                        @endif

                                                        @if($right_image)
                                                            <img  class="item-model right-layer" src="{{ $item->job_id ? $url_media.$right_image : url($right_image) }}"
                                                                  style="
                        transform-origin: left top;
                        left: {{ $right_image_pos_x }}px;
                         top: {{ $right_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                                                            />
                                                        @endif
                                                        @if($mid_image)
                                                            <img  class="item-model mid-layer" src="{{ $item->job_id ? $url_media.$mid_image : url($mid_image) }}"
                                                                  style="
                        transform-origin: left top;
                        left: {{ $mid_image_pos_x }}px;
                         top: {{ $mid_image_pos_y}}px;
                         position: absolute;
                         transform: scale({{$scale_item}}) translate(-50%, -50%);"
                                                            />
                                                        @endif

                                                @endswitch
                                        @endforeach


                                    </div>
                                </div>

{{--                                @if(backpack_user()->hasAnyRole('Admin|ItemEditor'))--}}
{{--                                    <div style="justify-content: center;" class="row">--}}
{{--                                        <a  template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-success export-template-to-outfit"style="margin: 10px 10px 0 10px; padding-bottom: 8px;"><i class="fa fa-anchor">Export Outfit</i></a>--}}
{{--                                    </div>--}}
{{--                                @endiffit--}}
{{--                                --}}
                                <h5>{!! $template->name !!}</h5>
                                <h5>Creator: {!! $template->user->name !!}</h5>

                                <div class="row">
                                    <div style="margin: auto; display: block">

                                        @if($template->file_zip != '')
                                            <a href="{!! env('URL_CDN').$template->template !!}" target="_blank" class="btn btn-primary" style="margin-right: 5px"><i class="fa fa-mail-forward">Link</i></a>
                                            <a download href="{!! asset($template->file_zip) !!}" target="_blank" class="btn btn-success link-download" ><i class="fa fa-download">Download</i></a>

                                        @else
                                               <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-primary link-canvas a-{!! $template->id !!}" style="margin-right: 5px;"><i class="fa fa-mail-forward">Link</i></a>
                                                <a id="link-{!! $template->id !!}" style="display: none" href="" target="_blank" class="btn btn-primary created-canvas-{!! $template->id !!}" style="margin-right: 5px;"><i class="fa fa-mail-forward">Link</i></a>


                                               <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-success create-canvas a-{!! $template->id !!}"><i class="fa fa-download">Download</i></a>
                                               <a id="download-{!! $template->id !!}" style="display: none" download href="" class="btn btn-success created-canvas-{!! $template->id !!}"><i class="fa fa-download">Download</i></a>
                                        @endif
                                            @if(backpack_user()->hasAnyRole('Admin') or $template->admin_id == backpack_user()->id)
                                            <a href="{{  Request::url() }}/{!! $template->id !!}/edit" class="btn btn-info"style="margin-left: 5px"><i class="fa fa-edit">Edit</i></a>

                                            <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-warning delete-template"style="margin-left: 5px"><i class="fa fa-trash-o"></i></a>

                                            @endif
                                    </div>
                                </div>

                                <div style="justify-content: center;" class="row">
                                    <a  template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-success clone-template"style="margin: 10px 10px 0 10px; padding-bottom: 8px;"><i class="fa fa-copy">Clone</i></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div><!-- /.box-body -->

                <div class="row">
                    <div class="pagination"  style="margin: 50px auto">
                        {{ $templates->appends(request()->input())->links() }}
                    </div>
                </div>

            </div><!-- /.box -->
        </div>

    </div>
    <div style="position: fixed; width: 100%; z-index: 999; top: 40%; left: 0;">
        <div class="loader" style="margin: auto; display: none "></div>
    </div>
    <div class="locker"></div>
@endsection



@section('after_styles')
    <!-- DATA TABLES -->
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">


    <style>
        .locker {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3); /*lets make it semi-transparent */
            z-index: 998; /*because you could set some z-indexes in your code before, so lets make sure that this will be over every elements in html*/
        }

        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <style>
        .item{
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .preview-container {
            z-index: -999;
            margin: auto;
            width: 352px;
            height: 667px;
            /*background: url('/uploads/background/party_8-1-min.jpg');*/
            text-align: center;
            position: relative;
            background-size: 100% 667px;
            background-repeat: no-repeat;
        }
        .item h5{
            margin-top: 10px;
            text-align: center;
        }
        .full-model {
            transform-origin: left top;
            position: absolute;
        }
        .item-model {
            position: absolute;
            transform-origin: left top;
        }
    </style>
    <!-- CRUD LIST CONTENT - crud_list_styles stack -->
    @stack('crud_list_styles')
@endsection

@section('after_scripts')
    @include('crud::inc.datatables_logic')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{asset('canvas/html2canvas.js')}}"></script>
    <script>

        var locker = $('.locker');

        $('.link-canvas').on('click', function() {

            locker.css('display', 'block');
            $('.loader').show();
            var template_id = $(this).attr("template_id");

            $.ajax({
                type: "POST",
                data: {
                    template_id: template_id,
                },
                url: '/api/merge_and_zip_image',
                success: function (data) {
                    var full_path_file = data.data.file;
                    var filename = data.data.filename;
                    var file_template = data.data.file_template;
                    if(full_path_file){

                        full_path_file =  '{{ asset('/') }}' + full_path_file
                        $('.a-' + template_id).hide();
                        $(".created-canvas-"+template_id).show();
                        $("#download-"+template_id).attr("href", full_path_file);
                        $('#link-'+ template_id).attr("href", file_template);

                        $('.loader').hide();
                        locker.css('display', 'none');

                        var a = document.createElement('a');
                        a.href = file_template;
                        a.target = '_blank';
                        a.click();

                    }else{
                        alert('Create file download faild.')
                        $('.loader').hide();
                        locker.css('display', 'none');

                    }
                },
                error: function(data) {
                    alert('Create file download faild.')
                    $('.loader').hide();
                    locker.css('display', 'none');
                }

            })


        })

        $('.create-canvas').on('click', function() {
            locker.css('display', 'block');
            $('.loader').show();
            var template_id = $(this).attr("template_id");

            $.ajax({
                type: "POST",
                data: {
                    template_id: template_id,
                },
                url: '/api/merge_and_zip_image',
                success: function (data) {
                    var full_path_file = data.data.file;
                    var filename = data.data.filename;
                    var file_template = data.data.file_template;
                    if(full_path_file){
                        download(filename, full_path_file)

                        full_path_file =  '{{ asset('/') }}' + full_path_file

                        $('.a-' + template_id).hide();
                        $(".created-canvas-"+template_id).show();
                        $("#download-"+template_id).attr("href", full_path_file);
                        $('#link-'+ template_id).attr("href", file_template);

                    }else{
                        alert('Create file download faild.')
                        $('.loader').hide();
                        locker.css('display', 'none');

                    }
                },
                error: function(data) {
                    alert('Create file download faild.')
                    $('.loader').hide();
                    locker.css('display', 'none');
                }

            })


        })

        function download(filename, filepath) {

            var element = document.createElement('a');
            var full_path =  '{{ asset('/') }}' + filepath
            element.setAttribute('href', full_path);
            element.setAttribute('download', filename);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
            $('.loader').hide();
            locker.css('display', 'none');

        }

    </script>
    <script>
        $('.delete-template').on('click', function() {
            if (confirm('Are you sure you want to delete this template?')){
                var template_id = $(this).attr('template_id')
                $.ajax({
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    url: '/admin/template/' + template_id,
                    type: 'DELETE',
                    success: function(result) {
                        var url = window.location.href;
                        window.location.href = url;
                    }
                });
            }
        })


    </script>
    <script>
        $('select[name=topic]').on('change', function() {
            var topic = $(this).val()

            var queryParams = new URLSearchParams(window.location.search);
            queryParams.set("topic", topic);
            history.replaceState(null, null, "?"+queryParams.toString());

            var url = window.location.href;
            window.location.href = url
        })
        $('select[name=user]').on('change', function() {
            var user = $(this).val()

            var queryParams = new URLSearchParams(window.location.search);
            queryParams.set("user", user);
            history.replaceState(null, null, "?"+queryParams.toString());

            var url = window.location.href;
            window.location.href = url
        })
    </script>
    <script>
        $('.export-template-to-outfit').on('click', function() {
            var template_id = $(this).attr('template_id')

            swal({

                title: "Confirm",
                text: "Are you sure you want to export this template to Outfit?",
                icon: "info",
                buttons: {
                    export: {
                        text: "Export",
                        value: true,
                        visible: true,
                        className: "bg-success",
                    },
                    cancel: {
                        text: "{!! trans('backpack::crud.cancel') !!}",
                        value: null,
                        visible: true,
                        className: "bg-secondary",
                        closeModal: true,
                    }

                },
            }).then((value) => {

                if (value) {
                    $.ajax({
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "admin_id": {{ backpack_user()->id }},
                            "template_id" : template_id
                        },
                        url: '/api/outfit/create-from-template',
                        type: 'POST',
                        success: function(result) {
                            if (result == 1) {
                                // Show a success notification bubble
                                new Noty({
                                    type: "success",
                                    text: "<strong>Item Exported</strong><br>The item has been exported successfully"
                                }).show();

                            } else {
                                // if the result is an array, it means
                                // we have notification bubbles to show
                                if (result instanceof Object) {
                                    // trigger one or more bubble notifications
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function(message, i) {
                                            new Noty({
                                                type: type,
                                                text: message
                                            }).show();
                                        });
                                    });
                                } else {// Show an error alert
                                    swal({
                                        title: "Not Exported",
                                        text: "There's been an error. Your item might not have been exported.",
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            }
                        },
                        error: function(result) {
                            // Show an alert with the result
                            swal({
                                title: "Not Exported",
                                text: "There's been an error. Your item might not have been exported.",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            });
        })
    </script>
    <script>
        $('.clone-template').on('click', function() {
            var template_id = $(this).attr('template_id')

            swal({

                title: "Confirm",
                text: "Are you sure you want to clone this template?",
                icon: "info",
                buttons: {
                    clone: {
                        text: "Clone",
                        value: true,
                        visible: true,
                        className: "bg-success",
                    },
                    cancel: {
                        text: "{!! trans('backpack::crud.cancel') !!}",
                        value: null,
                        visible: true,
                        className: "bg-secondary",
                        closeModal: true,
                    }

                },
            }).then((value) => {

                if (value) {
                    $.ajax({
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "admin_id": {{ backpack_user()->id }},
                            "template_id" : template_id
                        },
                        url: '/api/template/clone-template',
                        type: 'POST',
                        success: function(result) {
                            if (result == 1) {
                                // Show a success notification bubble
                                new Noty({
                                    type: "success",
                                    text: "<strong>Item Cloned</strong><br>The item has been cloned successfully"
                                }).show();
                                //alert("The item has been cloned successfully.")
                                setTimeout(function(){
                                    var url = window.location.href;
                                    window.location.href = url;
                                }, 1000);


                            } else {
                                // if the result is an array, it means
                                // we have notification bubbles to show
                                if (result instanceof Object) {
                                    // trigger one or more bubble notifications
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function(message, i) {
                                            new Noty({
                                                type: type,
                                                text: message
                                            }).show();
                                        });
                                    });
                                } else {// Show an error alert
                                    swal({
                                        title: "Not Cloned",
                                        text: "There's been an error. Your item might not have been cloned.",
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            }
                        },
                        error: function(result) {
                            // Show an alert with the result
                            swal({
                                title: "Not Cloned",
                                text: "There's been an error. Your item might not have been cloned.",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            });
        })
    </script>
    <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
    @stack('crud_list_scripts')
@endsection
