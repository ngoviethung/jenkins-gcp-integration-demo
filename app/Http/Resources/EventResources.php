<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResources extends JsonResource
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
            'event_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'topic_id' => $this->topic_id,
            'min_score' => $this->min_score,
            'max_score' => $this->max_score,
            'banner' => $this->banner . '.bytes',
            'icon' => $this->icon . '.bytes',
            'thumb' => $this->thumb . '.bytes',
            'bg_color' => $this->color,
            'level_unlock' => $this->level_unlock,
            'unlock_by_gem' => $this->unlock_by_gem,
            'unlock_by_ticket' => $this->unlock_by_ticket,
            'entry_fee_gem' => $this->entry_fee_gem,
            'countries' => $this->countries()->pluck('country_code')->toArray(),
            "start_date" => strtotime($this->start_date),
            "end_date" =>  strtotime($this->end_date),
            "vip" =>  $this->vip,
            "rewards" => json_decode($this->rewards),
            "unlock_by_ads" => $this->unlock_by_ads,
            "entry_by_ads" => $this->entry_by_ads
        ];
    }
}
