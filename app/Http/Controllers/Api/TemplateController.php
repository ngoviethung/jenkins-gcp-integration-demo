<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use Exception;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Template;
use ZipArchive;
use URL;
use \Treinetic\ImageArtist\lib\Image;
use DB;
use App\Models\Type;

class TemplateController extends AppBaseController
{

    public function cloneTemplate(Request $request){

        try{
            $template_id = $request->template_id;
            $admin_id = $request->admin_id;
            $template = Template::find($template_id);

            $new_template = $template->replicate();
            $new_template->admin_id = $admin_id;
            $new_template->created_at = date("Y-m-d H:i:s");
            $new_template->save();

            return 1;
        }catch(exception $e){
            return 0;
        }

    }
    /*
    public function deleteFile(Request $request){

        $filename = $request->filename;
        if(file_exists("uploads/zip_template/$filename")){
            unlink("uploads/zip_template/$filename");
        }
        $filename = explode('.zip', $filename);
        $filename = $filename[0].'.png';
        if(file_exists("uploads/template/$filename")){
            unlink("uploads/template/$filename");
        }

        return $this->sendResponse([]);

    }
    */

    private function groupDataItem($image, $pos_x, $pos_y, $order, $scale){
        $data = [
            'order' => $order,
            'image' => $image,
            'pos_x' => $pos_x,
            'pos_y' => $pos_y,
            'scale' => $scale
        ];
        return $data;
    }
    public function saveImageFromRef($image){//read image from url and write
        if($image){
            $url_media = env('URL_MEDIA');
            $link_image = $url_media.$image;

            if(!file_exists($image)){
                file_put_contents($image, file_get_contents($link_image));
            }
        }

        return $image;
    }
    public function mergeAndZipImage(Request $request){

        try{
            $type_scale_1_4 = DB::connection('mysql2')->table('types')->where('scale', 0.25)->get(['code'])->pluck('code')->toArray();

            $template_id = $request->template_id;
            $template = Template::find($template_id);
            if(!$template){
                throw new Exception('Template not found');
            }

            //$name = $template->name;
            $name = $template_id;
            if(file_exists($template->file_zip)){
                $zip_file_name = explode('zip_template/', $template->file_zip);
                $zip_file_name = $zip_file_name[1];
                return $this->sendResponse(['file' => $template->file_zip, 'filename' => $zip_file_name]);
            }

            $list_item_id = $template->item_id;
            $arr_item_id = explode(',', $list_item_id);
            $arr_parent_child_id = [];
            foreach ($arr_item_id as &$item_id){
                $value = explode('-', $item_id);
                $item_id = $value[0];
                if(isset($value[1])){
                    $arr_parent_child_id[$item_id] = $value[1];
                }
            }
            $items = Item::join('types', 'items.type_id', 'types.id')
                ->whereIn('items.id', $arr_item_id)
                ->orderBy('types.order', 'ASC')
                ->get(['items.*', 'types.order']);

            foreach ($items as $item){
                $item->child_id = isset($arr_parent_child_id[$item->id]) ? $arr_parent_child_id[$item->id] : null;
            }

            //add items checking to list items
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


            $zip = new ZipArchive();

            $zip_file_path = "uploads/zip_template/";
            $zip_file_name = $name.'_'.date('Y-m-d H:i:s').'.zip';

            if ($zip->open($zip_file_path.$zip_file_name, ZIPARCHIVE::CREATE) != TRUE) {
                die ("Could not open archive");
            }

            $background = $template->background;
            if(!file_exists($background)){
                throw new Exception('Background not found');
            }

            $zip->addFile($background, 'items/'.$this->convertFileName(basename($background)));

            $img = new Image($background);

            //Anh dau tien la anh png se bi loi luc megre nen phai format ve anh jpg
            if(exif_imagetype($background) == 3){ // IMAGETYPE_PNG

                $filename = basename($background);
                $filename = explode('.png', $filename);
                $filename = $filename[0];
                $filename_background_convert = 'uploads/background_convert/'.$filename.'.jpg';

                if(!file_exists($filename_background_convert)){
                    $img->save($filename_background_convert, IMAGETYPE_JPEG);
                }
                $img = new Image($filename_background_convert);

            }
            //697 la chieu rong cua background chuan? = khoang cach tu tam con mau * 2
            $add_x =  ($img->getWidth() - 697) / 2; // width background is large than origin
            $add_y = ($img->getHeight() - 1334) / 2; // height background is large than origin


            $data_item_origin = [];
            $data_merge_front_layer = [];
            $data_merge_left_layer = [];
            $data_merge_mid_layer = [];
            $data_merge_right_layer = [];
            $data_merge_back_layer = [];

            $model = DB::connection('mysql2')->table('models')->where('app_id', env('APP_ID'))->first();
            $url_media = env('URL_MEDIA');
            $scale_model = $model->scale;
            $mid_image = $this->saveImageFromRef($model->body_image);
            $left_image = $this->saveImageFromRef($model->left_hand_image);
            $right_image = $this->saveImageFromRef($model->right_hand_image);
            $order = 0;
            $scale = $model->scale * 100; //thu vien nay dung sclae theo %

            $image_skin = [];
            $skins = DB::table('skins')->get();
            foreach ($skins as $k => $skin){
                $image_skin[$skin->id] = [
                    'left_hand_image' => $skin->left_hand_image,
                    'right_hand_image' => $skin->right_hand_image,
                    'body_image' => $skin->body_image,
                ];
            }

            $skin_id = $template->model;
            if($skin_id){
                $scale = 100;
                $mid_image = $image_skin[$skin_id]['body_image'];
                $left_image =  $image_skin[$skin_id]['left_hand_image'];
                $right_image = $image_skin[$skin_id]['right_hand_image'];
            }

            //cac toa do cua model ben client gui la da scale ve 0.25 roi nen ko can scale lai nua
            if(file_exists($left_image)){

                $zip->addFile($left_image, 'items/'.$this->convertFileName(basename($left_image)));
                $img_item = new Image($left_image);
                //$scale = $model->scale * 100;
                $img_item->scale($scale);

                $pos_x = ($model->left_hand_image_pos_x) - ($img_item->getWidth() / 2 ) + $add_x;
                $pos_y = ($model->left_hand_image_pos_y) - ($img_item->getHeight() / 2 ) + $add_y;
                $data_merge_left_layer[] = $this->groupDataItem($left_image, $pos_x, $pos_y, $order, $scale);
                $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                $data_item_origin[] = $this->groupDataItem($left_image, $pos_x_origin, $pos_y_origin, $order, $scale);
            }

            if(file_exists($mid_image)){
                $zip->addFile($mid_image, 'items/'.$this->convertFileName(basename($mid_image)));
                $img_item = new Image($mid_image);
                //$scale = $model->scale * 100;
                $img_item->scale($scale);
                $pos_x = ($model->body_image_pos_x) - ($img_item->getWidth() / 2 ) + $add_x;
                $pos_y = ($model->body_image_pos_y) - ($img_item->getHeight() / 2 ) + $add_y;
                $data_merge_mid_layer[] = $this->groupDataItem($mid_image, $pos_x, $pos_y, $order, $scale);
                $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                $data_item_origin[] = $this->groupDataItem($mid_image, $pos_x_origin, $pos_y_origin, $order, $scale);
            }
            if(file_exists($right_image)){
                $zip->addFile($right_image, 'items/'.$this->convertFileName(basename($right_image)));
                $img_item = new Image($right_image);
                //$scale = $model->scale * 100;
                $img_item->scale($scale);
                $pos_x = ($model->right_hand_image_pos_x) - ($img_item->getWidth() / 2 ) + $add_x;
                $pos_y = ($model->right_hand_image_pos_y) - ($img_item->getHeight() / 2 ) + $add_y;

                $data_merge_right_layer[] = $this->groupDataItem($right_image, $pos_x, $pos_y, $order, $scale);
                $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                $data_item_origin[] = $this->groupDataItem($right_image, $pos_x_origin, $pos_y_origin, $order, $scale);
            }


            //zip and merge image item

            foreach ($items as $item){
                $type_code = $item->type->code;
                $order = $item->order;

                switch ($type_code){
                    case 'hair':
                        $hair_items = $item->hair_items;
                        if($hair_items){
                            $hair_items = json_decode($hair_items);
                        }
                        foreach ($hair_items as $k => $hair_item){
                            if($k != $item->child_id){
                                continue;
                            }

                            $front_image = $item->job_id ? $this->saveImageFromRef($hair_item->image) : $hair_item->image;
                            $back_image = $item->job_id ? $this->saveImageFromRef($hair_item->back_image) : $hair_item->back_image;
                            $mid_image = $item->job_id ? $this->saveImageFromRef($hair_item->mid_image) : $hair_item->mid_image;

                            if(file_exists($front_image)){
                                $zip->addFile($front_image, 'items/'.$this->convertFileName(basename($front_image)));
                                $img_item = new Image($front_image);
                                $scale = 100;
                                if(in_array($type_code, $type_scale_1_4)) {
                                    $scale = 25; //Thu vien nay no tinh theo %
                                }
                                $img_item->scale($scale);
                                $pos_x = ($hair_item->image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                                $pos_y = ($hair_item->image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                                $data_merge_front_layer[] = $this->groupDataItem($front_image, $pos_x, $pos_y, $order, $scale);
                                $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                                $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                                $data_item_origin[] = $this->groupDataItem($front_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                            }
                            if(file_exists($mid_image)){
                                $zip->addFile($mid_image, 'items/'.$this->convertFileName(basename($mid_image)));
                                $img_item = new Image($mid_image);
                                $scale = 100;
                                if(in_array($type_code, $type_scale_1_4)) {
                                    $scale = 25; //Thu vien nay no tinh theo %
                                }
                                $img_item->scale($scale);
                                $pos_x = ($hair_item->mid_image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                                $pos_y = ($hair_item->mid_image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                                $data_merge_mid_layer[] = $this->groupDataItem($mid_image, $pos_x, $pos_y, $order, $scale);
                                $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                                $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                                $data_item_origin[] = $this->groupDataItem($mid_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                            }
                            if(file_exists($back_image)){
                                $zip->addFile($back_image, 'items/'.$this->convertFileName(basename($back_image)));
                                $img_item = new Image($back_image);
                                $scale = 100;
                                if(in_array($type_code, $type_scale_1_4)) {
                                    $scale = 25; //Thu vien nay no tinh theo %
                                }
                                $img_item->scale($scale);
                                $pos_x = ($hair_item->back_image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                                $pos_y = ($hair_item->back_image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                                $data_merge_back_layer[] = $this->groupDataItem($back_image, $pos_x, $pos_y, $order, $scale);
                                $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                                $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                                $data_item_origin[] = $this->groupDataItem($back_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                            }

                        }
                        break;
                    case 'makeup':
                        $makeup_items = $item->makeup_items;
                        if($makeup_items){
                            $makeup_items = json_decode($makeup_items);
                        }
                        if(isset($makeup_items[0])){

                            foreach ($makeup_items[0] as $k => $front_image){
                                if($k != $item->child_id){
                                    continue;
                                }
                                $front_image = $item->job_id ? $this->saveImageFromRef($front_image) : $front_image;

                                if(file_exists($front_image) && $k == 0){
                                    $zip->addFile($front_image, 'items/'.$this->convertFileName(basename($front_image)));
                                    $img_item = new Image($front_image);
                                    $scale = 100;
                                    if(in_array($type_code, $type_scale_1_4)) {
                                        $scale = 25; //Thu vien nay no tinh theo %
                                    }
                                    $img_item->scale($scale);
                                    $pos_x = ($item->image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                                    $pos_y = ($item->image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                                    $data_merge_front_layer[] = $this->groupDataItem($front_image, $pos_x, $pos_y, $order, $scale);
                                    $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                                    $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                                    $data_item_origin[] = $this->groupDataItem($front_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                                }
                                $k++;
                            }
                        }

                        break;
                    default:

                        $front_image = $item->job_id ? $this->saveImageFromRef($item->image) : $item->image;
                        $left_image = $item->job_id ? $this->saveImageFromRef($item->left_image) : $item->left_image;
                        $mid_image = $item->job_id ? $this->saveImageFromRef($item->mid_image) : $item->mid_image;
                        $right_image = $item->job_id ? $this->saveImageFromRef($item->right_image) : $item->right_image;
                        $back_image = $item->job_id ? $this->saveImageFromRef($item->back_image) : $item->back_image;


                        if(file_exists($front_image)){
                            $zip->addFile($front_image, 'items/'.$this->convertFileName(basename($front_image)));
                            $img_item = new Image($front_image);
                            $scale = 100;
                            if(in_array($type_code, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $img_item->scale($scale);
                            $pos_x = ($item->image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                            $pos_y = ($item->image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                            $data_merge_front_layer[] = $this->groupDataItem($front_image, $pos_x, $pos_y, $order, $scale);
                            $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                            $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                            $data_item_origin[] = $this->groupDataItem($front_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                        }
                        if(file_exists($left_image)){
                            $zip->addFile($left_image, 'items/'.$this->convertFileName(basename($left_image)));
                            $img_item = new Image($left_image);
                            $scale = 100;
                            if(in_array($type_code, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $img_item->scale($scale);
                            $pos_x = ($item->left_image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                            $pos_y = ($item->left_image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                            $data_merge_left_layer[] = $this->groupDataItem($left_image, $pos_x, $pos_y, $order, $scale);
                            $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                            $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                            $data_item_origin[] = $this->groupDataItem($left_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                        }
                        if(file_exists($mid_image)){
                            $zip->addFile($mid_image, 'items/'.$this->convertFileName(basename($mid_image)));
                            $img_item = new Image($mid_image);
                            $scale = 100;
                            if(in_array($type_code, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $img_item->scale($scale);
                            $pos_x = ($item->mid_image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                            $pos_y = ($item->mid_image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                            $data_merge_mid_layer[] = $this->groupDataItem($mid_image, $pos_x, $pos_y, $order, $scale);
                            $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                            $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                            $data_item_origin[] = $this->groupDataItem($mid_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                        }
                        if(file_exists($right_image)){
                            $zip->addFile($right_image, 'items/'.$this->convertFileName(basename($right_image)));
                            $img_item = new Image($right_image);
                            $scale = 100;
                            if(in_array($type_code, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $img_item->scale($scale);
                            $pos_x = ($item->right_image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                            $pos_y = ($item->right_image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                            $data_merge_right_layer[] = $this->groupDataItem($right_image, $pos_x, $pos_y, $order, $scale);
                            $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                            $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                            $data_item_origin[] = $this->groupDataItem($right_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                        }
                        if(file_exists($back_image)){
                            $zip->addFile($back_image, 'items/'.$this->convertFileName(basename($back_image)));
                            $img_item = new Image($back_image);
                            $scale = 100;
                            if(in_array($type_code, $type_scale_1_4)) {
                                $scale = 25; //Thu vien nay no tinh theo %
                            }
                            $img_item->scale($scale);
                            $pos_x = ($item->back_image_pos_x * ($scale/100)) - ($img_item->getWidth() / 2 ) + $add_x;
                            $pos_y = ($item->back_image_pos_y * ($scale/100)) - ($img_item->getHeight() / 2 ) + $add_y;
                            $data_merge_back_layer[] = $this->groupDataItem($back_image, $pos_x, $pos_y, $order, $scale);
                            $pos_x_origin = $pos_x + ($img_item->getWidth() / 2 ) - $add_x;
                            $pos_y_origin = $pos_y + ($img_item->getHeight() / 2 ) - $add_y;
                            $data_item_origin[] = $this->groupDataItem($back_image, $pos_x_origin, $pos_y_origin, $order, $scale);
                        }
                }


            }

            $collection = collect($data_merge_back_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $image = $data['image'];
                $pos_x = $data['pos_x'];
                $pos_y = $data['pos_y'];
                $scale = $data['scale'];
                $img_item = new Image($image);
                $img_item->scale($scale);
                $img->merge($img_item, $pos_x, $pos_y);
            }
            $collection = collect($data_merge_right_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $image = $data['image'];
                $pos_x = $data['pos_x'];
                $pos_y = $data['pos_y'];
                $scale = $data['scale'];
                $img_item = new Image($image);
                $img_item->scale($scale);
                $img->merge($img_item, $pos_x, $pos_y);
            }
            $collection = collect($data_merge_mid_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $image = $data['image'];
                $pos_x = $data['pos_x'];
                $pos_y = $data['pos_y'];
                $scale = $data['scale'];
                $img_item = new Image($image);
                $img_item->scale($scale);
                $img->merge($img_item, $pos_x, $pos_y);
            }
            $collection = collect($data_merge_left_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $image = $data['image'];
                $pos_x = $data['pos_x'];
                $pos_y = $data['pos_y'];
                $scale = $data['scale'];
                $img_item = new Image($image);
                $img_item->scale($scale);
                $img->merge($img_item, $pos_x, $pos_y);
            }
            $collection = collect($data_merge_front_layer);
            $sorted = $collection->sortBy('order');
            foreach ($sorted as $data){
                $image = $data['image'];
                $pos_x = $data['pos_x'];
                $pos_y = $data['pos_y'];
                $scale = $data['scale'];
                $img_item = new Image($image);
                $img_item->scale($scale);
                $img->merge($img_item, $pos_x, $pos_y);
            }


            /*======create file js for photoshop =======*/

            $settingFile = "uploads/template/settings/{$template_id}_create.js";
            $settings = collect($data_item_origin)->map(function ($e) {
               $e['image'] = $this->convertFileName(basename($e['image']));
               $e['order'] = (int) $e['order'];
               return $e;
            });
            $settings = $settings->sortBy('order');
            $settings = $settings->values();
            $settings->all();
            file_put_contents(
                public_path($settingFile), $this->getScriptContent($settings)
            );
            $zip->addFile($settingFile, 'create.js');

            /*======end create file js for photoshop =======*/


            $filename = "uploads/template/".$name.'_'.date('Y-m-d H:i:s').'.png';
            $img->save($filename, IMAGETYPE_PNG);

            if(file_exists($filename)){
                $zip->addFile($filename, 'demo/'.basename($filename));
            }
            $zip->close();
            if(!file_exists($zip_file_path.$zip_file_name)){
                throw new Exception('Create file zip faild');
            }

            Template::where('id', $template_id)->update(['file_zip' => $zip_file_path.$zip_file_name, 'template' => $filename]);

            return $this->sendResponse(['file' => $zip_file_path.$zip_file_name, 'filename' => $zip_file_name, 'file_template' => env('URL_CDN').$filename]);

        }catch(exception $e){
            return $this->sendError($e->getMessage());
        }
    }

    public function getScriptContent($settings) {
        $content = $settings->toJson();
        $content = "var settings = " . $content . ";";
        $content .= file_get_contents(__DIR__ .'/create.js');

        return $content;
    }

    public function convertFileName($fileName) {
        $fileName = str_replace(' ', '_', $fileName);
        $fileName = str_replace(':', '_', $fileName);
        return $fileName;
    }
}
