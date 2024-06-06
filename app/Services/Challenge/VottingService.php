<?php

namespace App\Services\Challenge;

use Log;
use Exception;

use App\Models\UserChallenge;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Config;
use App\Models\UserVoteChallenge;
use App\Models\VotingReward;
use App\Services\Challenge\ChallengeService;

use Carbon\Carbon;

class VottingService
{

    public function __construct()
    {
        
    }

    public function getRewardVote($user_id, $current_streak_vote){

        $voting_reward = VotingReward::where('streak', $current_streak_vote)->first();
        $voting_rewards_default = Config::first()->voting_rewards_default;

        if(!$voting_reward){
            return $voting_rewards_default;
        }
        
        $rewards = $voting_reward->rewards;
        if(!$rewards){
            $data[] = [
                'type' => 'SOFT',
                'value' => 2
            ];
            return $data;
        }
        $data = [];
        foreach($rewards as $reward){
            $data[] = [
                'type' => $reward['type'],
                'value' => $reward['type'] == 'ITEM' ? (int)$reward['item_id'] : (int)$reward['value']
            ];
        }

        return $data;
    }

    public function getRandomChallengeIdVotting(){

        $time_voting = Config::first()->time_voting;
        $challenge_service = new ChallengeService();
    
        $data = [];
        $challenges = Challenge::get(['id', 'start_time', 'end_time']);

        foreach($challenges as $challenge){
            $status = $challenge_service->getStatus($challenge->start_time, $challenge->end_time, $time_voting);
            if($status == 'voting'){
                $data[] = $challenge->id;
            }
        }

        $challenge_id = null;
        if(!empty($data)){
            $challenge_id = $data[array_rand($data)];
        }
        
        return $challenge_id;

    }


    public function getChallengeInfoVotting($user_id, $challenge_id, $current_streak_vote){

        $challenge = Challenge::find($challenge_id);
   
        $data = [
            '_id' => $challenge->_id,
            'name' => $challenge->name,
            'cover' => $challenge->cover. '.bytes',
            'background' => $challenge->background. '.bytes',
            'short_description' => $challenge->short_description,
            'long_description' => $challenge->long_description,
            'requirement' => $challenge->requirement,
            'current_streak_vote' => $current_streak_vote,
        ];

        return $data;

    }

    public function getNextVote($user_id, $challenge_id, $arr_id_just_votted){
        //$user_challenge_id danh sach cac id vua duoc get vote, can loai no ra khi get moi'
       
        $arr_id_votted = [];
        //lay danh sach cac id ma user do da vote roi de loai ra
        $arr_user_challenge_id_voted = UserVoteChallenge::where(['user_id' => $user_id, 'challenge_id' => $challenge_id])
        ->get()->pluck('user_challenge_id')->toArray();
        if($arr_id_just_votted !== null){
            //them id vua vote vao danh sach nay
            $arr_id_votted = array_merge($arr_user_challenge_id_voted, $arr_id_just_votted);
        }

        //lay vote tiep theo
        $records = UserChallenge::where('user_id', '!=', $user_id)
            ->where('challenge_id', $challenge_id)
            ->whereNotIn('_id', $arr_id_votted)->orderBy('take_vote', 'ASC')->take(12)->get();

        $data = [];
        
        $arr_id_take_vote = [];
        foreach ($records as $index => $row) {
            $pairIndex = floor($index / 2); // Tính chỉ số của cặp hiện tại
            // Khởi tạo mảng con nếu chưa tồn tại
            if (!isset($data[$pairIndex])) {
                $data[$pairIndex] = [];
            }
        
            $data[$pairIndex][] = [
                '_id' => $row->_id,
                'image' => $row->image . '.bytes',
                'total_result' => 3.75,
                'user_data' => $this->getUserData($row->user_id)
            ];

            $arr_id_take_vote[] = $row->_id;
        }
        
        // Đảm bảo chỉ có 3 cặp
        $data = array_slice($data, 0, 3);
        UserChallenge::whereIn('_id', $arr_id_take_vote)->increment('take_vote');

        return $data;

    }

    private function getUserData($user_id){
        $user = User::find($user_id);
        $data = [
            'name' => $user->name,
            'exp' => $user->exp,
            'avatar' => $user->avatar,
        ];
        return $data;
    }

}
