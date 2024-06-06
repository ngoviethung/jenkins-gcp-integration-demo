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
use App\Events\Item\ItemWasArchived;

class ActivityController extends AppBaseController
{

    public function reward(Request $request)
    {
        try {
            $user_id = $request->get('user_id');

            $input_data = $request->getContent();

            $source = json_decode($input_data );
            $package_id = $source->package_id;
            $check = UserReward::where(['user_id' => $user_id, 'package_id' => $package_id])->count();

            if($check > 0){
                return $this->sendResponse([]);
            }
            UserReward::create(['user_id' => $user_id, 'package_id' => $package_id, 'source' => $source]);
            //detail
            $rewards = $source->rewards;
            $soft = $hard = 0;
            $items = [];
            foreach ($rewards as $reward){
                if($reward->type == 'SOFT'){
                    $soft = $reward->value;
                }else if($reward->type == 'HARD'){
                    $hard = $reward->value;
                }else if($reward->type == 'ITEM'){
                    $items = $reward->value;
                }
            }
            if($soft){
                User::where('_id', $user_id)->increment('soft', $soft);
            }
            if($hard){
                User::where('_id', $user_id)->increment('hard', $hard);
            }
            if(!empty($items)){
                foreach ($items as $item_id){
                    $data = [
                        'user_id' => $user_id,
                        'item_id' => $item_id,
                        'type' => 'reward',
                    ];
                    UserBuyItem::firstOrCreate($data);
                }
            }
            return $this->sendResponse([]);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }



}

