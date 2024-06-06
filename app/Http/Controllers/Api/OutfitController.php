<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\Outfit;
use App\Models\Template;
use Illuminate\Http\Request;
use Exception;

class OutfitController
{
    public function createFromTemplate(Request $request)
    {
        try {
            $template_id = $request->template_id;
            $admin_id = $request->admin_id;

            if(!$template_id or !$admin_id){

                throw new Exception('Missing paramter', 66);
            }
            $template = Template::find($template_id)->toArray();

            unset($template['id']);
            unset($template['admin_id']);
            unset($template['created_at']);
            unset($template['updated_at']);
            unset($template['item_checking_id']);

            $template['admin_id'] = $admin_id;
            $template['template_id'] = $template_id;
            $template['category'] = 1;
            $template['created_at'] = date("Y-m-d H:i:s");

            Outfit::insert($template);

            return 1;

        } catch (Exception $exception) {
            return 0;
        }
    }


}
