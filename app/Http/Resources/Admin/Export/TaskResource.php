<?php

namespace App\Http\Resources\Admin\Export;

use App\Http\Resources\GroupLevelTask;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $names = json_decode($this->name);
        $name = [];
        foreach ($names as $key => $value) {
            $newKey = explode('_', $key);
            $name[$newKey[1]] = $value;
        }
        $descriptions = json_decode($this->description);
        $description = [];
        foreach ($descriptions as $key => $value) {
            $newKey = explode('_', $key);
            $description[$newKey[1]] = $value;
        }
        $styles = $this->styles;
        $styles = collect($styles)->transform(function ($e) {
            return [
                'id' => $e->id,
                'factor' => $e->pivot->factor
            ];
        });

        $types = $this->types;
//        $types = collect($types)->transform(function ($e) {
//            return [
//                'id' => $e->id,
//                'factor' => $e->pivot->factor
//            ];
//        });

        $types = collect($types)->transform(function ($e) {
            return $e->id;
        });

        return [
            'id' => $this->id,
            'name' => $name,
            'description' => $description,
            "cover" => $this->changeToBytes($this->cover),
            "background" => $this->changeToBytes($this->background),
            "weight" => $this->weight,
            "min_score" => $this->min_score,
//            "reward_coin" => $this->reward_coin,
//            "require_topic" => 'topic_' . $this->topic->id . '.json',
            "require_topic" => $this->topic->id,
//            "country" => $this->topic->country_code,
//            "group_level" => $this->groupleveltask ? GroupLevelTask::make($this->groupleveltask) : null,
            "group_level_id" => $this->group_level_task_id,
            "category_id" => $this->category_id,
            "require_styles" => $styles,
            'filter_types' => $types,
            'in_local' => $this->in_local,
            "price_unit" => $this->currency != null ? $this->currency : 1,
        ];
    }

    protected function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }

        return $fileName;
    }
}
