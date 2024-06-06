<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Resources\Json\JsonResource;

class Type extends JsonResource
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
            'parent' => $this->parent_id,
            'order_ui' => $this->order_num,
            'order_layer' => $this->order,
            'position_id' => $this->position_id,
            'icon' => $this->changeToBytes($this->icon),
            //'category' => $this->category
        ];
    }

    private function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }
        return $fileName;
    }

}
