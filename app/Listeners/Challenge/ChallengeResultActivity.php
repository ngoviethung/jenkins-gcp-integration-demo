<?php

namespace App\Listeners\Challenge;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserItem;
use App\Models\Item;
use App\Models\User;
use App\Models\UserChallenge;
use App\Models\UserReward;
use DB;
use App\Repositories\ChallengeRepository;
use App\Services\Reward\RewardService;

class ChallengeResultActivity
{
    /**
     * Create the event listener.
     *
     * @return void
     */

    protected $challenge_repo;

     /**
     * Constructor.
     *
     * @param UserRepository $user_repo  The user repo
     */

    public function __construct(ChallengeRepository $challenge_repo)
    {
        $this->challenge_repo = $challenge_repo;
    }

    /**
     * Handle the event.
     *
     * @param  ChangeStatusJob $event
     * @return void
     */
    public function handle($event)
    {
    
        
        $user_id = $event->user_id;
        $change_id = $event->change_id;
        $challenge_id = $event->challenge_id;
        $my_challenge = $event->my_challenge;
        $changes = [];

        
        $user_challege = UserChallenge::where(['user_id' => $user_id, 'challenge_id' => $challenge_id])->first();
        if(!$user_challege){
            return;
        }
        if($user_challege->claimed != 1){

            $score_voting = $my_challenge['score_voting'];
            $challenge = $this->challenge_repo->find($challenge_id);
            $prizes = json_decode($challenge->prizes, true);

            $reward = new RewardService();
            $changes = $reward->reviceRewardResultChallenge($user_id, $challenge_id, $score_voting, $prizes);

        }

        $user = new User();
        
        if(!empty($changes)){
            $user->service()->updateChange($change_id, $changes);
        }
        
        return;

    }

    


}
