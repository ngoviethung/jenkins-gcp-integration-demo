<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Request;
use App\Services\Item\ItemService;

class Item extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'items';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'image',
        'left_image',
        'right_image',
        'back_image',
        'mid_image',
        'thumbnail',
        'price',
        'currency',
        'type_id',
        'group_level_item_id',
        'in_local',
        'ready_to_topic',
        'vip',
        'image_pos_x',
        'image_pos_y',
        'left_image_pos_x',
        'left_image_pos_y',
        'right_image_pos_x',
        'right_image_pos_y',
        'back_image_pos_x',
        'back_image_pos_y',
        'mid_image_pos_x',
        'mid_image_pos_y',
        'hair_colors'
    ];
    // protected $hidden = [];
    // protected $dates = [];

    public function service()
    {
        return new ItemService($this);
    }

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
    public function styles()
    {
        return $this->belongsToMany(Style::class)->withPivot('style_id', 'score');
    }

    public function topicDetails()
    {
        return $this->belongsToMany(TopicDetail::class, 'topic_detail_items')->select([
            'topic_details.topic_id',
            'type_id',
        ]);
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'topic_item_rlt', 'item_id', 'topic_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
    public function grouplevelitem() {
        return $this->belongsTo(GroupLevelItem::class,'group_level_item_id');
    }

    public function models() {
        return $this->belongsToMany(CharacterModel::class, 'item_models', 'item_id', 'model_id');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'item_color', 'item_id', 'color_id');
    }
    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'item_collection', 'item_id', 'collection_id');
    }
    public function patterns()
    {
        return $this->belongsToMany(Pattern::class, 'item_pattern', 'item_id', 'pattern_id');
    }
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'item_material', 'item_id', 'material_id');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'item_tag', 'item_id', 'tag_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
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
    public function getListStylesAttribute()
    {
        $styles = $this->styles;
        $html = '';
        foreach ($styles as $style) {
            $html .= "{$style->name}: {$style->pivot->score} <br/>";
        }
        return $html;
    }

    public function getListTopicsAttribute()
    {
        $topics = $this->topics;
        $html = '';
        foreach ($topics as $topic) {
            $html .= "{$topic->name} <br/>";
        }
        return $html;
    }
    public function getPriceCurrencyAttribute()
    {

        if($this->currency == 1){
            $currency = "Soft";
        }elseif($this->currency == 2){
            $currency = "Hard";
        }else{
            $currency = '';
        }
        return $this->price."(".$currency.")";

    }
    public function getMakeupItemsAttribute($value){
        if($value){
            $value = '['.$value.']';
        }
        return $value;
    }
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */


    public function save(array $options = [])
    {

        if($this->hair_items){
            $hair_items = json_decode($this->hair_items);
            if($hair_items){
                foreach ($hair_items as $item){
                    $item->image_pos_x = request()->post('image_pos_x');
                    $item->image_pos_y = request()->post('image_pos_y');
                    $item->mid_image_pos_x = request()->post('mid_image_pos_x');
                    $item->mid_image_pos_y = request()->post('mid_image_pos_y');
                    $item->back_image_pos_x = request()->post('back_image_pos_x');
                    $item->back_image_pos_y = request()->post('back_image_pos_y');
                }
                $this->attributes['hair_items'] = json_encode($hair_items);
            }


        }

        return parent::save($options);
    }
}
