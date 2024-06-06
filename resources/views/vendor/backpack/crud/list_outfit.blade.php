@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      $crud->entity_name_plural => url($crud->route),
      trans('backpack::crud.list') => false,
    ];
    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

use App\Models\Outfit;
use App\Models\Item;
use App\Models\Topic;
use App\Models\BackpackUser;
use App\Models\Type;

$type_scale_1_4 = Type::where('scale', 0.25)->get(['id'])->pluck('id')->toArray();

$topic_id = app('request')->get('topic');
$user_id = app('request')->get('user');
$category_id = app('request')->get('category');

$query = Outfit::with('user','topic');
if(isset($user_id)){
    $query->where('admin_id', $user_id);
}
if(isset($topic_id)){
    $query->where('topic_id', $topic_id);
}
if(isset($category_id)){
    $query->where('category', $category_id);
}
$per_page = 9;
$templates = $query->orderBy('created_at', 'DESC')->paginate($per_page);

$topics = Topic::where('use_in_game', 1)->get();
//$users = BackpackUser::role('Stylist')->get(['name', 'id']);

$authorizedRoles = ['ItemEditor', 'Admin'];
$users = BackpackUser::whereHas('roles', static function ($query) use ($authorizedRoles) {
                    return $query->whereIn('name', $authorizedRoles);
                })->get();

$entries = $templates->total();

$page = request()->get('page');
if(!$page){
    $page = 1;
}
$total = Outfit::count();

$m = $per_page * $page;
$n = $m - $per_page + 1;


@endphp

@section('header')
    <div class="container-fluid">
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>Showing {{$n}} to {{$m}} of {{ $entries }} entries (filtered from {{$total}} total entries)</small>
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
                    <div class="col-3">
                        @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
                            <div style="display: flex;" class="hidden-print {{ $crud->hasAccess('create')?'with-border':'' }}">

                                @include('crud::inc.button_stack', ['stack' => 'top'])
                                @include('crud::inc.button_stack', ['stack' => 'top2'])

                            </div>
                        @endif
                    </div>

                    <div class="col-2">
                        <div class="form-group">

                            <select name="topic" class="form-control" style="width: 100%">
                                <option value="">--  All Topic  --</option>
                                @foreach($topics as $topic)
                                    <option value="{!! $topic->id !!}" @if($topic_id == $topic->id) selected @endif>{!! $topic->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-2">
                        <div class="form-group">

                            <select name="category" class="form-control" style="width: 100%">
                                <option value="">--  All Category  --</option>
                                <option value="1" @if($category_id === '1') selected @endif>Good</option>
                                <option value="0" @if($category_id === '0') selected @endif>Bad</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-2">
                        <div class="form-group">

                            <select name="user" class="form-control" style="width: 100%">
                                <option value="">--  All Creator  --</option>
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
                        @foreach($templates as $template)
                            @php
                                $arr_item_id = $template->item_id;
                                $arr_item_id = explode(',', $arr_item_id);
                                $category = $template->category;

                                $items = Item::with('type:id,order')->whereIn('id', $arr_item_id)->get();

                                /* comment ngay 2022-27-01 bo? model
                                $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();
                                $url_media = env('URL_MEDIA');
                                $scale = 1/2;
                                $default_items = json_decode($model->default_items);
                                $items_default = [];
                                foreach ($default_items as $item){
                                    $items_default[$item->type] = $item;
                                }

                                $items_default = [
                                    'lens_default' => [$items_default['Lens']->image, 20, $items_default['Lens']->pos_x / 4 * $scale, $items_default['Lens']->pos_y / 4 * $scale, -1],
                                    'eyebrows_default' => [$items_default['Eyebrows']->image, 17, $items_default['Eyebrows']->pos_x / 4 * $scale, $items_default['Eyebrows']->pos_y / 4 * $scale, 50],
                                    'lips_default' => [$items_default['Lips']->image, 21, $items_default['Lips']->pos_x / 4 * $scale, $items_default['Lips']->pos_y / 4 * $scale, 20],
                                    'dress_set_default' => [$items_default['DressSet']->image, 1, $items_default['DressSet']->pos_x * $scale, $items_default['DressSet']->pos_y * $scale, 100],
                                    'hair_default' => [$items_default['Hair']->image, 19, $items_default['Hair']->pos_x / 4 * $scale, $items_default['Hair']->pos_y / 4 * $scale, 155]
                                ];
                                */

                                $arr_item_checking_id = $template->item_checking_id;
                                if(!empty($arr_item_checking_id)){
                                    $arr_item_checking_id = explode(',', $arr_item_checking_id);
                                }else{
                                    $arr_item_checking_id = [];
                                }

                            @endphp
                            <div class="col-lg-4 col-sm-4 col-12 item" >
                                <div >

                                    <div  class="preview-container" id="{!! $template->id !!}" style="background-image: url('{!! asset($template->background) !!}');">

                                        {{-- comment ngay 2022-27-01 bo? model
                                        @if($template->model != '')
                                            <img class="full-model" src="{!! asset($template->model) !!}" style=" transform: scale({{$model->scale * $scale}}) translate(-50%, -50%); top: {{$model->pos_y * $scale}}px; left: {{$model->pos_x * $scale}}px;">
                                        @endif


                                        @foreach($items_default as $key => $item)
                                            @if(in_array($key, $arr_item_id))
                                                <img class="item-model" src="{{ $url_media.$item[0] }}"
                                                     style="
                                                     @if(in_array($item[1], $type_scale_1_4))
                                                         transform: scale(0.125) translate(-50%, -50%);
                                                         left: {{ $item[2]}}px;
                                                         top: {{ $item[3]}}px;
                                                         z-index: {{$item[4]}};

                                                     @else
                                                         transform: scale(0.5) translate(-50%, -50%);
                                                         left: {{ $item[2]}}px;
                                                         top: {{ $item[3]}}px;
                                                         z-index: {{$item[4]}};

                                                     @endif
                                                         "
                                                >
                                            @endif
                                        @endforeach
                                        --}}

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
                                            <img class="item-model" src="{{ asset('/') }}{!! $item->image !!}"
                                                 style="
                                                 @if(in_array($item->type_id, $type_scale_1_4))
                                                     transform: scale(0.125) translate(-50%, -50%);
                                                     left: {{ $pos_x / 2 }}px;
                                                     top: {{ ($pos_y  / 2 )}}px;
                                                     z-index: {{$z_index}};

                                                 @else
                                                     transform: scale(0.5) translate(-50%, -50%);
                                                     left: {{ $pos_x / 2 }}px;
                                                     top: {{ ($pos_y  / 2 )}}px;
                                                     z-index: {{$z_index}};

                                                 @endif
                                                     "
                                            >
                                            @if($item->thumb_top != '')
                                                <img class="item-model" src="{{ asset('/') }}{!! $item->thumb_top !!}"
                                                     style="
                                                     @if(in_array($item->type_id, $type_scale_1_4))
                                                         transform: scale(0.125) translate(-50%, -50%);
                                                         left: {{ $pos_x / 2 }}px;
                                                         top: {{ ($pos_y  / 2 )}}px;
                                                         z-index: -{{$z_index}};

                                                     @else
                                                         transform: scale(0.5) translate(-50%, -50%);
                                                         left: {{ $pos_x / 2 }}px;
                                                         top: {{ ($pos_y  / 2 )}}px;
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
                                                $z_index = $arr_value[3];
                                                $image = $arr_value[4];


                                                if(in_array($type, $type_scale_1_4)) {
                                                   $pos_x = $pos_x / 4;
                                                   $pos_y = $pos_y / 4;
                                               }

                                            @endphp

                                            @if(isset($arr_value[5]))
                                                @php
                                                    $thumb_top = $arr_value[5];
                                                @endphp
                                                @if($thumb_top != 'null' && $thumb_top != '')
                                                    <img class="item-model" src="{{ asset('/') }}{!! $thumb_top !!}"
                                                         style="
                                                         @if(in_array($type, $type_scale_1_4))
                                                             transform: scale(0.125) translate(-50%, -50%);
                                                             left: {{ $pos_x / 2 }}px;
                                                             top: {{ ($pos_y  / 2 )}}px;
                                                             z-index: -{{$z_index}};

                                                         @else
                                                             transform: scale(0.5) translate(-50%, -50%);
                                                             left: {{ $pos_x / 2 }}px;
                                                             top: {{ ($pos_y  / 2 )}}px;
                                                             z-index: -{{$z_index}};

                                                         @endif
                                                             "
                                                    >
                                                @endif
                                            @endif

                                            <img class="item-model" src="{{ asset('/') }}{!! $image !!}"
                                                 style="
                                                 @if(in_array($type, $type_scale_1_4))
                                                     transform: scale(0.125) translate(-50%, -50%);
                                                     left: {{ $pos_x / 2 }}px;
                                                     top: {{ ($pos_y  / 2 )}}px;
                                                     z-index: {{$z_index}};

                                                 @else
                                                     transform: scale(0.5) translate(-50%, -50%);
                                                     left: {{ $pos_x / 2 }}px;
                                                     top: {{ ($pos_y  / 2 )}}px;
                                                     z-index: {{$z_index}};

                                                 @endif
                                                     "
                                            >

                                        @endforeach

                                    </div>
                                </div>
                                <h5>{!! $template->name !!}</h5>
                                <h5>Creator: {!! $template->user->name !!}</h5>
                                <h5>Topic: {!! $template->topic->name !!}</h5>
                                @if($category == 1)
                                    <h5 style="color: green">Good</h5>
                                @else
                                    <h5 style="color: red">Bad</h5>
                                @endif

                                <!--
                                <div class="row">
                                    @if($template->file_zip != '')
                                    <div class="col-lg-5 col-sm-5" style="text-align: right">
                                        <a download href="{!! asset($template->file_zip) !!}" class="btn btn-success link-download"><i class="fa fa-download">Quick Download</i></a>
                                        </div>
                                        <div class="col-lg-5 col-sm-5" style="text-align: left">
                                            <a href="/admin/template/download?template_id={!! $template->id !!}" target="_blank" class="btn btn-info link-download"><i class="fa fa-download">Preview Download</i></a>
                                        </div>
                                        <div class="col-lg-2 col-sm-2" style="padding: 0">
                                            <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-warning delete-template"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                    @else

                                    <div class="col-lg-8 col-sm-8" style="text-align: right">
                                        <a href="/admin/template/download?template_id={!! $template->id !!}" target="_blank" class="btn btn-info link-download"><i class="fa fa-download">Preview Download</i></a>
                                        </div>
                                        <div class="col-lg-2 col-sm-2" style="padding: 0; text-align: left">
                                            <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-warning delete-template"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                    @endif


                                </div>
                                -->
                                <div class="row">
                                    <div style="margin: auto; display: block">

{{--                                        @if($template->file_zip != '')--}}
{{--                                            <a href="{!! env('URL_CDN').$template->template !!}" target="_blank" class="btn btn-primary" style="margin-right: 5px"><i class="fa fa-mail-forward">Link</i></a>--}}
{{--                                            <a download href="{!! asset($template->file_zip) !!}" target="_blank" class="btn btn-success link-download" ><i class="fa fa-download">Download</i></a>--}}

{{--                                        @else--}}
{{--                                            <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-primary link-canvas a-{!! $template->id !!}" style="margin-right: 5px;"><i class="fa fa-mail-forward">Link</i></a>--}}
{{--                                            <a id="link-{!! $template->id !!}" style="display: none" href="" target="_blank" class="btn btn-primary created-canvas-{!! $template->id !!}" style="margin-right: 5px;"><i class="fa fa-mail-forward">Link</i></a>--}}


{{--                                            <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-success create-canvas a-{!! $template->id !!}"><i class="fa fa-download">Download</i></a>--}}
{{--                                            <a id="download-{!! $template->id !!}" style="display: none" download href="" class="btn btn-success created-canvas-{!! $template->id !!}"><i class="fa fa-download">Download</i></a>--}}
{{--                                        @endif--}}

                                        @if(backpack_user()->hasAnyRole('Admin|ItemEditor') or $template->admin_id == backpack_user()->id)
                                            <a href="{{  Request::url() }}/{!! $template->id !!}/edit" class="btn btn-info"style="margin-left: 5px"><i class="fa fa-edit">Edit</i></a>

                                            <a template_id={!! $template->id !!} href="javascript:void(0)" class="btn btn-warning delete-template"style="margin-left: 5px"><i class="fa fa-trash-o"></i></a>
                                        @endif
                                    </div>
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
{{--    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>--}}

    <script src="{{ asset('packages/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('packages/backpack/crud/js/form.js') }}"></script>
    <script src="{{ asset('packages/backpack/crud/js/list.js') }}"></script>

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
                //url: '/api/merge_and_zip_image',
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
                //url: '/api/merge_and_zip_image',
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
                    url: '/admin/outfit/' + template_id,
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

        $('select[name=category]').on('change', function() {
            var category = $(this).val()

            var queryParams = new URLSearchParams(window.location.search);
            queryParams.set("category", category);
            history.replaceState(null, null, "?"+queryParams.toString());

            var url = window.location.href;
            window.location.href = url
        })

    </script>
    <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
    @stack('crud_list_scripts')
@endsection
