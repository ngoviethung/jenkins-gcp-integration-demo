<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;


use App\Models\Style;
use App\Models\Topic;
use Exception;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Type;
use App\Models\GroupLevelItem;
use DB;
use App\Models\ItemStyle;
use App\Models\ItemTopic;
use Log;

class ItemController
{

    public function createItem(Request $request)
    {
        try {

            $name = $request->name;
            $image = $request->image;
            $thumb_top = $request->thumb_top;
            $thumb_bottom = $request->thumb_bottom;
            $type = $request->type;
            $topics = $request->topic;
            $styles = $request->style;
            $price = $request->price;
            $brand = $request->brand;
            $group_level_item = $request->group_level_item;
            $pos_x = $request->pos_x;
            $pos_y = $request->pos_y;


            if(!$name or !$image or !$pos_x or !$pos_y){
                throw new Exception('Missing paramter.', 66);
            }
            $data = [];
            $data['name'] = $name;
            $data['pos_x'] = $pos_x;
            $data['pos_y'] = $pos_y;
            //$data['price'] = $price;
            $data ['brand'] = $brand;



            $type = Type::where('name', $type)->get()->first();
            $type_id = 0;
            if($type){
                $type_id = $type->id;
                $data['type_id'] = $type_id;
            }

            $data['image'] = $this->saveImage($image, $type_id);
            $data['thumb_top'] = $this->saveThumb($thumb_top);
            $data['thumb_bottom'] = $this->saveThumb($thumb_bottom);

            /*
            $group_level_item = GroupLevelItem::where('name', $group_level_item)->get()->first();

            if($group_level_item){
                $data['group_level_item_id'] = $group_level_item->id;
            }
            */

            $where = [
                'image' => $data['image']
            ];

            $id = Item::firstOrCreate($where, $data)->id;

            //$this->setStyles($id, $styles);
            $this->setStyleDefault($id, 1);
            $this->setTopics($id, $topics);
            $data['id'] = $id;
            return response()->json(['code' => 200, 'data' => $data]);

        } catch (Exception $exception) {
            return [
                'error' => $exception->getMessage()
            ];
        }
    }
    public function updateItem(Request $request)
    {
        try {

            $id = $request->id;
            $image = $request->image;
            $thumb_top = $request->thumb_top;
            $thumb_bottom = $request->thumb_bottom;
            $pos_x = $request->pos_x;
            $pos_y = $request->pos_y;


            if(!$id or !$image or !$pos_x or !$pos_y){
                throw new Exception('Missing paramter.', 66);
            }

            $item = Item::where('id', $id)->get(['type_id'])->first();
            if(!$item){
                return response()->json(['code' => 200, 'data' => []]);
            }
            $type_id = $item->type_id;

            $data = [];
            $data['pos_x'] = $pos_x;
            $data['pos_y'] = $pos_y;
            $data['image'] = $this->saveImage($image, $type_id);
            $data['thumb_top'] = $this->saveThumb($thumb_top);
            $data['thumb_bottom'] = $this->saveThumb($thumb_bottom);

            Item::where('id', $id)->update($data);

            $data['id'] = $id;

            return response()->json(['code' => 200, 'data' => $data]);

        } catch (Exception $exception) {
            return [
                'error' => $exception->getMessage()
            ];
        }
    }
    private function setStyleDefault($item_id, $style_id){
        ItemStyle::create(['item_id' => $item_id, 'style_id' => $style_id]);
    }

    private function setStyles($item_id, $styles){
        $styles = json_decode($styles);
        if($styles){
            foreach ($styles as $style){
                $style_id = $this->getStyleId($style);
                if($style_id){
                    ItemStyle::firstOrCreate(['item_id' => $item_id, 'style_id' => $style_id]);
                }
            }
        }
    }
    private function setTopics($item_id, $topics){

        if($topics){
            foreach ($topics as $topic){
                $topic_id = $this->getTopicId($topic);
                if($topic_id){
                    ItemTopic::firstOrCreate(['item_id' => $item_id, 'topic_id' => $topic_id]);
                }
            }
        }
    }

    private function getStyleId($style){
        $style = Style::where('name', $style)->get()->first();
        if($style){
            return $style->id;
        }
        return 0;
    }

    private function getTopicId($topic){
        $topic = Topic::where('name', $topic)->get()->first();
        if($topic){
            return $topic->id;
        }
        return 0;
    }

    public function createImage(Request $request){//read image from url and write
        try {

            $url_media = env('URL_MEDIA');
            $images = $request->images;
            if(!$images){
                throw new Exception('Missing paramter.', 66);
            }

            if(!is_array($images)){
                throw new Exception('Images require an array.', 66);
            }

            foreach ($images as $image){
                $link_image = $url_media.'/'.$image;
                if(!file_exists($image)){
                    file_put_contents($image, file_get_contents($link_image));
                }
            }

            return response()->json(['code' => 200, 'data' => 'success']);

        } catch (Exception $exception) {
            return [
                'error' => $exception->getMessage()
            ];
        }

    }
    private function saveImage($link_image, $type_id){

        if($link_image != ''){
            $filename = explode('uploads/image/', $link_image);
            if(isset($filename[1])){
                $filename = $filename[1];
            }else{
                return '';
            }
            if($type_id){
                $path = "uploads/item/type_$type_id/";
                if (!file_exists($path)) {
                   mkdir($path, 0777, true);
                }
            }else{
                $path = "uploads/item/other/";
            }

            file_put_contents($path.$filename, file_get_contents($link_image));
            $image = $path.$filename;

            return $image;

        }else{
            return '';
        }

    }

    private function saveThumb($link_image){
        if($link_image != ''){
            $filename = explode('uploads/image/', $link_image);
            if(isset($filename[1])){
                $filename = $filename[1];
            }else{
                return '';
            }
            $path = 'uploads/thumbnail/';
            file_put_contents($path.$filename, file_get_contents($link_image));
            $image = $path.$filename;

            return $image;

        }else{
            return '';
        }

    }


}
