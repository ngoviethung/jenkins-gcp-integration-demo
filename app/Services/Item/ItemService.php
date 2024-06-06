<?php

namespace App\Services\Item;

use Log;
use Exception;

use App\Models\User;
use App\Models\Item;
use App\Models\UserItem;

class ItemService
{


    public function __construct()
    {
        
    }

    public function checkAndGetCurrencyHairEnough($user_id, $item_id, $k){

       
        $item = Item::where('id', $item_id)->first();
        $hair_colors = json_decode($item->hair_colors);
        $color =  $hair_colors[$k];
        $price = $color->price != '' ? $color->price : 0;
        $currency = $color->currency;

        $user = User::find($user_id);
        $data = [
            'soft' => 0,
            'hard' => 0
        ];
        if($currency == 1){
            $user_has_soft = $user->soft;
            if($user_has_soft < $price){
                return [];
            }
            $data['soft'] =  $price;
        }
        if($currency == 2){
            $user_has_hard = $user->hard;
            if($user_has_hard < $price){
                return [];
            }
            $data['hard'] =  $price;
        }

        return $data;

    }

    public function checkAndGetCurrencyEnough($user_id, $arr_item_id){

        $total_price_soft = Item::whereIn('id', $arr_item_id)->where('currency', 1)->sum('price');
        $total_price_hard = Item::whereIn('id', $arr_item_id)->where('currency', 2)->sum('price');
        
        $user = User::find($user_id);
        $user_has_soft = $user->soft;
        $user_has_hard = $user->hard;

        if($user_has_soft < $total_price_soft or $user_has_hard < $total_price_hard){
            return [];
        }

        $data = [
            'soft' => $total_price_soft,
            'hard' => $total_price_hard
        ];

        return $data;

    }

    public function resetUnwornItem($user_id, $item_id, $arr_total){

        $changes = [];
        $where = [
            'user_id' => $user_id,
            'item_id' => (int)$item_id,
        ];
        $k = 0;
        $check = Useritem::where($where)->where('worn', 1)->count();
        if($check > 0){
            UserItem::where($where)->update(['worn' => 0]);
            $k++;
        }

        return $k;
        
    }

    public function buyItems($user_id, $arr_item_id, $change_id){

        $k = 0;
        $changes = [];
        foreach($arr_item_id as $item_id){
            $where = [
                'user_id' => $user_id,
                'item_id' => $item_id,
            ];
            $data = [
                'user_id' => $user_id,
                'item_id' => $item_id,
                'type' => 'buy'
            ];

            $check = Useritem::where($where)->count();
            
            if($check == 0){
                UserItem::create($data);
                $changes[] = [
                    'type' => 'BUY_ITEM_SUCCESS',
                    'value' => (int)$item_id
                ];
                
                $k++;
            }
            
        }
    
        if(!empty($changes)){
            $user = new User();
            $user->service()->updateChange($change_id, $changes);
        }

        return $k;
        
    }

    public function buyHairItems($user_id, $hair_color_id, $change_id){

        $k = 0;
        $changes = [];

        $where = [
            'user_id' => $user_id,
            'item_id' => $hair_color_id,
        ];
        $data = [
            'user_id' => $user_id,
            'item_id' => $hair_color_id,
            'type' => 'buy',
            'is_hair' => 1
        ];

        $check = Useritem::where($where)->count();
        
        if($check == 0){
            UserItem::create($data);
            $changes[] = [
                'type' => 'BUY_ITEM_SUCCESS',
                'value' => $hair_color_id
            ];
            
            $k++;
        }
    
        if(!empty($changes)){
            $user = new User();
            $user->service()->updateChange($change_id, $changes);
        }

        return $k;
        
    }

    

}
