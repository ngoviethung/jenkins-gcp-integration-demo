<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Resources\Json\JsonResource;

class Skin extends JsonResource
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
            'body' => $this->changeToBytes($this->body_image),
            'left_hand' => $this->changeToBytes($this->left_hand_image),
            'right_hand' => $this->changeToBytes($this->right_hand_image),
        ];
    }

    private function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }
        return $fileName;
    }
}
