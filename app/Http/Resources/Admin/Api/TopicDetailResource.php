<?php

namespace App\Http\Resources\Admin\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TopicDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $type = $this->type;
        return [
            'index' => $this->id,
            'type' => [
                'id' => $type->id,
                'name' => $type->name,
            ],
            'topic_items' => TopicDetailItemResource::collection($this->topicDetailItems),
        ];
    }
}
