<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Resources\Admin\Api\TypeResource;
use App\Models\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cache;
class TypeController extends Controller
{
    public function getTypes()
    {
        $types = Type::all();
        $typeResource = TypeResource::collection($types);
        return $typeResource;
    }

    public function typeOptions(Request $request)
    {
        $term = $request->input('term');
        $options = Type::where('name', 'like', '%' . $term . '%')->get()->pluck('name', 'id');
        return $options;
    }

    public function getChildrenType(Request $request){

        $parent_id = $request->parent_id;
        $data = Cache::remember('get_children_type_'.$parent_id, 60, function() use($parent_id) {
            $types = Type::with('position:id,code')->where('parent_id', $parent_id)->get();
            return $types;

        });

        return response()->json(['data' => $data]);
    }

    public function getPositionByType(Request $request){

        $type_code = $request->type_code;
        $data = Cache::remember('get_position_by_type_'.$type_code, 60, function() use($type_code) {
            $type = Type::with('position:id,code')->where('code', $type_code)->first();
            if($type->position){
                return $type->position->code;
            }
            return null;
        });

        return response()->json(['data' => $data]);
    }
}
