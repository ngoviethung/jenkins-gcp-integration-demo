<?php

namespace App\Http\Resources\Admin\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TopicDetailItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->item_id,
            'name' => $this->item->name
        ];
    }
}
