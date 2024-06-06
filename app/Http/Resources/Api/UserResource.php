<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            '_id' => $this->_id,
            'name' => $this->name,
            'hard' => $this->hard,
            'soft' => $this->soft,
            'ticket' => $this->ticket
        ];
    }
}
