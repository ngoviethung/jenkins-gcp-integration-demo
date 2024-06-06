<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Resources\Json\JsonResource;

class Hair extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $styles = $this->styles;
        $styles = collect($styles)->transform(function ($e) {
            return [
                'id' => $e->id,
                'factor' => $e->pivot->factor
            ];
        });

        return [
            'id' => $this->id,
            'thumb' => $this->changeToBytes($this->thumbnail),
            'name' => $this->name,
            'price' => (int)$this->price,
            'price_unit' => $this->currency != null ? $this->currency : 1,
            'coordinates' => [
                'front' => [
                    'pos_x' => $this->image_pos_x,
                    'pos_y' => $this->image_pos_y,
                ],
                'mid' => [
                    'pos_x' => $this->mid_image_pos_x,
                    'pos_y' => $this->mid_image_pos_y,
                ],
                'back' => [
                    'pos_x' => $this->back_image_pos_x,
                    'pos_y' => $this->back_image_pos_y,
                ]
            ],
            'colors' => $this->getColors($this->id, $this->hair_items,  $this->hair_colors),
            'styles' => $styles,
            'level_unlock' => $this->grouplevelitem ? $this->grouplevelitem->level : 0,
            'type_id' => $this->type_id,
        ];
    }

    private function getColors($id, $hair_items, $hair_colors)
    {

        $arr_price_unit = ['SOFT', 'HARD', 'AD'];
        $price = 0;
        $price_unit = 'SOFT';
        $hair_colors = json_decode($hair_colors);
        
        $data = [];
        $hair_items = json_decode($hair_items);
        foreach ($hair_items as $k => $hair){
            if($hair_colors){
                if(isset($hair_colors[$k]->price)){
                    $price = $hair_colors[$k]->price != '' ? (int)$hair_colors[$k]->price : 0;
                }
                if(isset($hair_colors[$k]->currency)){
                    $currency = (int)$hair_colors[$k]->currency;
                    $price_unit = $arr_price_unit[$currency - 1];
                }
            }

            $data[] = [
                'id' => $id * 10000 + $k,
                'name' => $hair->name,
                'thumb' => $this->changeToBytes($hair->thumbnail),
                'front' => $this->changeToBytes($hair->image),
                'mid' => $this->changeToBytes($hair->mid_image),
                'back' => $this->changeToBytes($hair->back_image),
                'price' => $price,
                'price_unit' => $price_unit
            ];
        }
        return $data;
    }

    private function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }
        return $fileName;
    }
}
