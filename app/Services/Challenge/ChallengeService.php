<?php

namespace App\Services\Challenge;

use Log;
use Exception;

use App\Models\UserChallenge;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Config;
use Carbon\Carbon;

class ChallengeService
{

    public function __construct()
    {
        
    }

    private function getUserDataLeaderBoard($user_id){
        $user = User::find($user_id);
        $data = [
            'user_id' => $user->_id,
            'name' => $user->name,
            'exp' => $user->exp,
            'avatar' => $user->avatar,
        ];
        return $data;
    }

    public function getLeaderBoard($challenge_id){
        $users_challenge = UserChallenge::where('challenge_id', $challenge_id)->orderBy('vote', 'DESC')->take(10)->get();
        
        $data = [];
        foreach($users_challenge as $user_challenge){
            $input_data = $user_challenge->input_data;
            $item_submitted = $input_data['list_item'];

            $data[] = [
                'user_data' => $this->getUserDataLeaderBoard($user_challenge->user_id),
                'likes' => 1000,
                'total_result' => 1000,
                'image' => $user_challenge->image.'.bytes',
                'item_submitted' => $item_submitted,
            ];
        }
        return $data;
    }

    public function getResultChallengeByUser($user_id, $challenge_id){
        
        $user_challenge = UserChallenge::where('challenge_id', $challenge_id)->where('user_id', $user_id)->first();

        $data = [];
        if($user_challenge){
            $image = $user_challenge->image.'.bytes';
            $data = [
                'image' => $image,
                'score_voting' => 4.5,
                'score_unworn' => $user_challenge->score_unworn
            ];
        }
        
        return $data;
    }



    public function getChallengeSubmitted($user_id){


        $user_challenge_id = UserChallenge::where('user_id', $user_id)->whereNotNull('image')
        ->orderBy('start_time')->get(['challenge_id', 'claimed']);

        $arr_challenge_id = $user_challenge_id->pluck('challenge_id')->toArray();
        $arr_claimed = $user_challenge_id->pluck('claimed', 'challenge_id')->toArray();
        $arr_score_unworn = $user_challenge_id->pluck('score_unworn', 'challenge_id')->toArray();


        $challenges = Challenge::whereIn('_id', $arr_challenge_id)->get();
        $data = [];
        foreach($challenges as $challenge){
            $claimed = $arr_claimed[$challenge->id] == 1 ? true : false;
            $score_unworn = isset($arr_score_unworn[$challenge->id]) ? $arr_score_unworn[$challenge->id] : 0;
            $data[] = [
                '_id' => $challenge->_id,
                'name' => $challenge->name,
                'cover' => $challenge->cover.'.bytes',
                'background' => $challenge->background.'.bytes',
                'end_time' => strtotime($challenge->end_time),
                'claimed' => $claimed,
                'score_voting' => 0,
                'score_unworn' => $score_unworn
            ];
        }

        return $data;
        
    }


    public function getChallenges($startDate, $endDate){

        $challenges = Challenge::whereBetween('start_time', [$startDate, $endDate])
                            ->orderBy('start_time', 'DESC')
                            ->get();
        $data = $this->challengeResource($challenges);

        return $data;
            
    }


    private function challengeResource($challenges){

        $time_voting = Config::first()->time_voting;
        $result = [];
        foreach ($challenges as $challenge){
            $prizes = json_decode($challenge->prizes);
            $dress_code = json_decode($challenge->dress_code);

            $new_prizes = [];
            foreach ($prizes as $value){
                $new_prizes[] = [
                    'require_star' => (float)$value->require_star,
                    'type' => $value->type,
                    'value' => (int)$value->value,
                    'item_id' => (int)$value->item_id,
                ];
            }

            $new_dress_code = [];
            foreach ($dress_code as $value){

                $colors = $value->{'colors[]'};
                $collections = $value->{'collections[]'};
                $patterns = $value->{'patterns[]'};
                $materials = $value->{'materials[]'};
                $brands = $value->{'brands[]'};
                $type_id = $value->{'type_id[]'};
                $item_id = $value->{'item_id[]'};

                $colors = array_map('intval', $colors);
                $collections = array_map('intval', $collections);
                $patterns = array_map('intval', $patterns);
                $materials = array_map('intval', $materials);
                $brands = array_map('intval', $brands);
                $type_id = array_map('intval', $type_id);
                $item_id = array_map('intval', $item_id);

                /*
                $items = \App\Models\Item::whereIn('id', $item_id)->get(['id', 'type_id'])->pluck('id','type_id')->toArray();
                
                $new_items = [];
                foreach ($items as $key => $val) {
                    $newKey = (string)$key;
                    $new_items[$newKey] = $val;
                }
                */
                $new_dress_code[] = [
                    'name' => $value->name,
                    'filter' => [
                        'colors' => $colors,
                        'collections' => $collections,
                        'patterns' => $patterns,
                        'materials' => $materials,
                        'brands' => $brands,
                        'type_id' => $type_id,
                    ],
                    'items' => $item_id
                ];
            }

            $result[] = [
                '_id' => $challenge->_id,
                'name' => $challenge->name,
                'cover' => $challenge->cover.'.bytes',
                'background' => $challenge->background.'.bytes',
                'short_description' => $challenge->short_description,
                'long_description' => $challenge->long_description,
                'start_time' => strtotime($challenge->start_time),
                'end_time' => strtotime($challenge->end_time),
                'tag' => $challenge->tag,
                'max_unworn_value' => $challenge->max_unworn_value,
                'entry_reward' => $challenge->entry_reward,
                'requirement' => $challenge->requirement,
                'status' => $this->getStatus($challenge->start_time, $challenge->end_time, $time_voting),
                'dress_code' => $new_dress_code,
                'prizes' => $new_prizes
            ];
        }
        return $result;
    }

    public function getStatus($start, $end, $time_voting){

        $now = Carbon::now();
        $end = Carbon::parse($end);
        $end_voting = $end->copy()->addMinutes($time_voting);
    
        // Convert $start to a Carbon instance
        $start = Carbon::parse($start);
    
        // Determine the status
        if ($now->lt($start)) {
            $status = 'not_live';
        } elseif ($now->lt($end)) {
            $status = 'living';
        } elseif ($now->lt($end_voting)) {
            $status = 'voting';
        } else {
            $status = 'completed';
        }
    
        return $status;
    }
    


}
