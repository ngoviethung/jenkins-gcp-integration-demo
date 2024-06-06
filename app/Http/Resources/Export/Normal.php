<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;
class Normal extends JsonResource
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
                'score' => $e->pivot->score
            ];
        });

        $colors = $this->colors->pluck('id')->toArray();
        $collections = $this->collections->pluck('id')->toArray();
        $patterns = $this->patterns->pluck('id')->toArray();
        $materials = $this->materials->pluck('id')->toArray();

        //$job = DB::connection('mysql2')->table('jobs')->where('item_id', $this->id)->first();

        return [
            'id' => $this->id,
            'thumb' => $this->changeToBytes($this->thumbnail),
            'name' => $this->name,
            'price' => (int)$this->price,
            'price_unit' => $this->currency != null ? $this->currency : 1,
            'image' => [
                'front' => [
                    'pos_x' => $this->image_pos_x,
                    'pos_y' => $this->image_pos_y,
                    'img' => $this->changeToBytes($this->image),
                ],
                'left' => [
                    'pos_x' => $this->left_image_pos_x,
                    'pos_y' => $this->left_image_pos_y,
                    'img' => $this->changeToBytes($this->left_image),
                ],
                'mid' => [
                    'pos_x' => $this->mid_image_pos_x,
                    'pos_y' => $this->mid_image_pos_y,
                    'img' => $this->changeToBytes($this->mid_image),
                ],
                'right' => [
                    'pos_x' => $this->right_image_pos_x,
                    'pos_y' => $this->right_image_pos_y,
                    'img' => $this->changeToBytes($this->right_image),
                ],
                'back' => [
                    'pos_x' => $this->back_image_pos_x,
                    'pos_y' => $this->back_image_pos_y,
                    'img' => $this->changeToBytes($this->back_image)
                ]
            ],
            'colors' => $colors,
            'collections' => $collections,
            'patterns' => $patterns,
            'materials' => $materials,
            'styles' => $styles,
            'level_unlock' => $this->grouplevelitem ? $this->grouplevelitem->level : 0,
            'type_id' => $this->type_id,
        ];
    }



    /*
    private function getColors($job){
        if(!$job){
            return [];
        }
        $reference_id = $job->reference_id;
        $data = DB::connection('mysql2')->table('reference_color')->where('reference_id', $reference_id)
            ->get()->pluck('color_id')->toArray();

        return $data;
    }
    private function getCollections($job){
        if(!$job){
            return [];
        }
        $reference_id = $job->reference_id;
        $data = DB::connection('mysql2')->table('reference_collection')->where('reference_id', $reference_id)
            ->get()->pluck('collection_id')->toArray();

        return $data;
    }
    private function getPatterns($job){
        if(!$job){
            return [];
        }
        $reference_id = $job->reference_id;
        $data = DB::connection('mysql2')->table('reference_pattern')->where('reference_id', $reference_id)
            ->get()->pluck('pattern_id')->toArray();

        return $data;
    }
    private function getMaterials($job){
        if(!$job){
            return [];
        }
        $reference_id = $job->reference_id;
        $data = DB::connection('mysql2')->table('reference_material')->where('reference_id', $reference_id)
            ->get()->pluck('material_id')->toArray();

        return $data;
    }

    */

    private function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }
        return $fileName;
    }
}
