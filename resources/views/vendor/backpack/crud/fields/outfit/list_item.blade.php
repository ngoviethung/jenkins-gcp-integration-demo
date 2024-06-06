@php
    use App\Models\Type;
    $types = Type::all();

    $type_scale_1_4 = Type::where('scale', 0.25)->get(['id'])->pluck('id')->toArray();
@endphp

<!-- select2 from array -->
@include('crud::fields.inc.wrapper_start')
    <label>Type</label>
    <div class="tab">
        @foreach($types as $type)
            <button type_id={!! $type->id !!} type_name={{ str_replace(' ', '_SEPERATE_', str_replace('>', '_dongngoacnhon_', $type->name))  }}  type_order={!! $type->order !!} type="button" class="btn btn-info tablinks" onclick="openCity(event, {!! $type->id !!}, '{!! $type->name !!}', {!! $type->order !!})">{!! $type->name !!}</button>
        @endforeach
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
            .item{
                text-align: center;
                display: inline-block;
                /*width: 25%;*/
                margin: 5px 0;
            }
            .item button{
                display: block;
                margin: auto;
            }
            .type-20, .type-5, .type-6, .type-16, .type-17, .type-18, .type-21, .type-22, .type-25, .type-27{
                width: 50%;
                margin: 10px 0;

            }

            #list-item{
                position: relative;
                overflow-y: auto;
                height: 950px;
            }
            .img-item{
                width: 100%;
            }
            .item button{
                border: 1px solid darkseagreen;
                background-color: white;
                outline-color: yellowgreen;
            }
            .item-checking{
                background-color: cornsilk !important;
            }

            .item-model {
                position: absolute;
                transform-origin: left top;

            }
        </style>
        <style>
        /* Style the tab */
        .tab {
        overflow: hidden;

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
        </style>

    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include tabs js-->
        <script>
            function openCity(evt, type_id, type_name, type_order) {
                var i, tabcontent, tablinks;

                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                evt.currentTarget.className += " active";

                var topic_id = $('select[name="topic_id"]').val()
                var topic_name = $('select[name="topic_id"] option:selected').text()

                var count = 0;
                $('#list-item').html('');
                if(topic_id){
                    var item = '';
                    $.ajax({
                        url: '/api-admin/get-item-outfit?topic_id=' + topic_id + '&type_id=' + type_id,
                        success: function (data) {
                            var items = data.data;
                            count = items.length
                            $.each(items, function(key, value){
                                var n = key + 1;
                                item += '<div style="display:inline-block; text-align: center; margin: 5px 0; width: 25%;">'
                                item += '<a class="item type-' + value['type_id'] +'" item_id=' + value['id'] + ' type_id=' + value['type_id'] + ' type_order=' + value['type'].order  + ' pos_x=' + value['pos_x'] + ' pos_y=' + value['pos_y'] + ' image=' + encodeURIComponent(value['image']) + ' thumb_top=' + encodeURIComponent(value['thumb_top']) + '>'
                                item += '<button type="button">'
                                item += '<span>' + n + '</span>'
                                item += '<img class="img-item" src="{{ asset('/') }}' + value['thumb_bottom'] + '">'
                                item += '</button>'
                                item += '<span>Đẹp: ' + value['style_score'] + '</span>'
                                item += '<br>'
                                item += '<span>Price: ' + value['price_currency'] + '</span>'
                                item += '</a>'
                                item += '<br>'
                                item += '<a href="/admin/item/' + value['id'] + '/edit" target="_blank">Edit</a>'
                                item += '</div>'
                            })
                            $('#list-item').append(item);
                        }
                    })

                    //call ajax to get item checking

                    /*
                    var item_checking = '';
                    $.ajax({
                        url: '{{env('URL_MEDIA')}}' + 'api/get-item-checking',
                        type: 'POST',
                        data: {
                            app_id: {{env('APP_ID')}},
                            topic_name: topic_name,
                            type_name: type_name
                        },
                        //CrossDomain:true,
                        success: function (data) {
                            var items = data.data;
                            $.each(items, function(key, value){
                                var m = count + key + 1;
                                item_checking += '<a checking=1 class="item type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' type_order=' + type_order  + ' pos_x=' + value['pos_x'] + ' pos_y=' + value['pos_y'] + ' image=' + encodeURIComponent(value['image']) + ' thumb_top=' + encodeURIComponent(value['thumb_top']) + '>'
                                item_checking += '<button class="item-checking" type="button">'
                                item_checking += '<span>' + m + '</span>'
                                item_checking += '<img class="img-item" src="{{env('URL_MEDIA')}}' + value['thumb_bottom'] + '">'
                                item_checking += '</button>'
                                item_checking += '</a>'
                            })
                            $('#list-item').append(item_checking);
                        }
                    })
                    */


                }

            }
        </script>
        <!-- end include tabs js-->

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

        <script>

            /*
            $('select[name="type_id"]').on('change', function(){
                var type_id = $(this).val()
                var topic_id = $('select[name="topic_id"]').val()
                if(topic_id){
                    $.ajax({
                        url: '/api-admin/get-item-outfit?topic_id=' + topic_id + '&type_id=' + type_id,
                        success: function (data) {

                            var items = data.data;
                            if(items.length == 0){
                                var item = 'No Data !!!';
                            }else{
                                var item = '';
                            }

                            $.each(items, function(key, value){
                                item += '<a class="item type-' + value['type_id'] +'" item_id=' + value['id'] + ' type_id=' + value['type_id'] + ' type_order=' + value['type'].order + ' pos_x=' + value['pos_x'] + ' pos_y=' + value['pos_y'] + ' image=' + encodeURIComponent(value['image']) + '>'
                                item += '<button type="button">'
                                item += '<img class="img-item" src="{{ asset('/') }}' + value['thumb_bottom'] + '">'
                                item += '</button>'
                                item += '</a>'
                            })
                            $('#list-item').html(item);
                        }
                    })
                }

            })
            */
            var arr_item = []

            $('select[name="topic_id"]').on('change', function(){
                //reset template to defautl
                /*
                var default_list_item = '<img class="item-model" src="/item_default/lens.png" type_id="20" item_id="lens_default" style=" transform: scale(0.1875) translate(-50%, -50%); top: 234px; left: 269.0625px;">\n' +
                    '            <img class="item-model" src="/item_default/Default_eyebrowstrang.png" type_id="17" item_id="eyebrows_default" style="transform: scale(0.1875) translate(-50%, -50%); top: 223.78125px; left: 269.0625px;">\n' +
                    '            <img class="item-model" src="/item_default/Default_moitrang.png" type_id="21" item_id="lips_default" style="transform: scale(0.1875) translate(-50%, -50%); top: 264.375px; left: 269.25px;">\n' +
                    '            <img class="item-model" src="/item_default/Default_clothestrang.png" type_id="1" item_id="dress_set_default" style="transform: scale(0.75) translate(-50%, -50%); top: 408.375px; left: 277.875px;">\n' +
                    '            <img class="item-model" src="/item_default/default_toc.png"  type_id="19" item_id="hair_default" style="transform: scale(0.1875) translate(-50%, -50%); top: 202.5px; left: 270.375px;">\n' +
                    '\n'
                $('.item-model').remove();
                $('#preview-container').append(default_list_item)

                */
                var count = 0;
                arr_item = []
                var topic_id = $(this).val()
                var topic_name = $(this).find('option:selected').text()
                //var type_id = $('select[name="type_id"]').val()
                var type_id = $('.tablinks.active').attr('type_id');
                var type_name = $('.tablinks.active').attr('type_name');
                var type_order = $('.tablinks.active').attr('type_order');

                $('#list-item').html('');
                if(type_id){
                    var item = '';
                    $.ajax({
                        url: '/api-admin/get-item-outfit?topic_id=' + topic_id + '&type_id=' + type_id,
                        success: function (data) {

                            var items = data.data;
                            count = items.length
                            $.each(items, function(key, value){
                                var n = key + 1;
                                item += '<div style="display:inline-block; text-align: center; margin: 5px 0; width: 25%;">'
                                item += '<a class="item type-' + value['type_id'] +'" item_id=' + value['id'] + ' type_id=' + value['type_id'] + ' type_order=' + value['type'].order + ' pos_x=' + value['pos_x'] + ' pos_y=' + value['pos_y'] + ' image=' + encodeURIComponent(value['image']) + ' thumb_top=' + encodeURIComponent(value['thumb_top']) + '>'
                                item += '<button type="button">'
                                item += '<span>' + n + '</span>'
                                item += '<img class="img-item" src="{{ asset('/') }}' + value['thumb_bottom'] + '">'
                                item += '</button>'
                                item += '<span>Đẹp: ' + value['style_score'] + '</span>'
                                item += '<br>'
                                item += '<span>Price: ' + value['price_currency'] + '</span>'
                                item += '</a>'
                                item += '<br>'
                                item += '<a href="/admin/item/' + value['id'] + '/edit" target="_blank">Edit</a>'
                                item += '</div>'
                            })
                            $('#list-item').append(item);
                        }
                    })

                    //call ajax to get item checking


                    /*
                    var item_checking = '';
                    $.ajax({
                        url: '{{env('URL_MEDIA')}}' + 'api/get-item-checking',
                        type: 'POST',
                        data: {
                            app_id: {{env('APP_ID')}},
                            topic_name: topic_name,
                            type_name: type_name
                        },
                        //CrossDomain:true,
                        success: function (data) {
                            var items = data.data;
                            $.each(items, function(key, value){
                                var m = count + key + 1;
                                item_checking += '<a checking=1 class="item type-' + type_id +'" item_id=' + value['id'] + ' type_id=' + type_id + ' type_order=' + type_order  + ' pos_x=' + value['pos_x'] + ' pos_y=' + value['pos_y'] + ' image=' + encodeURIComponent(value['image']) + ' thumb_top=' + encodeURIComponent(value['thumb_top']) + '>'
                                item_checking += '<button class="item-checking" type="button">'
                                item_checking += '<span>' + m + '</span>'
                                item_checking += '<img class="img-item" src="{{env('URL_MEDIA')}}' + value['thumb_bottom'] + '">'
                                item_checking += '</button>'
                                item_checking += '</a>'
                            })
                            $('#list-item').append(item_checking);
                        }
                    })
                    */



                }

            })


            $(document).on('click', '.item', function(){

                var item_id = $(this).attr('item_id')
                var type_id = parseInt($(this).attr('type_id'))
                var z_index = parseInt($(this).attr('type_order'))
                var pos_x = $(this).attr('pos_x')
                var pos_y = $(this).attr('pos_y')
                var image = $(this).attr('image')
                var thumb_top = $(this).attr('thumb_top')

                var checking = $(this).attr('checking')
                if(checking == 1){

                    item_id = 'checking-' + item_id;
                    var item_model = '<img checking=1 class="item-model ' + item_id + '" src="{{env('URL_MEDIA')}}' + image + '" type_id=' + type_id + ' item_id=' + item_id + ' item_value=' + type_id + '_SEPERATE_' + pos_x + '_SEPERATE_' + pos_y + '_SEPERATE_' + z_index + '_SEPERATE_' + image +  '_SEPERATE_' + thumb_top + '_SEPERATE_' + item_id + ' style="z-index:' + z_index + '">'
                    var thumb_top_item_model = '<img checking=1 class="item-model ' + item_id + '" src="{{env('URL_MEDIA')}}' + thumb_top + '" type_id=' + type_id + ' item_id=' + item_id + ' style="z-index: -' + z_index + '">'
                }else{
                    var item_model = '<img class="item-model ' + item_id + '" src="{{ asset('/') }}' + image + '" type_id=' + type_id + ' item_id=' + item_id + ' style="z-index:' + z_index + '">'
                    var thumb_top_item_model = '<img class="item-model ' + item_id + '" src="{{ asset('/') }}' + thumb_top + '" type_id=' + type_id + ' item_id=' + item_id + ' style="z-index: -' + z_index + '">'
                }


                var check_type_appended =  $('img[type_id=' + type_id + ']').length
                var check_item_appended =  $('img[item_id=' + item_id + ']').length
                //var check_thumb_top = [1, 2, 3, 19]


                if(check_type_appended == 0){

                    if([2,3].includes(type_id) === true){ //dressset, top , bottom are conflict
                        $('img[type_id=1]').remove();
                    }
                    if([1].includes(type_id) === true){ //dressset, top , bottom are conflict
                        $('img[type_id=2]').remove();
                        $('img[type_id=3]').remove();
                    }

                    $('#preview-container').append(item_model)
                    if(thumb_top != 'null' && thumb_top != ''){
                        $('#preview-container').append(thumb_top_item_model)
                    }


                }else{
                    $('img[type_id=' + type_id + ']').remove(); //remove old item
                    if(check_item_appended == 0){
                        $('#preview-container').append(item_model) // add new item
                        if(thumb_top != 'null' && thumb_top != ''){
                            $('#preview-container').append(thumb_top_item_model)
                        }
                    }
                }


                // style css x, y
                var check_type = [<?php echo implode(',', $type_scale_1_4)?>];

                if(check_type.includes(type_id) === true){
                    pos_x = (pos_x / 4)/ (4/3)
                    pos_y = (pos_y / 4) / (4/3)
                    $("." + item_id).css("transform", "scale(0.1875) translate(-50%, -50%)")
                }else{
                    pos_x = pos_x / (4/3)
                    pos_y = pos_y / (4/3)
                    $("." + item_id).css("transform", "scale(0.75) translate(-50%, -50%)")
                }
                $("." + item_id).css("top", pos_y);
                $("." + item_id).css("left", pos_x);


                //push to input save database
                arr_item = []
                arr_checking_item = []
                $("img.item-model").each(function() {
                    var item_id = $(this).attr("item_id");
                    var item_value = $(this).attr("item_value");
                    var checking = $(this).attr("checking");
                    if(checking){
                        if(item_value){
                            arr_checking_item.push(item_value)
                        }
                    }else{
                        if(arr_item.includes(item_id) === false){
                            arr_item.push(item_id)
                        }

                    }

                });
                console.log(arr_item);
                console.log(arr_checking_item);


                $('input[name=item_id]').val(arr_item);
                $('input[name=item_checking_id]').val(arr_checking_item);

            })

        </script>

        <script>
            $('.submit-form').on('click', function() {
                var value_action = $(this).data('value');
                $('input[name=save_action]').val(value_action)
                var topic_name = $('select[name="topic_id"] option:selected').text()
                if (confirm('Bạn chắc chắn muốn lưu vào topic ' + topic_name + '?')){
                    $('form').submit();
                }
            })

        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

