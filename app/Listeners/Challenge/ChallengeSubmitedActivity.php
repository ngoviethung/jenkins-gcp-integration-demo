<?php

namespace App\Listeners\Challenge;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserItem;
use App\Models\Item;
use App\Models\User;
use App\Models\UserChallenge;
use App\Models\Challenge;

class ChallengeSubmitedActivity
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
    
        $data = $event->data;
        $user_id = $event->user_id;
        $change_id = $event->change_id;
        
        $data = json_decode($data, true);
        $challenge_id = $data['challenge_id'];
        $challenge = Challenge::find($challenge_id);

        $this->updateScoreUnworn($user_id, $challenge, $data);

        $entry_reward = (int)$challenge->entry_reward;
        if($entry_reward  > 0){
            $user = new User();
            $user->service()->upSoft($user_id, $entry_reward, $change_id);
        }

        return;

    }

    private function updateScoreUnworn($user_id, $challenge, $data){

        $challenge_id = $challenge->id;

        $arr_item_id = $data['list_item'];
        $arr_item_id[] = $data['makeup'];
        $arr_item_id[] = $data['hair']['item_id'];

    
        $arr_item_id_unworn = UserItem::whereIn('item_id', $arr_item_id)->where('worn', '!=', 1)->get(['item_id'])->pluck('item_id')->toArray();
        $unworn_value = Item::whereIn('id', $arr_item_id_unworn)->sum('price');
        $max_unworn_value = $challenge->max_unworn_value;
        $score_unworn = min(1, $unworn_value / $max_unworn_value);

        UserChallenge::where(['user_id' => $user_id, 'challenge_id' => $challenge_id])->update(['score_unworn' => $score_unworn]);
        $this->updateUnworn($user_id, $arr_item_id_unworn);

        return 1;
    }

    private function updateUnworn($user_id, $arr_item_id_unworn){

        UserItem::where('user_id', $user_id)->whereIn('item_id', $arr_item_id_unworn)->update(['worn' => 1]);

        return 1;
    }

}
