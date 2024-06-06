<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use Exception;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Skin;
use URL;
use \Treinetic\ImageArtist\lib\Image;
use DB;
use App\Models\UserChallenge;
use Log;


class MergeImageController extends AppBaseController
{

    private $width = 714;
    private $height = 1333;
    private $data_merge_by_item = [];

    private $rename;

    private $public_path;

    private $arr_filename = [];


    public function mergeAndZipImage($input_data = false){

        //try{
            $this->rename = date("Y-m-d-H:i:s").'-';
            $this->public_path = public_path().'/';

            if($input_data === false){
                $input_data = '{
                  "user_id": "664eb3cf4867a2f380002b72",
                  "challenge_id": "6646cf52eb493debb50e77e2",
                  "isTuck": false,
                  "skin_id": 9,
                  "makeup": 0,
                  "list_item": [478,4887],
                  "hair":{
                    "item_id": 0,
                    "color_id": 0
                  }
                }';
            }

            $input_data = json_decode($input_data);
            $is_tuck = $input_data->isTuck;
            $skin_id = $input_data->skin_id;
            $arr_item_id = $input_data->list_item;
            $hair = $input_data->hair;
            $makeup_id = $input_data->makeup;

            $where = [
                'user_id' => $input_data->user_id,
                'challenge_id' => $input_data->challenge_id,
            ];
            $data_create = [
                'input_data' => $input_data,
            ];
            $record = UserChallenge::where($where)->update($data_create);
            $record = UserChallenge::where($where)->first();
            $user_challenge_id = $record->id;

            if($record->image){
                //return 1;
            }
            $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['code'])->pluck('code')->toArray();

            $data_merge_front_layer = [];
            $data_merge_left_layer = [];
            $data_merge_mid_layer = [];
            $data_merge_right_layer = [];
            $data_merge_back_layer = [];


            //Skin Items
            $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();
            $skin = Skin::find($skin_id);


            $mid_image = $skin->body_image;
            $left_image = $skin->left_hand_image;
            $right_image = $skin->right_hand_image;

            $order = 0;
            $position = null;
            $type = null;
            $item_id = 0;
            $scale =  100; //thu vien nay dung sclae theo %

            //cac toa do cua model ben client gui la da scale ve 0.25 roi nen ko can scale lai nua
            if(is_file($this->public_path.$left_image) && file_exists($this->public_path.$left_image)){
                $pos_x = $model->left_hand_image_pos_x;
                $pos_y = $model->left_hand_image_pos_y;
                $data_merge_left_layer[] = $this->groupDataItem($item_id, $left_image, $pos_x, $pos_y, $order, $scale, $type, $position);
            }

            if(is_file($this->public_path.$mid_image) && file_exists($this->public_path.$mid_image)){
                $pos_x = $model->body_image_pos_x ;
                $pos_y = $model->body_image_pos_y;
                $data_merge_mid_layer[] = $this->groupDataItem($item_id, $mid_image, $pos_x, $pos_y, $order, $scale, $type, $position);
            }
            if(is_file($this->public_path.$right_image) && file_exists($this->public_path.$right_image)){
                $pos_x = $model->right_hand_image_pos_x;
                $pos_y = $model->right_hand_image_pos_y;
                $data_merge_right_layer[] = $this->groupDataItem($item_id, $right_image, $pos_x, $pos_y, $order, $scale, $type, $position);
            }

            //Makeup Items
            $item_id = $makeup_id;

            if($item_id == 0){
                $k = $skin_id - 4;
                
                $type = 'makeup';
                $position = 'makeup';
                $order = 100;
                $scale = 100;
                if(in_array($type, $type_scale_1_4)) {
                    $scale = 25; //Thu vien nay no tinh theo %
                }
                $pos_x = 350 * ($scale/100);
                $pos_y = 305.5 * ($scale/100);
                $front_image = 'item_default_merge_image/face_' . $k . '.png';

                $data_merge_front_layer[] = $this->groupDataItem($item_id, $front_image, $pos_x, $pos_y, $order, $scale, $type, $position);

            }else{
                $item = Item::join('types', 'items.type_id', 'types.id')
                ->join('positions', 'types.position_id', 'positions.id')
                ->where('items.id', $item_id)
                ->orderBy('types.order', 'ASC')
                ->get(['items.*', 'types.order', 'positions.code'])->first();


                $type = $item->type->code;
                if($type == 'makeup'){
                    $skin_code = $skin->code;
                    $skin_ref = DB::connection('mysql2')->table('skins')->where('code', $skin_code)->first(); //find skin id ben ref, vi skin ben game luu giong ben ref
                    $skin_id = $skin_ref->id;

                    $position = $item->code;
                    $order = $item->order;

                    $makeup_items = $item->makeup_items;
                    if($makeup_items){
                        $makeup_items = json_decode($makeup_items);
                    }

                    if(isset($makeup_items[0])){
                        foreach ($makeup_items[0] as $k => $front_image){
                            $id = 'image_skin_'.$skin_id;
                            if($k != $id){
                                continue;
                            }
                            $front_image = $front_image;

                            if(is_file($this->public_path.$front_image) && file_exists($this->public_path.$front_image) && $k == 0){
                                $scale = 100;
                                if(in_array($type, $type_scale_1_4)) {
                                    $scale = 25; //Thu vien nay no tinh theo %
                                }
                                $pos_x = $item->image_pos_x * ($scale/100);
                                $pos_y = $item->image_pos_y * ($scale/100);
                                $data_merge_front_layer[] = $this->groupDataItem($item_id, $front_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                            }

                        }
                    }
                }
            }
            

            //Hair Items
            $item_id = $hair->item_id;
            if($item_id == 0){ //gui len default

                $type = 'hair';
                $mid_image = 'item_default_merge_image/default_hair.png';
                $scale = 100;
                if(in_array($type, $type_scale_1_4)) {
                    $scale = 25; //Thu vien nay no tinh theo %
                }
                $pos_x = 350.5 * ($scale/100);
                $pos_y = 275 * ($scale/100);
                $position = 'hair';
                $order = 2300;
                $data_merge_front_layer[] = $this->groupDataItem($item_id, $mid_image, $pos_x, $pos_y, $order, $scale, $type, $position);


            }else{
                $item = Item::join('types', 'items.type_id', 'types.id')
                ->join('positions', 'types.position_id', 'positions.id')
                ->where('items.id', $item_id)
                ->orderBy('types.order', 'ASC')
                ->get(['items.*', 'types.order', 'positions.code'])->first();

                $type = $item->type->code;
                if($type == 'hair'){
                    $color_id = $hair->color_id;
                    $color_id = $color_id - ($item_id * 10000);

                    $position = $item->code;
                    $order = $item->order;

                    $hair_items = $item->hair_items;
                    if($hair_items){
                        $hair_items = json_decode($hair_items);
                    }
                    foreach ($hair_items as $k => $hair_item){
                        if($k != $color_id){ //check child_id co duoc chon khong
                            continue;
                        }

                        $front_image = $hair_item->image;
                        $back_image = $hair_item->back_image;
                        $mid_image = $hair_item->mid_image;

                        if(is_file($this->public_path.$front_image) && file_exists($this->public_path.$front_image)){
                            $scale = 100;
                            if(in_array($type, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $pos_x = $hair_item->image_pos_x * ($scale/100);
                            $pos_y = $hair_item->image_pos_y * ($scale/100);
                            $data_merge_front_layer[] = $this->groupDataItem($item_id, $front_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                        }
                        if(is_file($this->public_path.$mid_image) && file_exists($this->public_path.$mid_image)){
                            $scale = 100;
                            if(in_array($type, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $pos_x = $hair_item->mid_image_pos_x * ($scale/100);
                            $pos_y = $hair_item->mid_image_pos_y * ($scale/100);
                            $data_merge_mid_layer[] = $this->groupDataItem($item_id, $mid_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                        }
                        if(is_file($this->public_path.$back_image) && file_exists($this->public_path.$back_image)){
                            $scale = 100;
                            if(in_array($type, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $pos_x = $hair_item->back_image_pos_x * ($scale/100);
                            $pos_y = $hair_item->back_image_pos_y * ($scale/100);
                            $data_merge_back_layer[] = $this->groupDataItem($item_id, $back_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                        }
                    }
                }
            }

            

            //Normal Items
            $items = Item::join('types', 'items.type_id', 'types.id')
                ->join('positions', 'types.position_id', 'positions.id')
                ->whereIn('items.id', $arr_item_id)
                ->orderBy('types.order', 'ASC')
                ->get(['items.*', 'types.order', 'positions.code']);

            foreach ($items as $item){
                $type = $item->type->code;
                if($type == 'hair' || $type == 'makeup'){
                    continue;
                }
                $position = $item->code;
                $order = $item->order;
                $item_id = $item->id;

                $front_image = $item->image;
                $left_image = $item->left_image;
                $mid_image = $item->mid_image;
                $right_image = $item->right_image;
                $back_image = $item->back_image;


                if(is_file($this->public_path.$front_image) && file_exists($this->public_path.$front_image)){
                    $scale = 100;
                    if(in_array($type, $type_scale_1_4)) {
                        $scale = 25; //Thu vien nay no tinh theo %
                    }
                    $pos_x = $item->image_pos_x * ($scale/100);
                    $pos_y = $item->image_pos_y * ($scale/100);
                    $data_merge_front_layer[] = $this->groupDataItem($item_id, $front_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                }
                if(is_file($this->public_path.$left_image) && file_exists($this->public_path.$left_image)){
                    $scale = 100;
                    if(in_array($type, $type_scale_1_4)) {
                        $scale = 25; //Thu vien nay no tinh theo %
                    }
                    $pos_x = $item->left_image_pos_x * ($scale/100);
                    $pos_y = $item->left_image_pos_y * ($scale/100);
                    $data_merge_left_layer[] = $this->groupDataItem($item_id, $left_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                }
                if(is_file($this->public_path.$mid_image) && file_exists($this->public_path.$mid_image)){
                    $scale = 100;
                    if(in_array($type, $type_scale_1_4)) {
                        $scale = 25; //Thu vien nay no tinh theo %
                    }
                    $pos_x = $item->mid_image_pos_x * ($scale/100);
                    $pos_y = $item->mid_image_pos_y * ($scale/100);
                    $data_merge_mid_layer[] = $this->groupDataItem($item_id, $mid_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                }
                if(is_file($this->public_path.$right_image) && file_exists($this->public_path.$right_image)){
                    $scale = 100;
                    if(in_array($type, $type_scale_1_4)) {
                        $scale = 25; //Thu vien nay no tinh theo %
                    }
                    $pos_x = $item->right_image_pos_x * ($scale/100);
                    $pos_y = $item->right_image_pos_y * ($scale/100);
                    $data_merge_right_layer[] = $this->groupDataItem($item_id, $right_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                }
                if(is_file($this->public_path.$back_image) && file_exists($this->public_path.$back_image)){
                    $scale = 100;
                    if(in_array($type, $type_scale_1_4)) {
                        $scale = 25; //Thu vien nay no tinh theo %
                    }
                    $pos_x = $item->back_image_pos_x * ($scale/100);
                    $pos_y = $item->back_image_pos_y * ($scale/100);
                    $data_merge_back_layer[] = $this->groupDataItem($item_id, $back_image, $pos_x, $pos_y, $order, $scale, $type, $position);
                }
            }



            //Merge
            $data_merge = [];

            $collection = collect($data_merge_back_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $data_merge[] = [
                    'item_id' => $data['item_id'],
                    'image' => $data['image'],
                    'pos_x' => $data['pos_x'],
                    'pos_y' => $data['pos_y'],
                    'scale' => $data['scale'],
                    'type' => $data['type'],
                    'order' => $data['order'],
                    'position' => $data['position'],
                    'layer' => 'back'
                ];
            }

            $collection = collect($data_merge_right_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $data_merge[] = [
                    'item_id' => $data['item_id'],
                    'image' => $data['image'],
                    'pos_x' => $data['pos_x'],
                    'pos_y' => $data['pos_y'],
                    'scale' => $data['scale'],
                    'type' => $data['type'],
                    'order' => $data['order'],
                    'position' => $data['position'],
                    'layer' => 'right'
                ];
            }

            $collection = collect($data_merge_mid_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $data_merge[] = [
                    'item_id' => $data['item_id'],
                    'image' => $data['image'],
                    'pos_x' => $data['pos_x'],
                    'pos_y' => $data['pos_y'],
                    'scale' => $data['scale'],
                    'type' => $data['type'],
                    'order' => $data['order'],
                    'position' => $data['position'],
                    'layer' => 'mid'
                ];
            }
            $collection = collect($data_merge_left_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $data_merge[] = [
                    'item_id' => $data['item_id'],
                    'image' => $data['image'],
                    'pos_x' => $data['pos_x'],
                    'pos_y' => $data['pos_y'],
                    'scale' => $data['scale'],
                    'type' => $data['type'],
                    'order' => $data['order'],
                    'position' => $data['position'],
                    'layer' => 'left'
                ];
            }

            $collection = collect($data_merge_front_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $data_merge[] = [
                    'item_id' => $data['item_id'],
                    'image' => $data['image'],
                    'pos_x' => $data['pos_x'],
                    'pos_y' => $data['pos_y'],
                    'scale' => $data['scale'],
                    'type' => $data['type'],
                    'order' => $data['order'],
                    'position' => $data['position'],
                    'layer' => 'front'
                ];
            }
            if(empty($data_merge)){
                //Log::info(0);
                return 0;
            }

            foreach ($data_merge as $key => $value){
                $item_id = $value['item_id'];
                $image = $value['image'];
                $sourceX = $value['pos_x'];
                $sourceY = $value['pos_y'];
                $scaleX = $value['scale'];
                $data_merge[$key]['child_id'] = $item_id.'_'.$key; //tạo id cho item của layer
                $data_merge[$key]['image'] = $this->mergeImageSigle($key, $image, $sourceX, $sourceY, $scaleX);
            }



            if($is_tuck == 1){
                $source_position_accept_mask = ['jacket', 'vest_sweaters', 'bottom_pantshort_skirt', 'top', 'dresses', 'bikini_top', 'bikini_bottom'];
            }else{
                $source_position_accept_mask = ['jacket', 'vest_sweaters', 'top', 'bottom_pantshort_skirt', 'dresses', 'bikini_top', 'bikini_bottom'];
            }

            $collection = collect($data_merge);
            $filteredGrouped = $collection->filter(function ($item) use ($source_position_accept_mask) {
                return in_array($item['position'], $source_position_accept_mask);
            })->groupBy('item_id');
            $sortedFilteredGrouped = $filteredGrouped->sortBy(function ($group, $key) use ($source_position_accept_mask) {
                return array_search($group->first()['position'], $source_position_accept_mask);
            });
            $data_merge_by_item = $sortedFilteredGrouped->toArray();

            //những item còn lại
            $filtered = $collection->reject(function ($item) use ($source_position_accept_mask) {
                return in_array($item['position'], $source_position_accept_mask);
            });
            $data_merge_other = $filtered->toArray();

            $newArray = array();
            foreach ($data_merge_by_item as $key => $value) {
                foreach ($value as $item) {
                    $layer = $item['layer'];
                    $newArray[$key][$layer] = $item;
                }
            }

            $data_merge_by_item = array_values($newArray);
            $this->data_merge_by_item = $data_merge_by_item;



            //clear image by mask
            // item có k nhỏ hơn sẽ clear item sau đó
            foreach ($data_merge_by_item as $k => $item){
                if($k > 0){
                    $this->clearImageByMask($k-1, $k); //dùng item k-1 để clear item k
                }
            }

            //remove pixel mask 102
            $data_merge_item = $this->data_merge_by_item;
            foreach ($data_merge_item as $k => $item){
                if($k > 0){
                    foreach ($item as $layer => $value){
                        $data_merge_item[$k][$layer]['image'] = $this->removePixelMask($value['image'], $k, $layer);
                    }
                }
            }

            $this->data_merge_by_item = $data_merge_item;

            //đảo ngược mảng để merge theo thứ tự đúng order
            $data_merge = array_reverse($this->data_merge_by_item);

            //group theo layer
            $result = [];
            foreach ($data_merge as $item) {
                foreach ($item as $key => $value) {
                    $result[$key][] = $value;
                }
            }

            $data_end = $this->mergeImageLayer($result, $data_merge_other, $is_tuck);

            $image_end = null;
            foreach ($data_end as $k => $image){
                if($k == 0){
                    $image_end = $image;
                    continue;
                }
                $image_end = $this->mergeImageNormal('challenge', 'image', $image_end, $image);
            }
            if($image_end !== null){
                $filename = str_replace($this->public_path, '', $image_end);
                UserChallenge::where('_id', $user_challenge_id)->update(['image' => $filename]);
                foreach($this->arr_filename as $value){
                    if($value != $image_end){
                        unlink($value);
                    }
                }
            }
            return 1;

        // }catch(exception $e){
        //     echo $e->getMessage();
        //     //Log::info($e->getMessage());
        //     return 0;
        // }
    }



    private function groupDataItem($item_id, $image, $pos_x, $pos_y, $order, $scale, $type, $position){
        $data = [
            'item_id' => $item_id,
            'order' => $order,
            'image' => $this->public_path.$image,
            'pos_x' => $pos_x,
            'pos_y' => $pos_y,
            'scale' => $scale,
            'type' => $type,
            'position' => $position,
        ];
        return $data;
    }
    public function scale($sourceImagePath, $presentage){

        $sourceImage = imagecreatefrompng($sourceImagePath);
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $width = $sourceWidth * $presentage/100;
        $height = $sourceHeight * $presentage/100;
        $new_image = $this->createTransparentTemplate($width,$height);
        imagecopyresampled($new_image, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        return $new_image;
    }
    public function createTransparentTemplate($width,$height){
        $copy = imagecreatetruecolor($width, $height);
        $color = imagecolorallocatealpha($copy, 0, 0, 0, 127);
        imagefill($copy, 0, 0, $color);
        imagesavealpha($copy, true);
        return $copy;
    }

    private function removePixelMask($filePath, $item_id, $k){
        $check = 0;
        $filename = $this->public_path.'uploads/user_challenge/' . $this->rename . $item_id . '-' . $k . '-result.png';
        array_push($this->arr_filename, $filename);
        $sourceImage = imagecreatefrompng($filePath);

        $result = imagecreatetruecolor($this->width, $this->height);
        $transparent = imagecolorallocatealpha($result, 0, 0, 0, 127);
        imagefill($result, 0, 0, $transparent);
        imagesavealpha($result, true);

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $pixelColor = imagecolorat($sourceImage, $x, $y);
                $rgba = imagecolorsforindex($sourceImage, $pixelColor);
                if (!($rgba['red'] == 0 && $rgba['green'] == 255 && $rgba['blue'] == 0 && $rgba['alpha'] == 126)) {
                    $red = $rgba['red'];
                    $green = $rgba['green'];
                    $blue = $rgba['blue'];
                    $alpha = $rgba['alpha'];
                    $transparentColor = imagecolorallocatealpha($result, $red, $green, $blue, $alpha);
                    imagesetpixel($result, $x, $y, $transparentColor);
                    $check = 1;
                }
            }
        }
        if($check == 1){
            imagepng($result, $filename);
            $filePath = $filename;
        }
        imagedestroy($result);
        return $filePath;
    }

    public function mergeImageSigle($k, $sourceImagePath, $sourceX, $sourceY, $scale){

        //try{
            $filename = $this->public_path.'uploads/user_challenge/' . $this->rename . $k . '-result.png';
            array_push($this->arr_filename, $filename);
            if($scale < 100){
                $sourceImage = $this->scale($sourceImagePath, $scale);
            }else{
                $sourceImage = @imagecreatefrompng($sourceImagePath);
            }
            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            $sourceX = $sourceX - ($sourceWidth / 2);
            $sourceY = $sourceY - ($sourceHeight / 2);

            $all = imagecreatetruecolor($this->width, $this->height);
            $transparent = imagecolorallocatealpha($all, 0, 0, 0, 127);
            imagefill($all, 0, 0, $transparent);
            imagesavealpha($all, true);
            imagecopy($all, $sourceImage, $sourceX, $sourceY, 0, 0, $sourceWidth, $sourceHeight);
            imagepng($all, $filename);
            imagedestroy($all);

            return $filename;
        // }catch(exception $e){
            
        // }
        
    }
    public function mergeImageNormal($item_id, $k, $sourceImagePath, $maskImagePath){

        $filename = $this->public_path.'uploads/user_challenge/' . $this->rename . $item_id . '-' . $k . '-result.png';
        array_push($this->arr_filename, $filename);
        $sourceImage = imagecreatefrompng($sourceImagePath);
        $maskImage = imagecreatefrompng($maskImagePath);
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $result = imagecreatetruecolor($this->width, $this->height);
        $transparent = imagecolorallocatealpha($result, 0, 0, 0, 127);
        imagefill($result, 0, 0, $transparent);
        imagesavealpha($result, true);
        imagecopy($result, $sourceImage, 0, 0, 0, 0, $sourceWidth, $sourceHeight);
        imagecopy($result, $maskImage, 0, 0, 0, 0, $this->width, $this->height);
        imagepng($result, $filename);

        imagedestroy($result);
        imagedestroy($sourceImage);
        imagedestroy($maskImage);

        return $filename;
    }

    public function mergeImageMask($item_id, $k, $sourceImagePath, $maskImagePath)
    {
        $filename = $this->public_path.'uploads/user_challenge/' . $this->rename . $item_id . '-' . $k . '-clear.png';
        array_push($this->arr_filename, $filename);
        $sourceImage = imagecreatefrompng($sourceImagePath);
        $maskImage = imagecreatefrompng($maskImagePath);

        $resultImage2 = imagecreatetruecolor($this->width, $this->height);
        $transparents = imagecolorallocatealpha($resultImage2, 0, 0, 0, 127);
        imagefill($resultImage2, 0, 0, $transparents);
        imagesavealpha($resultImage2, true);
        imagecopy($resultImage2, $maskImage, 0, 0, 0, 0, $this->width, $this->height);

        $result = imagecreatetruecolor($this->width, $this->height);
        $transparent = imagecolorallocatealpha($result, 0, 0, 0, 127);
        imagefill($result, 0, 0, $transparent);
        imagesavealpha($result, true);

        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $pixelColor = imagecolorat($resultImage2, $x, $y);
                $colorInfo = imagecolorsforindex($resultImage2, $pixelColor);
                $red = $colorInfo['red'];
                $green = $colorInfo['green'];
                $blue = $colorInfo['blue'];
                $alpha = $colorInfo['alpha'];

                if (!($red == 0 && $green == 0 && $blue == 0 && $alpha == 127)) {
                    $pixelColor = imagecolorat($sourceImage, $x, $y);
                    $colorInfo = imagecolorsforindex($resultImage2, $pixelColor);
                    $red = $colorInfo['red'];
                    $green = $colorInfo['green'];
                    $blue = $colorInfo['blue'];
                    $alpha = $colorInfo['alpha'];

                    $transparentColor = imagecolorallocatealpha($result, $red, $green, $blue, $alpha);
                    imagesetpixel($result, $x, $y, $transparentColor);
                }
            }
        }
        imagepng($result, $filename);

        imagedestroy($sourceImage);
        imagedestroy($maskImage);
        imagedestroy($resultImage2);
        imagedestroy($result);

        return $filename;

    }

    private function mergeImageLayer($result, $data_merge_other, $is_tuck){
        //tiến hành merge theo layer
        $data_end = [];
        /*=============*/
        $data = [];
        if(isset($result['back'])){
            $data = $result['back'];
        }
        $add = 0;
        foreach ($data_merge_other as $item){
            if($item['layer'] == 'back'){
                $add = 1;
                array_push($data, $item);
            }
        }
        if($add == 1){
            usort($data, function($a, $b) {
                return $a['order'] - $b['order'];
            });
        }
        if(!empty($data)){
            if($is_tuck == 1){
                $data = $this->swapTopWithBottom($data); //sau khi thực hiện các bước trên thì thứ tự đã bị thay đổi nên cần swap lại top và bottom_pantshort_skirt
            }
            $image_1 = null;
            foreach ($data as $k => $value){
                if($k == 0){
                    $image_1 = $value['image'];
                    continue;
                }
                $image_2 = $value['image'];
                $item_id = 'back';
                $image_1 = $this->mergeImageNormal($item_id, $k, $image_1, $image_2);
            }
            $data_end[] = $image_1;
        }
        /*=============*/

        /*=============*/
        $data = [];
        if(isset($result['right'])){
            $data = $result['right'];
        }
        $add = 0;
        foreach ($data_merge_other as $item){
            if($item['layer'] == 'right'){
                $add = 1;
                array_push($data, $item);
            }
        }
        if($add == 1){
            usort($data, function($a, $b) {
                return $a['order'] - $b['order'];
            });
        }
        if(!empty($data)){
            if($is_tuck == 1){
                $data = $this->swapTopWithBottom($data);
            }
            $image_1 = null;
            foreach ($data as $k => $value){
                if($k == 0){
                    $image_1 = $value['image'];
                    continue;
                }
                $image_2 = $value['image'];
                $item_id = 'right';
                $image_1 = $this->mergeImageNormal($item_id, $k, $image_1, $image_2);
            }
            $data_end[] = $image_1;
        }
        /*=============*/

        /*=============*/
        $data = [];
        if(isset($result['mid'])){
            $data = $result['mid'];
        }
        $add = 0;
        foreach ($data_merge_other as $item){
            if($item['layer'] == 'mid'){
                $add = 1;
                array_push($data, $item);
            }
        }
        if($add == 1){
            usort($data, function($a, $b) {
                return $a['order'] - $b['order'];
            });
        }
        
        if(!empty($data)){
            if($is_tuck == 1){
                $data = $this->swapTopWithBottom($data);
                $data = $this->swapShoesWithBottom($data); //truong hop nay chi can doi swap mid thoi, vi giay toan la mid
            }   
            $image_1 = null;
            foreach ($data as $k => $value){
                if($k == 0){
                    $image_1 = $value['image'];
                    continue;
                }
                $image_2 = $value['image'];
                $item_id = 'mid';
                $image_1 = $this->mergeImageNormal($item_id, $k, $image_1, $image_2);
            }
            $data_end[] = $image_1;
        }
        /*=============*/

        /*=============*/
        $data = [];
        if(isset($result['left'])){
            $data = $result['left'];
        }
        $add = 0;
        foreach ($data_merge_other as $item){
            if($item['layer'] == 'left'){
                $add = 1;
                array_push($data, $item);
            }
        }
        if($add == 1){
            usort($data, function($a, $b) {
                return $a['order'] - $b['order'];
            });
        }
        if(!empty($data)){
            if($is_tuck == 1){
                $data = $this->swapTopWithBottom($data);
            }
            $image_1 = null;
            foreach ($data as $k => $value){
                if($k == 0){
                    $image_1 = $value['image'];
                    continue;
                }
                $image_2 = $value['image'];
                $item_id = 'left';
                $image_1 = $this->mergeImageNormal($item_id, $k, $image_1, $image_2);
            }
            $data_end[] = $image_1;
        }
        /*=============*/

        /*=============*/
        $data = [];
        if(isset($result['front'])){
            $data = $result['front'];
        }
        $add = 0;
        foreach ($data_merge_other as $item){
            if($item['layer'] == 'front'){
                $add = 1;
                array_push($data, $item);
            }
        }
        if($add == 1){
            usort($data, function($a, $b) {
                return $a['order'] - $b['order'];
            });
        }
        if(!empty($data)){
            if($is_tuck == 1){
                $data = $this->swapTopWithBottom($data);
            }
            $image_1 = null;
            foreach ($data as $k => $value){
                if($k == 0){
                    $image_1 = $value['image'];
                    continue;
                }
                $image_2 = $value['image'];
                $item_id = 'front';
                $image_1 = $this->mergeImageNormal($item_id, $k, $image_1, $image_2);
            }
            $data_end[] = $image_1;
        }
        /*=============*/
        return $data_end;
    }
    private function swapTopWithBottom($data){
        // Tìm index của các mảng có position là bottom_pantshort_skirt và top
        $bottom_index = $top_index = null;
        foreach ($data as $index => $item) {
            if ($item['position'] === 'bottom_pantshort_skirt') {
                $bottom_index = $index;
            } elseif ($item['position'] === 'top') {
                $top_index = $index;
            }
        }

        // Nếu tìm thấy cả hai index thì hoán đổi chỗ chúng
        if ($bottom_index !== null && $top_index !== null) {
            $temp = $data[$bottom_index];
            $data[$bottom_index] = $data[$top_index];
            $data[$top_index] = $temp;
        }
        return $data;
    }

    private function swapShoesWithBottom($data){
        // Tìm index của các mảng có position là bottom_pantshort_skirt và shoes
        $bottom_index = $shoes_index = null;
        foreach ($data as $index => $item) {
            if ($item['position'] === 'bottom_pantshort_skirt') {
                $bottom_index = $index;
            } elseif ($item['position'] === 'shoes') {
                $shoes_index = $index;
            }
        }

        // Nếu tìm thấy cả hai index thì hoán đổi chỗ chúng
        if ($bottom_index !== null && $shoes_index !== null) {
            $temp = $data[$bottom_index];
            $data[$bottom_index] = $data[$shoes_index];
            $data[$shoes_index] = $temp;
        }
        return $data;
    }

    private function clearImageByMask($n, $m){

        $data_merge_item = $this->data_merge_by_item;

        $item_mask = $data_merge_item[$n];
        $item = $data_merge_item[$m];


        $onyly_bi_mask = ['bikini_top', 'bikini_bottom'];

        foreach ($item as $layer => $value){
            if(!isset($item_mask[$layer])){ //check nếu item bên ngoài ko có layer thì dùng item bên ngoài nữa để clear.
                if(isset($data_merge_item[$n-1][$layer])){
                    $item_mask_before = $data_merge_item[$n-1];
                    $position_mask = $item_mask_before[$layer]['position'];
                    $image_mask = $item_mask_before[$layer]['image'];
                }else{
                    continue;
                }
            }else{
                $position_mask = $item_mask[$layer]['position'];
                $image_mask = $item_mask[$layer]['image'];
            }
            if(in_array($position_mask, $onyly_bi_mask)){
                return;
            }

            $item_id = $value['item_id'];
            $image = $value['image'];
            $child_id = $value['child_id']; //id item của layer

            $image_clear = $this->mergeImageMask($item_id, $child_id, $image, $image_mask); //dùng titem $image_mask clear item $image
            $this->data_merge_by_item[$m][$layer]['image'] = $image_clear;


        }

        return;
    }
    public function convertFileName($fileName) {
        $fileName = str_replace(' ', '_', $fileName);
        $fileName = str_replace(':', '_', $fileName);
        return $fileName;
    }
}
