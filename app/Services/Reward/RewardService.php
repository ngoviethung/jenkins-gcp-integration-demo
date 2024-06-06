<?php

namespace App\Services\Reward;

use Log;
use Exception;

use App\Models\User;
use App\Models\Item;
use App\Models\UserItem;
use App\Models\UserReward;
use App\Models\UserChallenge;
use App\Models\Level;
use App\Models\VotingReward;

class RewardService
{


    public function __construct()
    {
        
    }


    public function reviceRewardStreakVoteUp($user_id, $streak){

        try{
            $type = 'reward_streak_vote_up';

            $changes = [];
            $record = VotingReward::where('streak', $streak)->first();
            $prizes = $record->rewards;

            if(empty($prizes)){
                return 0;
            }

            foreach($prizes as $prize){
                
                if($prize['type'] == 'ITEM'){
                    $item_id = $prize['item_id'];
                
                    //recive
                    UserItem::firstOrCreate(['user_id' => $user_id, 'type' => $type, 'item_id' => $item_id]);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'level' => $level, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'REWARD_ITEM',
                        'value' => $item_id
                    ];
                }elseif($prize['type'] == 'HARD'){
            
                    $value = $prize['value'];
                    //recive
                    User::increment('soft', (int)$value);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'level' => $level, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'ADD_HARD',
                        'value' => $value
                    ];

                }elseif($prize['type'] == 'SOFT'){
                
                    $value = $prize['value'];
                    //recive
                    User::increment('soft', (int)$value);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'level' => $level, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'ADD_SOFT',
                        'value' => $value
                    ];
                }
            }

            return $changes;
        }catch(exception $e){
            return 0;
        }
        
    }

    public function reviceRewardLevelUp($user_id, $level){

        try{
            $type = 'reward_level_up';

            $changes = [];
            $record = Level::where('level', $level)->first();
            $prizes = json_decode($record->rewards, true);

            if(empty($prizes)){
                return 0;
            }

            foreach($prizes as $prize){
                
                if($prize['type'] == 'ITEM'){
                    $item_id = $prize['item_id'];
                
                    //recive
                    UserItem::firstOrCreate(['user_id' => $user_id, 'type' => $type, 'item_id' => $item_id]);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'level' => $level, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'REWARD_ITEM',
                        'value' => $item_id
                    ];
                }elseif($prize['type'] == 'HARD'){
            
                    $value = $prize['value'];
                    //recive
                    User::increment('soft', (int)$value);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'level' => $level, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'ADD_HARD',
                        'value' => $value
                    ];

                }elseif($prize['type'] == 'SOFT'){
                
                    $value = $prize['value'];
                    //recive
                    User::increment('soft', (int)$value);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'level' => $level, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'ADD_SOFT',
                        'value' => $value
                    ];
                }
            }

            return $changes;
        }catch(exception $e){
            return 0;
        }
        
    }

    public function reviceRewardResultChallenge($user_id, $challenge_id, $score_voting, $prizes){

        $changes = [];
        $type = 'reward_challenge';

        foreach($prizes as $prize){
            
            if($score_voting >= $prize['require_star']){
               
                if($prize['type'] == 'ITEM'){
                    $item_id = $prize['item_id'];
                   
                    //recive
                    UserItem::firstOrCreate(['user_id' => $user_id, 'type' => $type, 'item_id' => $item_id]);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'challenge_id' => $challenge_id, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'REWARD_ITEM',
                        'value' => $item_id
                    ];
                }elseif($prize['type'] == 'HARD'){
             
                    $value = $prize['value'];
                    //recive
                    User::increment('soft', (int)$value);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'challenge_id' => $challenge_id, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'ADD_HARD',
                        'value' => $value
                    ];

                }elseif($prize['type'] == 'SOFT'){
                   
                    $value = $prize['value'];
                    //recive
                    User::increment('soft', (int)$value);
                    //log
                    UserReward::create(['user_id' => $user_id, 'type' => $type, 'challenge_id' => $challenge_id, 'reward' => $prize]);
                    $changes[] = [
                        'type' => 'ADD_SOFT',
                        'value' => $value
                    ];
                }
            }
        }

        UserChallenge::where(['user_id' => $user_id, 'challenge_id' => $challenge_id])->update(['claimed' => 1]);

        return $changes;
    }



}
