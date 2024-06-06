<?php

namespace App\Listeners\Vote;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserItem;
use App\Models\Item;
use App\Models\User;
use App\Models\UserChallenge;
use App\Models\Challenge;
use App\Services\User\UserService;
use App\Services\Reward\RewardService;

class VoteSubmitedActivity
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
    
        $current_streak_vote = $event->current_streak_vote;
        $user_id = $event->user_id;
        $change_id = $event->change_id;
        
        $user_service = new UserService();
        $reward_service = new RewardService();

        $changes = $reward_service->reviceRewardStreakVoteUp($user_id, $current_streak_vote);
        if(!empty($changes)){
            $user->service()->updateChange($change_id, $changes);
        }
        $user_service->upCurrentStreakVote($user_id);

        return;

    }


}
