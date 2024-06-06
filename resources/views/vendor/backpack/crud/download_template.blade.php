
@php

    use App\Models\Template;
    use App\Models\Item;

    $template_id = $_GET['template_id'];
    $template = Template::find($template_id);
    $arr_item_id = $template->item_id;
    $arr_item_id = explode(',', $arr_item_id);
    $items = Item::with('type')->whereIn('id', $arr_item_id)->get();

    $items_default = [
        'lens_default' => ['item_default/lens.png', 20, 179.375 * 8, 156 * 8],
        'eyebrows_default' => ['item_default/Default_eyebrowstrang.png', 17, 179.375 * 8, 149.1875 * 8],
        'lips_default' => ['item_default/Default_moitrang.png', 21, 179.5 * 8, 176.25 * 8],
        'dress_set_default' => ['item_default/Default_clothestrang.png', 1,  185.25 * 2, 272.25 * 2],
        'hair_default' => ['item_default/default_toc.png', 19,  180.25 * 8, 135 * 8]
    ];
@endphp
<style>
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
<div style="position: fixed; width: 100%; z-index: 999" >
    <div class="loader" style="margin: auto"></div>
</div>

<div class="item" >

        <div class="preview-container" id="preview-container">

            <img class="full-model" src="/images/Model_trang1x.png">

            @foreach($items_default as $key => $item)
                @if(in_array($key, $arr_item_id))
                    <img class="item-model" src="{{ asset('/') }}{!! $item[0] !!}"
                         style="
                         @if(in_array($item[1], [5, 15, 19, 20, 16, 17, 18, 21, 22, 24 ]))
                             transform: scale(0.25) translate(-50%, -50%);
                             left: {{ $item[2] / 4 }}px;
                             top: {{ ($item[3]  / 4 )}}px;
                             transform-origin: left top;

                         @else
                             transform: translate(-50%, -50%);
                             left: {{ $item[2] }}px;
                             top: {{ ($item[3] )}}px;;

                         @endif
                             "
                    >
                @endif
            @endforeach



            @foreach($items as $item)
                @php
                    $pos_x = $item->pos_x;
                    $pos_y = $item->pos_y;
                    $z_index = $item->type->order;
                    if(in_array($item->type_id, [5, 15, 19, 20, 16, 17, 18, 21, 22, 24 ])) {
                       $pos_x = $pos_x / 4;
                       $pos_y = $pos_y / 4;
                   }
                @endphp
                <img class="item-model" src="{{ asset('/') }}{!! $item->image !!}"
                     style="
                     @if(in_array($item->type_id, [5, 15, 19, 20, 16, 17, 18, 21, 22, 24 ]))
                         transform: scale(0.25) translate(-50%, -50%);
                         left: {{ $pos_x }}px;
                         top: {{ ($pos_y )}}px;
                         transform-origin: left top;
                         z-index: {{$z_index}};

                     @else
                         transform: translate(-50%, -50%);
                         left: {{ $pos_x }}px;
                         top: {{ ($pos_y )}}px;
                         z-index: {{$z_index}};

                     @endif
                         "
                >
            @endforeach
        </div>

</div>

    <style>
        body{
            margin: 0;
        }
        .item{
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .preview-container {
            /*background: white;*/
            margin: auto;
            width: 750px;
            height: 1334px;
            /*border: thin black solid;*/
            background: url('{!! asset($template->background) !!}');
            text-align: center;
            position: relative;
            background-size: 100% 1334px;
            background-repeat: no-repeat;
        }
        .item h5{
            text-align: center;
        }
        .full-model {
            position: absolute;
            top: 236.5px;
            left: 254px;
        }
        .item-model {
            position: absolute;

        }

    </style>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{asset('canvas/html2canvas.js')}}"></script>
    <script>

        $('.loader').show();
        window.scrollTo(0, 0);
        html2canvas(document.getElementById('preview-container'), {
                scale: 1,
                scrollX: 0,
                scrollY: -window.scrollY,
                windowWidth: document.documentElement.offsetWidth,
                windowHeight: document.documentElement.offsetHeight,
                width: 750,

            }).then(function (canvas) {
            // Get the image data as JPEG and 0.9 quality (0.0 - 1.0)
            //console.log(canvas.toDataURL("image/jpeg", 1.0));
            $.ajax({
                type: "POST",
                data: {
                    template_id: {!! $template->id !!},
                    image: canvas.toDataURL("image/png", 1.0),
                    name: '{!! $template->name !!}'
                },
                url: '/api/conver_image_base64',
                success: function (data) {
                    var full_path_file = data.data.file;
                    var filename = data.data.filename;
                    if(full_path_file){
                        download(filename, full_path_file)
                        //delete file
                        /*
                        $.ajax({
                            type: "POST",
                            data: {
                                filename: filename
                            },
                            url: '/api/template/delete_file',
                            success: function (data) {

                            }
                        })

                         */

                    }else{
                        alert('Create file download faild.')
                        $('.loader').hide();
                    }
                },
                error: function(data) {
                    alert('Create file download faild.')
                    $('.loader').hide();
                }

            })

        }).catch( error =>  console.log(error) );

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
        }

    </script>


