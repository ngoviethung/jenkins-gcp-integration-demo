<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $prizes = json_decode($this->prizes);
        $dress_code = json_decode($this->dress_code);

        $new_prizes = [];
        foreach ($prizes as $value){
            $new_prizes[] = [
                'require_star' => (float)$value->require_star,
                'type' => $value->type,
                'value' => (int)$value->value,
                'item_id' => (int)$value->item_id,
            ];
        }


        $new_dress_code = [];
        foreach ($dress_code as $value){

            $colors = $value->{'colors[]'};
            $collections = $value->{'collections[]'};
            $patterns = $value->{'patterns[]'};
            $materials = $value->{'materials[]'};
            $brands = $value->{'brands[]'};
            $type_id = $value->{'type_id[]'};
            $item_id = $value->{'item_id[]'};

            $colors = array_map('intval', $colors);
            $collections = array_map('intval', $collections);
            $patterns = array_map('intval', $patterns);
            $materials = array_map('intval', $materials);
            $brands = array_map('intval', $brands);
            $type_id = array_map('intval', $type_id);
            $item_id = array_map('intval', $item_id);

            $items = \App\Models\Item::whereIn('id', $item_id)->get(['id', 'type_id'])->pluck('id','type_id')->toArray();
            $new_items = [];
            foreach ($items as $key => $val) {
                $newKey = '_NULL_'.$key.'_NULL_';
                $new_items[$newKey] = $val;
            }
            $new_dress_code[] = [
                'name' => $value->name,
                'filter' => [
                    'colors' => $colors,
                    'collections' => $collections,
                    'patterns' => $patterns,
                    'materials' => $materials,
                    'brands' => $brands,
                    'type_id' => $type_id,
                ],
                'items' => $new_items
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'cover' => asset($this->cover).'bytes',
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'start_time' => strtotime($this->start_time),
            'end_time' => strtotime($this->end_time),
            'tag' => $this->tag,
            'max_unworn_value' => $this->max_unworn_value,
            'entry_reward' => $this->entry_reward,
            'requirement' => $this->requirement,
            'dress_code' => $new_dress_code,
            'prizes' => $new_prizes
        ];
    }
}
