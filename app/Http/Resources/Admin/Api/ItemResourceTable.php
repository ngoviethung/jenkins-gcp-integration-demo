<?php

namespace App\Http\Resources\Admin\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResourceTable extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $selectedIds = $request->get('selectedIds');
        if($selectedIds) {
            $selectedIds = explode(',', $selectedIds);
        } else {
            $selectedIds = [];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => url($this->image),
            'active' => in_array($this->id,$selectedIds) ? 1 : 0
        ];
    }
}
