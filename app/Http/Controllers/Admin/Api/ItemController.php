<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Admin\Api;


use App\Http\Resources\Admin\Api\ItemResource;
use App\Http\Resources\Admin\Api\ItemResourceTable;
use App\Http\Resources\Admin\Api\StyleResource;
use App\Http\Resources\Admin\Api\TopicCurrentForItem;
use App\Models\Item;
use App\Models\Topic;
use App\Models\Type;
use Illuminate\Http\Request;
use DB;
use Cache;

class ItemController
{

    public function getItemOutfits(Request $request){

        $topic_id = $request->topic_id;
        $type_id = $request->type_id;


        $data = Cache::remember($topic_id.'_'.$type_id, 60, function() use($topic_id, $type_id) {

            $arr_item_id = DB::table('topic_item_rlt')->where('topic_id', $topic_id)->get(['item_id'])->pluck('item_id')->toArray();
            $items = Item::with('type:id,order','styles:id')->where('type_id', $type_id)->whereIn('id', $arr_item_id)->get(['id', 'type_id', 'pos_x', 'pos_y', 'image', 'thumb_bottom', 'thumb_top','price','currency', 'vip']);

            foreach ($items as $item){
                $styles = $item->styles;
                foreach ($styles as $style){
                    if($style->id == 1){//style Đẹp
                        $item->style_score = $style->pivot->score;
                    }
                }
            }
            $collection = collect($items);
            $items = $collection->sortByDesc('style_score')->values();

            return $items;

        });


        return response()->json(['data' => $data]);
    }
    public function getItemTemplates(Request $request){


        $topic_id = $request->topic_id;
        $type_id = $request->type_id;
        $material_id = $request->material_id;
        $color_id = $request->color_id;
        $pattern_id = $request->pattern_id;

        $data = Cache::remember($topic_id.'_'.$type_id.'_'.$material_id.'_'.$color_id.'_'.$pattern_id, 60, function() use($topic_id, $type_id, $material_id, $color_id, $pattern_id) {
            $arrays = $arr_item_id = $arr_item_id_1 = $arr_item_id_2 = $arr_item_id_3 = $arr_item_id_4 = [];
            if($topic_id) {
                $arr_item_id_1 = DB::table('topic_item_rlt')->where('topic_id', $topic_id)->get(['item_id'])->pluck('item_id')->toArray();
                $arrays[] = $arr_item_id_1;
            }
            if($material_id) {
                $arr_item_id_2 = DB::connection('mysql2')
                    ->table('jobs')
                    ->whereIn('reference_id', function ($query) use ($material_id) {
                        $query->select('reference_id')
                            ->from('reference_material')
                            ->where('material_id', $material_id);
                    })
                    ->whereNotNull('item_id')
                    ->get(['item_id'])
                    ->pluck('item_id')
                    ->toArray();
                $arrays[] = $arr_item_id_2;
            }
            if($color_id) {
                $arr_item_id_3 = DB::connection('mysql2')
                    ->table('jobs')
                    ->whereIn('reference_id', function ($query) use ($color_id) {
                        $query->select('reference_id')
                            ->from('reference_color')
                            ->where('color_id', $color_id);
                    })
                    ->whereNotNull('item_id')
                    ->get(['item_id'])
                    ->pluck('item_id')
                    ->toArray();
                $arrays[] = $arr_item_id_3;

            }
            if($pattern_id) {
                $arr_item_id_4 = DB::connection('mysql2')
                    ->table('jobs')
                    ->whereIn('reference_id', function ($query) use ($pattern_id) {
                        $query->select('reference_id')
                            ->from('reference_pattern')
                            ->where('pattern_id', $pattern_id);
                    })
                    ->whereNotNull('item_id')
                    ->get(['item_id'])
                    ->pluck('item_id')
                    ->toArray();
                $arrays[] = $arr_item_id_4;
            }

            $result = [];

            if (count($arrays) > 1) {
                $result = call_user_func_array('array_intersect', $arrays);
//                echo "<pre>";
//                print_r($arrays);
//                print_r($result);die();

            }else{
                if(isset($arrays[0])){
                    $result = $arrays[0];
                }
            }
            if(!empty($result)){
                $arr_item_id = array_unique($result);
            }

            if(!empty($arrays)){
                $items = Item::with('type:id,order,name')->where('type_id', $type_id)->whereIn('id', $arr_item_id)->get();
            }else{
                $items = Item::with('type:id,order,name')->where('type_id', $type_id)->get();
            }

            return $items;

        });


        return response()->json(['data' => $data]);
    }

    public function getItemsPreview(Request $request){

        $arr_item_id = $request->arr_item_id;
        $arr_item_id = explode(',', $arr_item_id);
        $items = Item::whereIn('id', $arr_item_id)->get(['id', 'thumbnail']);

        return response()->json(['data' => $items]);
    }
    public function getItemDetail(Request $request){

        $item_id = $request->item_id;
        $data = Cache::remember('item_'.$item_id, 60, function() use($item_id) {
            $item = Item::with('type:id,order')->where('id', $item_id)->get();

            return $item;

        });

        return response()->json(['data' => $data]);
    }


    public function getItems()
    {
        $items = Item::all();
        $itemsResource = ItemResource::collection($items);
        return $itemsResource;
    }


    public function getItemsForTable()
    {
        $draw = request()->get('draw');
        $start = request()->get('start');
        $length = request()->get('length');
        $filter = request()->get('search');
        $columns = request()->get('columns');
        $orders = request()->get('order');

        if($orders && $orders[0]['column']) {
            $orderColumn = $columns[$orders[0]['column']]['name'];
            $orderDir = $orders[0]['dir'];
        } else {
            $orderColumn = 'active';
            $orderDir = 'desc';
        }

        $search = (isset($filter['value']))? $filter['value'] : '';
        $selectedIds = request()->get('selectedIds') ? request()->get('selectedIds') : '0';

        $items = Item::where('name', 'like', "%$search%")->orWhere('id', '=', (int)$search)
            ->select(['id', 'name', 'image', DB::raw("CASE
    WHEN FIND_IN_SET(`id`, '{$selectedIds}') > 0 THEN 1
    ELSE 0
END as active")])
            ->limit($length)
            ->offset($start)
            ->orderBy($orderColumn, $orderDir);

        $items = $items->get();

        return ItemResourceTable::collection($items);
    }

    public function getStylesById(int $id)
    {
        $item = Item::findOrFail($id);
        $styles = $item->styles;
        $stylesResource = StyleResource::collection($styles);
        return $stylesResource;
    }

    public function getCurrentTopicsById(int $id)
    {
        $item = Item::findOrFail($id);
        $topics = $item->topicDetails->makeHidden('pivot');
        $topicIds = $topics->map(function ($item, $key) {
            return $item->topic_id;
        });
        $typeIds = $topics->map(function ($item, $key) {
            return $item->type_id;
        })->unique();
        $topicsCollect = Topic::whereIn('id', $topicIds)->get();
        $typesCollect = Type::whereIn('id', $typeIds)->get();
        $topicsFormated = [];
        foreach ($topics as $topic) {
            if (array_key_exists($topic['topic_id'], $topicsFormated)) {
                $topicsFormated[$topic['topic_id']]['types'][] = [
                    'id' => $topic['type_id'],
                    'name' => $typesCollect->firstWhere('id', $topic['type_id'])->name,
                ];
            } else {
                $topicsFormated[$topic['topic_id']] = [
                    'topic_id' => $topic['topic_id'],
                    'name' => $topicsCollect->firstWhere('id', $topic['topic_id'])->name,
                    'types' => [[
                        'id' => $topic['type_id'],
                        'name' => $typesCollect->firstWhere('id', $topic['type_id'])->name,
                    ]],
                ];
            }
        }
        $topicsFormated = collect($topicsFormated);
        $topicsResource = TopicCurrentForItem::collection($topicsFormated);
        return $topicsResource;

    }
}
