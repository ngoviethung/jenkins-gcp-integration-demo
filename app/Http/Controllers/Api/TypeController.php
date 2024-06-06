<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use Exception;

class TypeController
{
    public function createType(Request $request)
    {
        try {
            $name = $request->name;
            //$category = $request->category;

            if(!$name){
                throw new Exception('Missing paramter', 66);
            }
            Type::firstOrCreate(['name' => $name]);

            return response()->json(['code' => 200, 'data' => $name]);

        } catch (Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    public function updateType(Request $request)
    {
        try {
            $old_name = $request->old_name;
            $name = $request->name;
            //$category = $request->category;

            if(!$name or !$old_name ){
                throw new Exception('Missing paramter', 66);
            }
            Type::updateOrCreate(['name' => $old_name], ['name' => $name]);

            return response()->json(['code' => 200, 'data' => $name]);

        } catch (Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    public function deleteType(Request $request)
    {
        try {

            $name = $request->name;
            if(!$name){
                throw new Exception('Missing paramter', 66);
            }
            Type::where('name', $name)->delete();

            return response()->json(['code' => 200, 'data' => $name]);

        } catch (Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

}
