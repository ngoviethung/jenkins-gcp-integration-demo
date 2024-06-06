<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupLevelTask extends JsonResource
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
            'from' => $this->from_level,
            'to' => $this->to_level,
            'winProbility' => $this->win_probility,
            'winWeight' => $this->win_weight ? $this->win_weight : 0,
            'randomFactor' => $this->random_factor,
            'reward' => $this->reward,
            'currency' => $this->currency
        ];
    }
}
