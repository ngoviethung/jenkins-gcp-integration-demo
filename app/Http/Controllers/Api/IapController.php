<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserBuyItem;
use App\Http\Resources\Api\UserResource;
use App\Models\Iap;
use App\Models\Item;
use App\Models\UserReward;
use Cache;
use Illuminate\Support\Facades\Validator;
use Log;
use DB;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Exception;
class IapController extends AppBaseController
{



    public function addCurrency(Request $request){

        $user_id = $request->get('user_id');
        $type = $request->type;
        $value = (int)$request->value;

        if($type == 'soft'){
            User::where('_id', $user_id)->update(['soft' => $value]);
        }
        if($type == 'hard'){
            User::where('_id', $user_id)->update(['hard' => $value]);
        }

        $data = [
            'user_id' => $user_id,
            'type' => $type,
            'value' => $value
        ];

        return $this->sendResponse($data);

        
    }
    public function getIap(){
        try {
            $iap = Iap::get();
            return $this->sendResponse($iap);

        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }
    }
    public function buyIap(Request $request)
    {
        try {
            $user_id = $request->get('user_id');
            $validator = Validator::make($request->all(), [
                'product_id' => 'required',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['product_id'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }

            $product_id = $request->product_id;
            $iap = Iap::where('product_id', $product_id)->first();

            if(!$iap){
                return $this->sendError('param_invalid', 'product_id is invalid.', 404, 'Error');
            }
            $type = $iap->type;
            $value = $iap->value;

            User::where('_id', $user_id)->update(["$type" => $value]);
            $user = User::find($user_id);

            $data = new UserResource($user);

            return $this->sendResponse($data);
        } catch (exception $e) {

            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }

}

