<?php

namespace App\Models;


use App\OutfitRevision;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use DB;
use Illuminate\Database\Eloquent\Model;
use Request;
use URL;

class Outfit extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'outfits';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['name', 'category', 'item_id', 'item_checking_id', 'topic_id', 'admin_id', 'background', 'file_zip', 'template'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(BackpackUser::class, 'admin_id', 'id');
    }
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }



    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setBackgroundAttribute($value){

        $value = str_replace(URL::asset('/'), '', $value);
        $this->attributes['background'] = $value;
    }

    public function setItemCheckingIdAttribute($value){

        $arr_item_checking_id = explode(',', $value);
        if(!empty($arr_item_checking_id)){
            foreach($arr_item_checking_id as $item_checking_id){
                $arr_value = explode('_SEPERATE_', $item_checking_id);
                if(isset($arr_value[4]) && !file_exists($arr_value[4])){ //neu file do chua duoc convert tu cms ref ve local


                    $image_item_checking = env('URL_MEDIA').$arr_value[4];

                    $file_info = pathinfo($image_item_checking);
                    $new_file_name = date('Y-m-d H:i:s').'_'.rand(1111, 99999999).'.'.$file_info['extension'];
                    $file_item_checking = "uploads/item_checking/".$new_file_name;

                    $image_item_checking = file_get_contents($image_item_checking);
                    file_put_contents($file_item_checking, $image_item_checking);

                    if(file_exists($file_item_checking)){
                        $value = str_replace($arr_value[4],$file_item_checking, $value);
                    }
                }
                if(isset($arr_value[5]) && !file_exists($arr_value[5])){ //neu file do chua duoc convert tu cms ref ve local
                    if($arr_value[5] != 'null' && $arr_value[5] != ''){
                        $thumb_top_item_checking = env('URL_MEDIA').$arr_value[5];

                        $file_info = pathinfo($thumb_top_item_checking);
                        $new_file_name = date('Y-m-d H:i:s').'_'.rand(1111, 99999999).'.'.$file_info['extension'];
                        $file_item_checking = "uploads/item_checking/".$new_file_name;

                        $thumb_top_item_checking = file_get_contents($thumb_top_item_checking);
                        file_put_contents($file_item_checking, $thumb_top_item_checking);

                        if(file_exists($file_item_checking)){
                            $value = str_replace($arr_value[5],$file_item_checking, $value);
                        }
                    }

                }
            }
        }

        $this->attributes['item_checking_id'] = $value;

    }

//    public function setTopicIdAttribute($value){
//
//        $item_id = Request::post('item_id');
//        $arr_item_id = explode(',', $item_id);
//        $topics = DB::table('topic_item_rlt')->whereIn('item_id', $arr_item_id)->get()->pluck('topic_id')->toArray();
//        $topics = implode(',',array_unique($topics));
//        $this->attributes['topic_id'] = $topics;
//    }

}
