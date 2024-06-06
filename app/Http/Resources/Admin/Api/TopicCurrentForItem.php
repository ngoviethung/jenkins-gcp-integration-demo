<?php

namespace App\Http\Resources\Admin\Api;

use App\Models\Topic;
use App\Models\Type;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicCurrentForItem extends JsonResource
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
            'id' => $this['topic_id'],
            'name' => $this['name'],
            'types' => $this['types'],
        ];
    }
}
