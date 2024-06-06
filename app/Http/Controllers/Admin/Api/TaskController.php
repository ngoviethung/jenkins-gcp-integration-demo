<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Resources\Admin\Api\StyleForTaskResource;
use App\Http\Resources\Admin\Api\TypeForTaskResource;
use App\Models\Task;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function getStylesById(int $id)
    {
        $task = Task::findOrFail($id);
        $styles = $task->styles;
        $stylesResource = StyleForTaskResource::collection($styles);
        return $stylesResource;
    }

    public function getTypesById(int $id)
    {
        $task = Task::findOrFail($id);
        $types = $task->types;
        $typesResource = TypeForTaskResource::collection($types);
        return $typesResource;
    }
}
