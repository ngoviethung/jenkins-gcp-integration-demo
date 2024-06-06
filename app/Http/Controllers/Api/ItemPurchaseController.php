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
use App\Repositories\ItemRepository;


class ItemPurchaseController extends AppBaseController
{

    public $item_repo;

    public function __construct(ItemRepository $item_repo){

        $this->item_repo = $item_repo;

    }

    public function resetUnwornItem(Request $request, Item $item, User $user)
    {
        try {
            $user_id = $request->get('user_id');
            $validator = Validator::make($request->all(), [
                'item_id' => 'required|json',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['item_id'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $item_id = (int)$request->item_id;
            
            $item = $this->item_repo->find($item_id);
            if(!$item){
                return $this->sendError('param_invalid', 'item_id is not exist', 404, 'Error');
            }
            $arr_item_id[] = $item_id;


            $arr_total_currency_item = $item->service()->checkAndGetCurrencyEnough($user_id, $arr_item_id);
            if(empty($arr_total_currency_item)){
                return $this->sendError('not_enough_currency', 'Not enough currency', 404, 'Not enough currency');
            }

            $items_reseted = $item->service()->resetUnwornItem($user_id, $item_id, $arr_total_currency_item);


            $changes = [];
            if($items_reseted > 0){
                $change_id = $user->service()->createChange($user_id);
                event(new ItemWasArchived($user_id, $arr_item_id, $arr_total_currency_item, $change_id));
                $changes = $user->service()->getChange($change_id);
            }

            $user_info = $user->service()->getUserCurrencyAndExp($user_id);
            $data = [
                'changes' => $changes,
                'user_info' => $user_info
            ];

            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }

 
    public function buyItem(Request $request, Item $item, User $user)
    {
        try {
            $user_id = $request->get('user_id');
            $changes = [];
            $change_id = $user->service()->createChange($user_id);
            /*
            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|json',
                'hair_color_id' => 'required'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['item_ids', 'hair_color_id'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            */
            $items_added = 0;

            $hair_color_id = (int)$request->hair_color_id;
            $arr_item_id = json_decode($request->item_ids);

            if(!empty($arr_item_id)){
                $check = $this->item_repo->countWhereIn('id', $arr_item_id);
                if($check < count($arr_item_id)){
                    return $this->sendError('param_invalid', 'Have an item is not exist', 404, 'Error');
                }
                
                $arr_total_currency_item = $item->service()->checkAndGetCurrencyEnough($user_id, $arr_item_id);
                if(empty($arr_total_currency_item)){
                    return $this->sendError('not_enough_currency', 'Not enough currency', 404, 'Not enough currency');
                }

                $items_added = $item->service()->buyItems($user_id, $arr_item_id, $change_id);

                if($items_added > 0){
                    event(new ItemWasArchived($user_id, $arr_item_id, $arr_total_currency_item, $change_id));
                    $changes = $user->service()->getChange($change_id);
                }
            }

            if($hair_color_id){
                
                $k = substr($hair_color_id, -1);
                $item_id = ($hair_color_id - $k) / 10000;
                $check = $this->item_repo->countWhere('id', $item_id);
                if($check == 0){
                    return $this->sendError('param_invalid', 'item is not exist', 404, 'Error');
                }
                $arr_total_currency_item = $item->service()->checkAndGetCurrencyHairEnough($user_id, $item_id, $k);
                if(empty($arr_total_currency_item)){
                    return $this->sendError('not_enough_currency', 'Not enough currency', 404, 'Not enough currency');
                }

                $items_added = $item->service()->buyHairItems($user_id, $hair_color_id, $change_id);

                if($items_added > 0){
                    event(new ItemWasArchived($user_id, $arr_item_id, $arr_total_currency_item, $change_id));
                    $changes = $user->service()->getChange($change_id);
                }
            }

            $user_info = $user->service()->getUserCurrencyAndExp($user_id);
            $data = [
                'changes' => $changes,
                'user_info' => $user_info
            ];

            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }


}

