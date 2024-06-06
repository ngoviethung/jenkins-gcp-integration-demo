<?php

namespace App\Listeners\Item;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Item;
use App\Models\User;

class ItemArchivedActivity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  ChangeStatusJob $event
     * @return void
     */
    public function handle($event)
    {
       
        $arr_item_id = $event->arr_item_id;
        $user_id = $event->user_id;
        $change_id = $event->change_id;
        $arr_total_currency_item = $event->arr_total_currency_item;

        $soft = $arr_total_currency_item['soft'];
        $hard = $arr_total_currency_item['hard'];

        $exp = $soft + $hard;

        if($exp > 0){
            $user = new User();
            $user->service()->upExp($user_id, $exp, $change_id);
            $level_up = $user->service()->checkAndUpLevel($user_id, $change_id);
            $user->service()->downSoft($user_id, $soft, $change_id);
            $user->service()->downHard($user_id, $hard, $change_id);
        }

        return;

    }


}
