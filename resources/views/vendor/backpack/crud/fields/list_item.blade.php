@php

    use App\Models\Type;
    $types = Type::whereNull('parent_id')->where('name', '!=', 'Skin')->get();
    $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['code'])->pluck('code')->toArray();
    $check_scale_1_4 = '';
    foreach ($type_scale_1_4 as $code){
        $check_scale_1_4 .= '"'.$code.'",';
    }
    $type_scale_1_4 = substr($check_scale_1_4, 0, -1);
    $url = env('APP_URL').'/';

    $image_skin = [];
    $skins = DB::table('skins')->get();
    foreach ($skins as $skin){
        $image_skin[$skin->id] = [
            'left_hand_image' => $skin->left_hand_image,
            'right_hand_image' => $skin->right_hand_image,
            'body_image' => $skin->body_image
        ];
    }


@endphp
@include('crud::fields.inc.wrapper_start')
<div class="parent-type">
    <label>Parrent Type</label>
    <div class="tab">
        @foreach($types as $type)
            <button type_id={!! $type->id !!} type_code="{{ $type->code }}" type_name="{{ $type->name }}"  type_order={!! $type->order !!} type="button" class="btn btn-info tablinks" onclick="getChildrenType(event, {!! $type->id !!}, '{!! $type->code !!}', '{!! $type->name !!}', '{!! $type->order !!}')">{!! $type->name !!}</button>
        @endforeach
    </div>
</div>
<br>
<div class="children-type">
    <label>Children Type</label>
    <div class="tab" id="children-tab">
    </div>
</div>
<br>
<label>{!! $field['label'] !!}</label>
<div id="list-item"></div>
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
<input type="hidden" name="item_id" value="@if(isset($field['value'])){!! $field['value'] !!}@endif">
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
            .parrent-item{
                text-align: center;
                display: inline-block;
                width: 25%;
                margin: 5px 0;
                padding: 0 10px;
            }
            .link-preview{
                margin: 5px 0;
            }
            /*.item{*/
            /*    display: inline-block;*/
            /*    width: 25%;*/
            /*    margin: 5px 0;*/
            /*    padding: 0 10px;*/
            /*}*/
            .item button{
                display: block;
                margin: auto;
                border: 1px solid darkseagreen;
                background-color: white;
                outline-color: yellowgreen;
            }
            .thumbnail-hair{
                display: inline-block;
                width: 25%;
                margin: 5px 0;
            }
            .thumbnail-makeup{
                display: inline-block;
                width: 25%;
                margin: 5px 0;
            }
            .thumbnail-hair button{
                display: block;
                margin: auto;
                border: 1px solid darkseagreen;
                background-color: white;
                outline-color: yellowgreen;
            }
            .thumbnail-makeup button{
                display: block;
                margin: auto;
                border: 1px solid darkseagreen;
                background-color: white;
                outline-color: yellowgreen;
            }
            .type-20, .type-5, .type-6, .type-16, .type-17, .type-18, .type-21, .type-22, .type-25, .type-27{
                width: 50%;
                margin: 10px 0;
            }
            #list-item{
                position: relative;
                overflow-y: auto;
                height: 100px;
            }
            .img-item{
                width: 100%;
            }
            .item-checking{
                background-color: cornsilk !important;
            }
            .item-model {
                position: absolute;
                transform-origin: left top;
            }
            /* Style the tab */
            .tab {
                overflow: hidden;
                display: flex;
                height: 180px;
                overflow-y: hidden;
                overflow-x: auto;
            }
            #children-tab{
                height: 150px !important;
            }
            .tab button:hover{
                background-color: #5cb85c !important;
            }
            .tab button {
                margin: 5px;
            }
            .tab button.active {
                background-color: #5cb85c !important;
            }
            #list-item button{
                text-align: left;
            }
            #list-item button span{
                position: absolute;
            }

            #children-tab button {
                width: 100px;
                text-align: left;
            }
        </style>
    @endpush
    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include select2 js-->
        <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
        @if (app()->getLocale() !== 'en')
            <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
        @endif
        <script>
            function bpFieldInitSelect2FromArrayElement(element) {
                if (!element.hasClass("select2-hidden-accessible"))
                {
                    element.select2({
                        theme: "bootstrap"
                    }).on('select2:unselect', function(e) {
                        if ($(this).attr('multiple') && $(this).val().length == 0) {
                            $(this).val(null).trigger('change');
                        }
                    });
                }
            }
        </script>
        <!-- Parent Type-->
        <script>


            $('.select2_from_array').on('change', function(){

                var count = 0;
                var topic_id = $('select[name="topic_id"]').val();
                var material_id = $('select[name="material_id"]').val();
                var color_id = $('select[name="color_id"]').val();
                var pattern_id = $('select[name="pattern_id"]').val();

                var type_code = $('.tablinks.active').attr('type_code');
                var topic_name = $(this).find('option:selected').text()
                $('#list-item').html('');

                if(type_code == 'hair' || type_code == 'makeup'){
                    $('#children-tab').html('');
                    var type_id = $('.tablinks.active').attr('type_id');
                    var type_name = $('.tablinks.active').attr('type_name');
                    var type_order = $('.tablinks.active').attr('type_order');
                }else{
                    var type_id = $('.tablinks-children.active').attr('type_id');
                    var type_name = $('.tablinks-children.active').attr('type_name');
                    var type_order = $('.tablinks-children.active').attr('type_order');
                }

                switch (type_code){
                    case 'hair':
                        getHairs(topic_id, type_id, type_code, type_order, topic_name, type_name, material_id, color_id, pattern_id)
                        break;
                    case 'makeup':
                        getMakups(topic_id, type_id, type_code, type_order, topic_name, type_name, material_id, color_id, pattern_id)
                        break;
                    default:
                        if(type_id){
                            position_code = null
                            getNormals(topic_id, type_id, type_code, type_order, topic_name, type_name, position_code, material_id, color_id, pattern_id);
                        }
                }
            })


            //change parent type
            function getChildrenType(evt, type_id, type_code, type_name, type_order) {
                $('#list-item').html('');
                $('#children-tab').html('');

                var i, tabcontent, tablinks;
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                evt.currentTarget.className += " active";
                var count = 0;

                var topic_id = $('select[name="topic_id"]').val()
                var topic_name = $('select[name="topic_id"] option:selected').text()
                var material_id = $('select[name="material_id"]').val();
                var color_id = $('select[name="color_id"]').val();
                var pattern_id = $('select[name="pattern_id"]').val();


                switch (type_code) {
                    case 'hair':
                        //if(topic_id){
                        getHairs(topic_id, type_id, type_code, type_order, topic_name, type_name, material_id, color_id, pattern_id)
                        //}
                        break;
                    case 'makeup':
                        //if(topic_id){
                            getMakups(topic_id, type_id, type_code, type_order, topic_name, type_name, material_id, color_id, pattern_id)
                        //}
                        break;
                    default:
                        addChildrenTypeNormalToList(type_id, type_code)

                }

            }
            function getHairs(topic_id, type_id, type_code, type_order, topic_name, type_name, material_id, color_id, pattern_id){
                $.ajax({
                    url: '/api-admin/get-item-template?topic_id=' + topic_id + '&type_id=' + type_id + '&material_id=' + material_id + '&color_id=' + color_id + '&pattern_id=' + pattern_id,
                    success: function (data) {
                        var items = data.data;
                        addChildrenTypeHairToList(items, topic_id, type_id, type_code, type_order, 0)
                    }
                })

                $.ajax({
                    url: '{{env('URL_MEDIA')}}' + 'api/get-item-checking',
                    type: 'POST',
                    data: {
                        app_id: {{env('APP_ID')}},
                        topic_name: topic_name,
                        type_name: type_name,
                        material_id: material_id,
                        color_id: color_id,
                        pattern_id: pattern_id
                    },
                    success: function (data) {
                        var items = data.data;
                        addChildrenTypeHairToList(items, topic_id, type_id, type_code, type_order, 1)
                    }
                })
            }
            function getMakups(topic_id, type_id, type_code, type_order, topic_name, type_name, material_id, color_id, pattern_id){
                $.ajax({
                    url: '/api-admin/get-item-template?topic_id=' + topic_id + '&type_id=' + type_id + '&material_id=' + material_id + '&color_id=' + color_id + '&pattern_id=' + pattern_id,
                    success: function (data) {
                        var items = data.data;
                        addChildrenTypeMakeupToList(items, topic_id, type_id, type_code, type_order, 0)
                    }
                })

                $.ajax({
                    url: '{{env('URL_MEDIA')}}' + 'api/get-item-checking',
                    type: 'POST',
                    data: {
                        app_id: {{env('APP_ID')}},
                        topic_name: topic_name,
                        type_name: type_name,
                        material_id: material_id,
                        color_id: color_id,
                        pattern_id: pattern_id
                    },
                    success: function (data) {
                        var items = data.data;
                        addChildrenTypeMakeupToList(items, topic_id, type_id, type_code, type_order, 1)

                    }
                })
            }
            function addChildrenTypeNormalToList(type_id, type_code){
                var children_tab = '';
                $.ajax({
                    url: '/api-admin/get-children-type?parent_id=' + type_id,
                    success: function (data) {
                        var items = data.data;
                        $.each(items, function(key, value){
                            var position_code = value['position'] ? value['position'].code : 0;
                            children_tab += '<button type_id=' + value['id'] + ' type_code="' + type_code + '" type_name="' + value['name'] + '" type_order=' + value['order'] + ' type="button" class="btn btn-info tablinks-children" onclick="getNormalItems(event, ' + value['id'] + ',\'' + value['name'] + '\',' + value['order'] + ',\'' + type_code + '\'' +  ',\'' + position_code + '\'' + ')">' + value['name'] + '</button>'
                        })
                        $('#children-tab').append(children_tab);
                    }
                })

            }
            //get item and add to list
            function getNormalItems(evt, type_id, type_name, type_order, type_code, position_code) {
                var i, tabcontent, tablinks;
                tablinks = document.getElementsByClassName("tablinks-children");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                evt.currentTarget.className += " active";
                var topic_id = $('select[name="topic_id"]').val()
                var topic_name = $('select[name="topic_id"] option:selected').text()
                var material_id = $('select[name="material_id"]').val();
                var color_id = $('select[name="color_id"]').val();
                var pattern_id = $('select[name="pattern_id"]').val();

                var count = 0;
                $('#list-item').html('');
                //if(topic_id){
                    getNormals(topic_id, type_id, type_code, type_order, topic_name, type_name, position_code, material_id, color_id, pattern_id);
                //}
            }
            function getNormals(topic_id, type_id, type_code, type_order, topic_name, type_name, position_code, material_id, color_id, pattern_id){
                $.ajax({
                    url: '/api-admin/get-item-template?topic_id=' + topic_id + '&type_id=' + type_id + '&material_id=' + material_id + '&color_id=' + color_id + '&pattern_id=' + pattern_id,
                    success: function (data) {
                        var items = data.data;
                        count = items.length
                        addItemNormalToList(items, type_id, type_code, type_order, 0, position_code)
                    }
                })

                var item_checking = '';
                $.ajax({
                    url: '{{env('URL_MEDIA')}}' + 'api/get-item-checking',
                    type: 'POST',
                    data: {
                        app_id: {{env('APP_ID')}},
                        topic_name: topic_name,
                        type_name: type_name,
                        material_id: material_id,
                        color_id: color_id,
                        pattern_id: pattern_id
                    },
                    //CrossDomain:true,
                    success: function (data) {
                        var items = data.data;
                        addItemNormalToList(items, type_id, type_code, type_order, 1, position_code)

                    }
                })
            }


            function addChildrenTypeHairToList(items, topic_id, type_id, type_code, type_order, checking) {

                var item = '';
                $.each(items, function(key, value){
                    var n = key + 1;
                    item += '<a class="thumbnail-hair type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' type_order=' + type_order  + '>'
                    if(checking == 0){
                        item += '<button class="tablinks-children" type="button" style="position: relative;" onclick="getHairItems(event,' + value['id'] + ',' + type_id + ',\'' + type_code + '\','  + type_order  + ',' + checking + ')">'
                    }else{
                        item += '<button class="item-checking tablinks-children"  type="button" style="position: relative;" onclick="getHairItems(event,' + value['id'] + ',' + type_id + ',\'' + type_code + '\','  + type_order + ',' + checking + ')">'
                    }
                    item += '<span>' + n + '</span>'
                    if(value['vip'] == 1){
                        item += '<img style="right: 0; position: absolute;" src="{{asset('/icons/300px-star.svg.png')}}" width=30px>'
                    }
                    if(checking == 0){
                        item += '<img class="img-item" src="{{ asset('/') }}' + value['thumbnail'] + '">'
                    }else{
                        item += '<img class="img-item" src="{{env('URL_MEDIA')}}' + value['thumbnail'] + '">'
                    }

                    item += '</button>'
                    item += '</a>'
                })
                $('#children-tab').append(item);
                $("#list-item").css("height", "300px");

            }
            function addChildrenTypeMakeupToList(items, topic_id, type_id, type_code, type_order, checking) {
                var item = '';
                $.each(items, function(key, value){
                    var n = key + 1;
                    item += '<a class="thumbnail-makeup type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' type_order=' + type_order  + '>'
                    if(checking == 0){
                        item += '<button class="tablinks-children" type="button" style="position: relative;" onclick="getMakeupItems(event,' + value['id'] + ',' + type_id + ',\'' + type_code + '\','  + type_order  + ',' + checking + ')">'
                    }else{
                        item += '<button class="item-checking tablinks-children"  type="button" style="position: relative;" onclick="getMakeupItems(event,' + value['id'] + ',' + type_id + ',\'' + type_code + '\','  + type_order + ',' + checking + ')">'
                    }
                    item += '<span>' + n + '</span>'
                    if(value['vip'] == 1){
                        item += '<img style="right: 0; position: absolute;" src="{{asset('/icons/300px-star.svg.png')}}" width=30px>'
                    }
                    if(checking == 0){
                        item += '<img class="img-item" src="{{ asset('/') }}' + value['thumbnail'] + '">'
                    }else{
                        item += '<img class="img-item" src="{{env('URL_MEDIA')}}' + value['thumbnail'] + '">'
                    }

                    item += '</button>'
                    item += '</a>'
                })
                $('#children-tab').append(item);
                $("#list-item").css("height", "500px");

            }


            //get item and add to list
            function getHairItems(evt, item_id, type_id, type_code, type_order, checking) {

                var i, tabcontent, tablinks;
                tablinks = document.getElementsByClassName("tablinks-children");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                evt.currentTarget.className += " active";

                var topic_name = $('select[name="topic_id"] option:selected').text()
                $('#list-item').html('');

                if(checking == 0){
                    $.ajax({
                        url: '/api-admin/get-item-detail?item_id=' + item_id,
                        success: function (data) {
                            var items = data.data;
                            addItemHairToList(items, type_id, type_code, type_order, checking)
                        }
                    })
                }else{
                    $.ajax({
                        url: '{{env('URL_MEDIA')}}' + 'api/get-item-detail',
                        type: 'POST',
                        data: {
                            item_id: item_id
                        },
                        //CrossDomain:true,
                        success: function (data) {
                            var items = data.data;
                            addItemHairToList(items, type_id, type_code, type_order, checking)
                        }
                    })
                }

            }

            function getMakeupItems(evt, item_id, type_id, type_code, type_order, checking) {
                var i, tabcontent, tablinks;
                tablinks = document.getElementsByClassName("tablinks-children");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                evt.currentTarget.className += " active";
                var topic_name = $('select[name="topic_id"] option:selected').text()
                $('#list-item').html('');

                if(checking == 0){
                    $.ajax({
                        url: '/api-admin/get-item-detail?item_id=' + item_id,
                        success: function (data) {
                            var items = data.data;
                            addItemMakeupToList(items, type_id, type_code, type_order, checking)
                        }
                    })
                }else{
                    $.ajax({
                        url: '{{env('URL_MEDIA')}}' + 'api/get-item-detail',
                        type: 'POST',
                        data: {
                            item_id: item_id
                        },
                        success: function (data) {
                            var items = data.data;
                            addItemMakeupToList(items, type_id, type_code, type_order, checking)
                        }
                    })
                }


            }


            //add to list
            function addItemNormalToList(items, type_id, type_code, type_order, checking, position_code){

                var item = '';
                $.each(items, function(key, value){
                    var n = key + 1;
                    item += '<div class="parrent-item">'
                    if(checking == 0){
                        item += '<a class="item type-' + value['type_id'] +'" item_id=' + value['id'] + ' type_id=' + value['type_id'] + ' type_order=' + type_order + ' type_code=' + type_code + ' position_code=' + position_code
                    }else{
                        item += '<a checking=1 class="item type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' type_order=' + type_order + ' type_code=' + type_code + ' position_code=' + position_code
                    }
                    item += ' image=' + encodeURIComponent(value['image']) + ' image_pos_x=' + value['image_pos_x'] + ' image_pos_y=' + value['image_pos_y']
                        + ' left_image=' + encodeURIComponent(value['left_image']) + ' left_image_pos_x=' + value['left_image_pos_x'] + ' left_image_pos_y=' + value['left_image_pos_y']
                        + ' right_image=' + encodeURIComponent(value['right_image']) + ' right_image_pos_x=' + value['right_image_pos_x'] + ' right_image_pos_y=' + value['right_image_pos_y']
                        + ' back_image=' + encodeURIComponent(value['back_image']) + ' back_image_pos_x=' + value['back_image_pos_x'] + ' back_image_pos_y=' + value['back_image_pos_y']
                        + ' mid_image=' + encodeURIComponent(value['mid_image']) + ' mid_image_pos_x=' + value['mid_image_pos_x'] + ' mid_image_pos_y=' + value['mid_image_pos_y']
                        + '>'
                    if(checking == 0){
                        item += '<button  type="button" style="position: relative;">'
                    }else{
                        item += '<button class="item-checking" type="button" style="position: relative;">'
                    }

                    item += '<span>' + n + '</span>'
                    if(value['vip'] == 1 && checking == 0){
                        item += '<img style="right: 0; position: absolute;" src="{{asset('/icons/300px-star.svg.png')}}" width=30px>'
                    }
                    if(checking == 0){
                        item += '<img class="img-item" src="{{ asset('/') }}' + value['thumbnail'] + '">'
                    }else{
                        item += '<img class="img-item" src="{{env('URL_MEDIA')}}' + value['thumbnail'] + '">'
                    }
                    item += '</button>'
                    item += '</a>'

                    if(checking == 0){
                        item += '<a href="{{ env('URL_MEDIA') }}' + 'admin/done/redirect-to-show?id=' + value['id'] + '" class="btn btn-primary link-preview" target="_blank">preview</a>'
                    }else{
                        item += '<a href="{{ env('URL_MEDIA') }}' + 'admin/checking/' + value['id'] + '/show" class="btn btn-primary link-preview" target="_blank">preview</a>'
                    }
                    item += '</div>'


                })
                $("#list-item").css("height", "700px");
                $('#list-item').append(item);
            }


            //add to list
            function addItemHairToList(items, type_id, type_code, type_order, checking){
                var item = '';
                $.each(items, function(key, value){

                    var hair_items = value['hair_items']
                    hair_items = JSON.parse(hair_items)
                    $.each(hair_items, function(ke, val){
                        var n = ke + 1;

                        if(checking == 0){
                            item += '<a class="item type-' + value['type_id'] +'" item_id=' + value['id'] + ' type_id=' + value['type_id'] + ' children_type_id=' + ke + ' type_order=' + type_order + ' type_code=' + type_code
                        }else{
                            item += '<a checking=1 class="item type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' children_type_id=' + ke + ' type_order=' + type_order + ' type_code=' + type_code
                        }
                        item += ' image=' + encodeURIComponent(val['image']) + ' image_pos_x=' + val['image_pos_x'] + ' image_pos_y=' + val['image_pos_y']
                            + ' back_image=' + encodeURIComponent(val['back_image']) + ' back_image_pos_x=' + val['back_image_pos_x'] + ' back_image_pos_y=' + val['back_image_pos_y']
                            + ' mid_image=' + encodeURIComponent(val['mid_image']) + ' mid_image_pos_x=' + val['mid_image_pos_x'] + ' mid_image_pos_y=' + val['mid_image_pos_y']
                            + '>'
                        if(checking == 0){
                            item += '<button  type="button" style="position: relative;">'
                        }else{
                            item += '<button class="item-checking" type="button" style="position: relative;">'
                        }

                        item += '<span>' + n + '</span>'
                        if(value['vip'] == 1){
                            item += '<img style="right: 0; position: absolute;" src="{{asset('/icons/300px-star.svg.png')}}" width=30px>'
                        }
                        if(checking == 0){
                            item += '<img class="img-item" src="{{ asset('/') }}' + val['thumbnail'] + '">'
                        }else{
                            item += '<img class="img-item" src="{{env('URL_MEDIA')}}' + val['thumbnail'] + '">'
                        }

                        item += '</button>'
                        item += '</a>'
                    })
                })

                $('#list-item').append(item);
            }

            function addItemMakeupToList(items, type_id, type_code, type_order, checking){
                var item = '';
                $.each(items, function(key, value){

                    var makeup_items = value['makeup_items']
                    makeup_items = JSON.parse(makeup_items)
                    var n = 0;
                    $.each(makeup_items[0], function(ke, val){
                        n++

                        if(checking == 0){
                            item += '<a class="item type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' children_type_id=' + ke  + ' type_order=' + type_order + ' type_code=' + type_code
                        }else{
                            item += '<a checking=1 class="item type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' children_type_id=' + ke  + ' type_order=' + type_order + ' type_code=' + type_code
                        }
                        item += ' image=' + encodeURIComponent(val) + ' image_pos_x=' + value['image_pos_x'] + ' image_pos_y=' + value['image_pos_y']
                            + '>'
                        if(checking == 0){
                            item += '<button  type="button" style="position: relative;">'
                        }else{
                            item += '<button class="item-checking" type="button" style="position: relative;">'
                        }

                        item += '<span>' + n + '</span>'
                        if(value['vip'] == 1){
                            item += '<img style="right: 0; position: absolute;" src="{{asset('/icons/300px-star.svg.png')}}" width=30px>'
                        }
                        if(checking == 0){
                            item += '<img class="img-item" src="{{ asset('/') }}' + val + '">'
                        }else{
                            item += '<img class="img-item" src="{{env('URL_MEDIA')}}' + val + '">'
                        }

                        item += '</button>'
                        item += '</a>'
                    })
                })
                $('#list-item').append(item);
            }


            $(document).on('click', '.item', function(){

                var item_id = $(this).attr('item_id')
                //var type_id = parseInt($(this).attr('type_id'))
                var children_type_id = $(this).attr('children_type_id')
                var type_code = $(this).attr('type_code')
                var z_index = parseInt($(this).attr('type_order'))

                var position_code = $(this).attr('position_code')

                if(typeof position_code !== 'undefined'){
                    if(position_code == null){
                        $.ajax({
                            url: '/api-admin/get-position-by-type?type_code=' + type_code,
                            success: function (data) {
                                position_code = data.data;
                            }
                        })
                    }

                }else{
                    position_code = null
                }

                var check_type_appended =  $('img[type_code=' + type_code + ']').length
                var check_childred_type_appended =  $('img[children_type_id=' + children_type_id + ']').length
                var check_position_appended =  $('img[position_code=' + position_code + ']').length


                var check_type = [<?php echo $type_scale_1_4?>];
                var scale = 0.75;
                if(check_type.includes(type_code) === true){
                    scale *= 0.25;
                }
                var checking = $(this).attr('checking')
                switch (type_code) {
                    case 'hair':

                        item_id = item_id + '-' + children_type_id

                        var image = $(this).attr('image')
                        var image_pos_x = $(this).attr('image_pos_x')
                        var image_pos_y = $(this).attr('image_pos_y')
                        var back_image = $(this).attr('back_image')
                        var back_image_pos_x = $(this).attr('back_image_pos_x')
                        var back_image_pos_y = $(this).attr('back_image_pos_y')
                        var mid_image = $(this).attr('mid_image')
                        var mid_image_pos_x = $(this).attr('mid_image_pos_x')
                        var mid_image_pos_y = $(this).attr('mid_image_pos_y')


                        if(checking == 1){
                            var item_checking_id = {
                                'product_id' : item_id,
                                'child_id' : children_type_id,
                                'type_code' : type_code
                            }
                            item_checking_id = JSON.stringify(item_checking_id)

                            item_id = 'checking-' + item_id;
                            var img_image = '<img class="item-model front-layer img_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + image + '" type_code=' + type_code + ' children_type_id=' + children_type_id +  ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' style="z-index:' + z_index + '">'
                            var img_back_image = '<img class="item-model back-layer img_back_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + back_image + '" type_code=' + type_code + ' children_type_id=' + children_type_id + ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' style="z-index: ' + z_index + '">'
                            var img_mid_image = '<img class="item-model mid-layer img_mid_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + mid_image + '" type_code=' + type_code + ' children_type_id=' + children_type_id + ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' style="z-index: ' + z_index + '">'

                        }else{
                            var img_image = '<img class="item-model front-layer img_image_' + item_id + '" src="{{ asset('/') }}' + image + '" type_code=' + type_code + ' children_type_id=' + children_type_id +  ' item_id=' + item_id + ' style="z-index:' + z_index + '">'
                            var img_back_image = '<img class="item-model back-layer img_back_image_' + item_id + '" src="{{ asset('/') }}' + back_image + '" type_code=' + type_code + ' children_type_id=' + children_type_id + ' item_id=' + item_id + ' style="z-index: ' + z_index + '">'
                            var img_mid_image = '<img class="item-model mid-layer img_mid_image_' + item_id + '" src="{{ asset('/') }}' + mid_image + '" type_code=' + type_code + ' children_type_id=' + children_type_id + ' item_id=' + item_id + ' style="z-index: ' + z_index + '">'

                        }


                        if(check_type_appended == 0){
                            if(image != 'null' && image != ''){
                                $('#preview-container').append(img_image) // add new item
                            }

                            if(back_image != 'null' && back_image != ''){
                                $('#preview-container').append(img_back_image)
                            }
                            if(mid_image != 'null' && mid_image != ''){
                                $('#preview-container').append(img_mid_image)
                            }
                        }else{
                            $('img[type_code=' + type_code + ']').remove(); //remove old item
                            if(check_childred_type_appended == 0){
                                if(image != 'null' && image != ''){
                                    $('#preview-container').append(img_image) // add new item
                                }
                                if(back_image != 'null' && back_image != ''){
                                    $('#preview-container').append(img_back_image)
                                }
                                if(mid_image != 'null' && mid_image != ''){
                                    $('#preview-container').append(img_mid_image)
                                }
                            }
                        }
                        // style css x, y

                        image_pos_x *= scale
                        image_pos_y *= scale
                        back_image_pos_x *= scale
                        back_image_pos_y *= scale
                        mid_image_pos_x *= scale
                        mid_image_pos_y *= scale

                        $(".img_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_image_" + item_id).css("left", image_pos_x);
                        $(".img_image_" + item_id).css("top", image_pos_y);
                        $(".img_back_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_back_image_" + item_id).css("left", back_image_pos_x);
                        $(".img_back_image_" + item_id).css("top", back_image_pos_y);
                        $(".img_mid_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_mid_image_" + item_id).css("left", mid_image_pos_x);
                        $(".img_mid_image_" + item_id).css("top", mid_image_pos_y);



                        break;
                    case 'makeup':
                        item_id = item_id + '-' + children_type_id

                        var image = $(this).attr('image')
                        var image_pos_x = $(this).attr('image_pos_x')
                        var image_pos_y = $(this).attr('image_pos_y')

                        if(checking == 1) {
                            var item_checking_id = {
                                'product_id' : item_id,
                                'child_id' : children_type_id,
                                'type_code' : type_code
                            }
                            item_checking_id = JSON.stringify(item_checking_id)
                            item_id = 'checking-' + item_id;
                            var img_image = '<img class="item-model mid-layer img_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + image + '" type_code=' + type_code + ' children_type_id=' + children_type_id +  ' item_id=' + item_id +  ' item_checking_id=' + item_checking_id + ' style="z-index:' + z_index + '">'

                        }else{
                            var img_image = '<img class="item-model mid-layer img_image_' + item_id + '" src="{{ asset('/') }}' + image + '" type_code=' + type_code + ' children_type_id=' + children_type_id +  ' item_id=' + item_id + ' style="z-index:' + z_index + '">'

                        }

                        if(check_type_appended == 0){
                            if(image != 'null' && image != ''){
                                $('#preview-container').append(img_image) // add new item
                            }
                            if(back_image != 'null' && back_image != ''){
                                $('#preview-container').append(img_back_image)
                            }
                            if(mid_image != 'null' && mid_image != ''){
                                $('#preview-container').append(img_mid_image)
                            }
                        }else{
                            $('img[type_code=' + type_code + ']').remove(); //remove old item
                            if(check_childred_type_appended == 0){
                                if(image != 'null' && image != ''){
                                    $('#preview-container').append(img_image) // add new item
                                }
                                if(back_image != 'null' && back_image != ''){
                                    $('#preview-container').append(img_back_image)
                                }
                                if(mid_image != 'null' && mid_image != ''){
                                    $('#preview-container').append(img_mid_image)
                                }
                            }
                        }
                        // style css x, y

                        image_pos_x *= scale
                        image_pos_y *= scale

                        $(".img_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_image_" + item_id).css("left", image_pos_x);
                        $(".img_image_" + item_id).css("top", image_pos_y);


                        break;
                    default:


                        var image = $(this).attr('image')
                        var image_pos_x = $(this).attr('image_pos_x')
                        var image_pos_y = $(this).attr('image_pos_y')
                        var back_image = $(this).attr('back_image')
                        var back_image_pos_x = $(this).attr('back_image_pos_x')
                        var back_image_pos_y = $(this).attr('back_image_pos_y')
                        var mid_image = $(this).attr('mid_image')
                        var mid_image_pos_x = $(this).attr('mid_image_pos_x')
                        var mid_image_pos_y = $(this).attr('mid_image_pos_y')
                        var left_image = $(this).attr('left_image')
                        var left_image_pos_x = $(this).attr('left_image_pos_x')
                        var left_image_pos_y = $(this).attr('left_image_pos_y')
                        var right_image = $(this).attr('right_image')
                        var right_image_pos_x = $(this).attr('right_image_pos_x')
                        var right_image_pos_y = $(this).attr('right_image_pos_y')

                        if(checking == 1){
                            var item_checking_id = {
                                'product_id' : item_id,
                                'type_code' : type_code
                            }
                            item_checking_id = JSON.stringify(item_checking_id)
                            item_id = 'checking-' + item_id;

                            var img_image = '<img class="item-model front-layer img_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + image + '" type_code=' + type_code + ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' position_code=' + position_code + ' style="z-index:' + z_index + '">'
                            var img_back_image = '<img class="item-model back-layer img_back_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + back_image + '" type_code=' + type_code + ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'
                            var img_mid_image = '<img class="item-model mid-layer img_mid_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + mid_image + '" type_code=' + type_code + ' item_id=' + item_id  + ' item_checking_id=' + item_checking_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'
                            var img_left_image = '<img class="item-model left-layer img_left_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + left_image + '" type_code=' + type_code + ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'
                            var img_right_image = '<img class="item-model right-layer img_right_image_' + item_id + '" src="{{env('URL_MEDIA')}}' + right_image + '" type_code=' + type_code + ' item_id=' + item_id + ' item_checking_id=' + item_checking_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'


                        }else{
                            var img_image = '<img class="item-model front-layer img_image_' + item_id + '" src="{{ asset('/') }}' + image + '" type_code=' + type_code + ' item_id=' + item_id + ' position_code=' + position_code + ' style="z-index:' + z_index + '">'
                            var img_back_image = '<img class="item-model back-layer img_back_image_' + item_id + '" src="{{ asset('/') }}' + back_image + '" type_code=' + type_code + ' item_id=' + item_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'
                            var img_mid_image = '<img class="item-model mid-layer img_mid_image_' + item_id + '" src="{{ asset('/') }}' + mid_image + '" type_code=' + type_code + ' item_id=' + item_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'
                            var img_left_image = '<img class="item-model left-layer img_left_image_' + item_id + '" src="{{ asset('/') }}' + left_image + '" type_code=' + type_code + ' item_id=' + item_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'
                            var img_right_image = '<img class="item-model right-layer img_right_image_' + item_id + '" src="{{ asset('/') }}' + right_image + '" type_code=' + type_code + ' item_id=' + item_id + ' position_code=' + position_code + ' style="z-index: ' + z_index + '">'

                        }

                        var check_item_appended =  $('img[item_id=' + item_id + ']').length
                        if(check_position_appended == 0){

                            if(image != 'null' && image != ''){
                                $('#preview-container').append(img_image) // add new item
                            }
                            if(back_image != 'null' && back_image != ''){
                                $('#preview-container').append(img_back_image)
                            }
                            if(mid_image != 'null' && mid_image != ''){
                                $('#preview-container').append(img_mid_image)
                            }
                            if(left_image != 'null' && left_image != ''){
                                $('#preview-container').append(img_left_image)
                            }
                            if(right_image != 'null' && right_image != ''){
                                $('#preview-container').append(img_right_image)
                            }

                        }else{
                            $('img[position_code=' + position_code + ']').remove(); //remove old item

                            if(check_item_appended == 0){
                                if(image != 'null' && image != ''){
                                    $('#preview-container').append(img_image) // add new item
                                }
                                if(back_image != 'null' && back_image != ''){
                                    $('#preview-container').append(img_back_image)
                                }
                                if(mid_image != 'null' && mid_image != ''){
                                    $('#preview-container').append(img_mid_image)
                                }
                                if(left_image != 'null' && left_image != ''){
                                    $('#preview-container').append(img_left_image)
                                }
                                if(right_image != 'null' && right_image != ''){
                                    $('#preview-container').append(img_right_image)
                                }

                            }
                        }

                        // style css x, y

                        image_pos_x *= scale
                        image_pos_y *= scale
                        back_image_pos_x *= scale
                        back_image_pos_y *= scale
                        mid_image_pos_x *= scale
                        mid_image_pos_y *= scale
                        left_image_pos_x *= scale
                        left_image_pos_y *= scale
                        right_image_pos_x *= scale
                        right_image_pos_y *= scale

                        $(".img_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_image_" + item_id).css("left", image_pos_x);
                        $(".img_image_" + item_id).css("top", image_pos_y);
                        $(".img_back_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_back_image_" + item_id).css("left", back_image_pos_x);
                        $(".img_back_image_" + item_id).css("top", back_image_pos_y);
                        $(".img_mid_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_mid_image_" + item_id).css("left", mid_image_pos_x);
                        $(".img_mid_image_" + item_id).css("top", mid_image_pos_y);
                        $(".img_left_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_left_image_" + item_id).css("left", left_image_pos_x);
                        $(".img_left_image_" + item_id).css("top", left_image_pos_y);
                        $(".img_right_image_" + item_id).css("transform", `scale(${scale}) translate(-50%, -50%)`);
                        $(".img_right_image_" + item_id).css("left", right_image_pos_x);
                        $(".img_right_image_" + item_id).css("top", right_image_pos_y);


                }

                groupLayer()

                //push to input save database
                var arr_item = []
                var arr_checking_item = []
                $("img.item-model").each(function() {
                    item_id = $(this).attr("item_id");
                    item_checking_id = $(this).attr("item_checking_id");
                    if(typeof item_checking_id !== 'undefined'){
                        product_find = JSON.parse(item_checking_id)
                        if(arr_checking_item.length > 0){
                            const check = arr_checking_item.find(product => product.product_id === product_find.product_id && product.type_code === product_find.type_code);
                            if (!check) {
                                arr_checking_item.push(product_find)
                            }
                        }else{
                            arr_checking_item.push(product_find)
                        }
                    }else{
                        if(arr_item.includes(item_id) === false){
                            arr_item.push(item_id)
                        }
                    }

                });

                $('input[name=item_id]').val(arr_item);
                $('input[name=item_checking_id]').val(JSON.stringify(arr_checking_item));

            })

        </script>

        <script>
            function groupLayer(){
                var parent_front_layer = document.querySelector('.parent-front-layer');
                var parent_left_layer = document.querySelector('.parent-left-layer');
                var parent_mid_layer = document.querySelector('.parent-mid-layer');
                var parent_right_layer = document.querySelector('.parent-right-layer');
                var parent_back_layer = document.querySelector('.parent-back-layer');

                var front_layers = document.querySelectorAll('.front-layer');
                var left_layers = document.querySelectorAll('.left-layer');
                var mid_layers = document.querySelectorAll('.mid-layer');
                var right_layers = document.querySelectorAll('.right-layer');
                var back_layers = document.querySelectorAll('.back-layer');


                front_layers.forEach(function (front_layer) {
                    parent_front_layer.appendChild(front_layer);
                });
                left_layers.forEach(function (left_layer) {
                    parent_left_layer.appendChild(left_layer);
                });
                mid_layers.forEach(function (mid_layer) {
                    parent_mid_layer.appendChild(mid_layer);
                });
                right_layers.forEach(function (right_layer) {
                    parent_right_layer.appendChild(right_layer);
                });
                back_layers.forEach(function (back_layer) {
                    parent_back_layer.appendChild(back_layer);
                });
            }
        </script>


        <script>

            $('.submit-form').on('click', function() {
                var value_action = $(this).data('value');
                $('input[name=save_action]').val(value_action)
                var topic_name = $('select[name="topic_id"] option:selected').text()
                if(topic_name){
                    if (confirm('Bạn chắc chắn muốn lưu vào topic ' + topic_name + '?')){
                        $('form').submit();
                    }
                }

            })

        </script>

        <script>
            $('select[name="model"]').on('change', function(){
                var image_skin = <?php echo json_encode($image_skin); ?>;
                var skin_id = $(this).val();

                var left_hand_image = "{{ $url }}" + image_skin[skin_id]['left_hand_image'];
                var right_hand_image = "{{ $url }}" + image_skin[skin_id]['right_hand_image'];
                var body_image = "{{ $url }}" + image_skin[skin_id]['body_image'];

                $('.full-model.left-layer').attr('src', left_hand_image);
                $('.full-model.right-layer').attr('src', right_hand_image);
                $('.full-model.mid-layer').attr('src', body_image);
            });
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

