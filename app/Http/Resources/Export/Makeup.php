<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;

class Makeup extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'thumb' => $this->changeToBytes($this->thumbnail),
            'pos_x' => $this->image_pos_x,
            'pos_y' => $this->image_pos_y,
            'for_skins' => $this->getSkins($this->makeup_items),
            'level_unlock' => $this->grouplevelitem ? $this->grouplevelitem->level : 0,

        ];
    }

    private function getSkins($makeup_items)
    {
        $data = [];
        $makeup_items = json_decode($makeup_items);
        foreach ($makeup_items[0] as $key => $value){
            $skin_id = $this->getSkinId($key); //6
            $skin_ref = DB::connection('mysql2')->table('skins')->find($skin_id); //find code
            $skin = DB::table('skins')->where('code', $skin_ref->code)->first();
            $skin_id = $skin->id;

            $data['_skin_id_'.$skin_id.'_skin_id_'] = $this->changeToBytes($value);
        }

        return $data;
    }

    private function getSkinId($string)
    {
        $parts = explode('_skin_', $string);
        $number = end($parts);

        return $number;
    }

    private function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }
        return $fileName;
    }
}
