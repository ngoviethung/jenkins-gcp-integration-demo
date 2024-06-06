<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Style;
use Illuminate\Http\Request;
use Exception;


class StyleController
{
    public function createStyle(Request $request)
    {
        try {
            $name = $request->name;
            if(!$name){
                throw new Exception('Missing paramter', 66);
            }
            Style::firstOrCreate(['name' => $name]);
            return response()->json(['code' => 200, 'data' => $name]);

        } catch (Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    public function updateStyle(Request $request)
    {
        try {
            $old_name = $request->old_name;
            $name = $request->name;
            if(!$name or !$old_name){
                throw new Exception('Missing paramter', 66);
            }

            Style::updateOrCreate(['name' => $old_name], ['name' => $name]);

            return response()->json(['code' => 200, 'data' => $name]);

        } catch (Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    public function deleteStyle(Request $request)
    {
        try {

            $name = $request->name;
            if(!$name){
                throw new Exception('Missing paramter', 66);
            }
            Style::where('name', $name)->delete();

            return response()->json(['code' => 200, 'data' => $name]);

        } catch (Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

}
