<?php

namespace App\Services\User;

use Log;
use Exception;
use App\Models\User;
use App\Models\UserChallenge;
use App\Models\UserItem;
use App\Models\Change;
use Illuminate\Support\Collection;
use App\Models\Level;
use App\Services\Reward\RewardService;
use App\Services\Challenge\ChallengeService;

class UserService
{

    public function __construct()
    {
        
        
    }

    public function getCurrentStreakVote($user_id){
        $current_streak_vote = User::find($user_id)->current_streak_vote;

        return $current_streak_vote;
    }

    public function upCurrentStreakVote($user_id){
        User::where('_id', $user_id)->increment('current_streak_vote');

        return;
    }


    public function upSoft($user_id, $value, $change_id){

        User::where('user_id', $user_id)->increment('soft', (int)$value);
        $changes[] = [
            'type' => 'ADD_SOFT',
            'value' => $value
        ];
        $this->updateChange($change_id, $changes);

        return $value;
    }

    public function downSoft($user_id, $value, $change_id){

        User::where('user_id', $user_id)->decrement('soft', (int)$value);

        $changes[] = [
            'type' => 'REDUCE_SOFT',
            'value' => $value
        ];
        $this->updateChange($change_id, $changes);

        return $value;
    }

    public function downHard($user_id, $value, $change_id){

        User::where('user_id', $user_id)->decrement('hard', (int)$value);
        $changes[] = [
            'type' => 'REDUCE_HARD',
            'value' => $value
        ];
        $this->updateChange($change_id, $changes);

        return $value;
    }


    public function checkAndUpLevel($user_id, $change_id){

        $user = User::find($user_id);
        $current_level = $user->level != null ? $user->level : 1;
        $current_exp = $user->exp;

        $levels = Level::where('level', '>', $current_level)->get();
        
        $level_up = 0;
        foreach($levels as $value){
            if($current_exp >= $value->exp){
                $level_up++;
                $current_level++;
                $reward_service = new RewardService();
                $reward_service->reviceRewardLevelUp($user_id, $current_level);

            }else{
                break;
            }
        
        }

        if($level_up > 0){
            User::where('_id', $user_id)->increment('level', (int)$level_up);
            $changes[] = [
                'type' => 'LEVEL_UP',
                'value' => $current_level
            ];
            $this->updateChange($change_id, $changes);
        }
        
        return $level_up;
        
    }

    public function upExp($user_id, $exp, $change_id){

        User::where('_id', $user_id)->increment('exp', (int)$exp);
        

        $changes[] = [
            'type' => 'EXP',
            'value' => $exp
        ];
        $this->updateChange($change_id, $changes);
        
        return 1;
    }

    

    public function getChange($id){

        $change = Change::find($id);

        $data_1 = [];
        $data_2 = [];
        if ($change->metadata) {
            $arrayData = $change->metadata;

            $typesToKeepOriginal = ['BUY_ITEM_SUCCESS', 'LEVEL_UP', 'REWARD_ITEM'];

            foreach($arrayData as $value){
                $type = $value['type'];
                if(in_array($type, $typesToKeepOriginal)){
                    $data_1[] = $value;
                }else{
                    if(isset($data_2[$type])){
                        $data_2[$type]['value'] += $value['value'];
                    }else{
                        $data_2[$type]['type'] = $type;
                        $data_2[$type]['value'] = $value['value'];
                    }
                }

            }
            
        }
        $data_2 = array_values($data_2);
        $data = array_merge($data_1, $data_2);

        $this->deleteChange($id);

        return $data;
        
    }

    public function createChange($user_id){
     
        $change = Change::create(['user_id' => $user_id]);
        return $change->id;
    }

    public function updateChange($id, $metadata){

        foreach($metadata as $value){
            Change::where('_id', $id)->push('metadata', $value);
        }

        return ;
    }

    public function deleteChange($id){

        Change::where('_id', $id)->delete();
        return ;
    }


    //get access token demo:
    //B1: https://developers.google.com/oauthplayground
    //B2: Select scope Google OAuth2 API v2
    //B3:: Click button Authorize Apis
    //B4:: Click button Exchange authorization code for token

    public function getUserInfoGoogle($id_token){

        $user_info_url = 'https://oauth2.googleapis.com/tokeninfo?id_token='. $id_token ;
//        $headers = array(
//            'Authorization: Bearer ' . $access_token
//        );
        $headers = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;

    }

    public function loginGoogle($user_id, $ip, array $google_data){

        try{
            
           
            $google_id = isset($google_data['sub']) ? $google_data['sub'] : null;
            $email = isset($google_data['email']) ? $google_data['email'] : null;
            $name = isset($google_data['name']) ? $google_data['name'] : null;
            $avatar = isset($google_data['picture']) ? $google_data['picture'] : null;

            if(!$email or !$google_id) {
                return null;
            }

            $record = [];
            $record['ip'] = $ip;
            $record['email'] = $email;
            if($google_id){
                $record['google_id'] = $google_id;
            }
            if($name){
                $record['name'] = $name;
            }
            if($avatar){
                $record['avatar'] = $avatar;
            }
            $condition_1 = ['google_id' => $google_id];
            $condition_2 = ['email' => $email];

            $user = User::where($condition_1)->first();
            //check 1
            if($user){
                $user->ip = $ip;
                if(!$user->name){
                    $user->name = $name;
                }
                if(!$user->avatar){
                    $user->avatar = $avatar;
                }
                if(!$user->avatar){
                    $user->email = $email;
                }
                $user->save();
                
            }else{
                //check 2
                $user = User::where($condition_2)->first();
                if($user){
                    $user->ip = $ip;
                    if(!$user->google_id){
                        $user->google_id = $google_id;
                    }
                    if(!$user->name){
                        $user->name = $name;
                    }
                    if(!$user->avatar){
                        $user->avatar = $avatar;
                    }
                    $user->save();
                }else{
                    if($user_id){ //if has user identifier before
                        User::where('user_id', $user_id)->update($record);
                        $user = User::find($user_id);
                    }else{
                        $user = User::create($record);
                    }
                }
            }
            
            
            return $user;

        }catch(exception $e){
            
            return null;
        }

    }

    public function linkToGoogle($user_id, array $google_data){

        try{
            
            $google_id = isset($google_data['sub']) ? $google_data['sub'] : null;
            $email = isset($google_data['email']) ? $google_data['email'] : null;
            $name = isset($google_data['name']) ? $google_data['name'] : null;
            $avatar = isset($google_data['picture']) ? $google_data['picture'] : null;

            if(!$email or !$google_id) {
                return null;
            }
            $condition_1 = ['google_id' => $google_id];

            $user = User::where($condition_1)->where('user_id', '!=', $user_id)->first();
           
            if($user){
                return ['exited' => 1];
            }else{
                $user = User::find($user_id);
                $user->google_id = $google_id;
                $user->name = $name;
                $user->avatar = $avatar;
                $user->save();
            }
            
            return $user;

        }catch(exception $e){
            dd($e->getMessage());
            return null;
        }

    }

    public function linkToFacebook($user_id, object $facebook_data){

        try{
            $facebook_id = isset($facebook_data->id) ? $facebook_data->id : null;
            if(!$facebook_id){
                return null;
            }
            $name = isset($facebook_data->name) ? $facebook_data->name : $facebook_id;
            $phone = isset($facebook_data->phone) ? $facebook_data->phone : null;
            $email = isset($facebook_data->email) ? $facebook_data->email : $facebook_id . '@facebook.com';
            $avatar = isset($facebook_data->picture->data->url) ? $facebook_data->picture->data->url : null;

            $condition_1 = ['facebook_id' => $facebook_id];
            $user = User::where($condition_1)->where('user_id', '!=', $user_id)->first();

            if($user){
                return ['exited' => 1];
            }else{
                $user = User::find($user_id);
                $user->facebook_id = $facebook_id;
                $user->facebook_name = $name;
                $user->avatar = $avatar;
                $user->save();
            }
            
            return $user;

        }catch(exception $e){
            return null;
        }

    }
    //https://developers.facebook.com/tools/explorer/

    public function loginFacebook($user_id, $ip, object $facebook_data){

        try{
            $facebook_id = isset($facebook_data->id) ? $facebook_data->id : null;
            if(!$facebook_id){
                return null;
            }
            $name = isset($facebook_data->name) ? $facebook_data->name : $facebook_id;
            $phone = isset($facebook_data->phone) ? $facebook_data->phone : null;
            $email = isset($facebook_data->email) ? $facebook_data->email : $facebook_id . '@facebook.com';
            $avatar = isset($facebook_data->picture->data->url) ? $facebook_data->picture->data->url : null;

            $record = [];
            $record['ip'] = $ip;
            $record['facebook_id'] = $facebook_id;

            if($name){
                $record['facebook_name'] = $name;
            }
            if($phone){
                $record['phone'] = $phone;
            }
            if($avatar){
                $record['avatar'] = $avatar;
            }
            
            $condition_1 = ['facebook_id' => $facebook_id];
            $condition_2 = ['email' => $email];

            $user = User::where($condition_1)->first();
            if($user){

                $user->ip = $ip;
                if(!$user->facebook_name){
                    $user->facebook_name = $name;
                }
                if(!$user->avatar){
                    $user->avatar = $avatar;
                }
                if(!$user->avatar){
                    $user->email = $email;
                }
                $user->save();

            }else{
                //check 2
                $user = User::where($condition_2)->first();
                if($user){
                    $user->ip = $ip;
                    if(!$user->facebook_id){
                        $user->facebook_id = $facebook_id;
                    }
                    if(!$user->name){
                        $user->name = $name;
                    }
                    if(!$user->avatar){
                        $user->avatar = $avatar;
                    }
                    $user->save();
                }else{
                    if($user_id){ //if has user identifier before
                        $user = User::where('user_id', $user_id)->update($record);
                    }else{
                        $user = User::create($record);
                    }
                }
            }

            return $user;

        }catch(exception $e){
            return null;
        }

    }

    public function getUserCurrencyAndExp($user_id){
        $user = User::find($user_id);
        $data = [
            'softCurrency' => $user->soft,
            'hardCurrency' => $user->hard,
            'exp' => $user->exp,
        ];

        return $data;
    }

    public function getUserInfo($user_id){

        $user = User::find($user_id);
        $itemOwned = [];
        $hairOwned = [];
        
        /*
        $challengeJoined = [];
        $challengeSubmitted = [];
        $challengeClaimed = [];
        $itemWorn = [];

        $user_challenges = UserChallenge::where('user_id', $user_id)->get();
        foreach($user_challenges as $value){
            $challengeJoined[] = $value->challenge_id;
            if(isset($value->image)){
                $challengeSubmitted[] = $value->challenge_id;
            }
            if($value->claimed == 1){
                $challengeClaimed[] = $value->challenge_id;
            }
        }
        */

        $user_items = UserItem::where('user_id', $user_id)->where('is_hair', '!=', 1)->get();
        foreach($user_items as $value){
            $itemOwned[] = [
                'id' => $value->item_id,
                'unworn' => $value->worn == 1 ? 0 : 1
            ];
        }

        $user_items = UserItem::where(['user_id' => $user_id, 'is_hair' => 1]) ->get();
        foreach($user_items as $value){
            $hairOwned[] = [
                'id' => $value->item_id,
                'unworn' => $value->worn == 1 ? 0 : 1
            ];
        }

        $challenge_service = new ChallengeService();
        //$challengeSubmitted = $challenge_service->getChallengeSubmitted($user_id);

        $data = [
            '_id' => $user_id,
            'uuid' => $user->uuid,
            'fb_id' => $user->facebook_id,
            'fb_name' => $user->facebook_name,
            'gg_id' => $user->google_id,
            'gg_name' => $user->name,
            'gg_email' => $user->email,
            'avatar' => $user->avatar,
            'softCurrency' => $user->soft,
            'hardCurrency' => $user->hard,
            'exp' => $user->exp,
            'current_streak_vote' => $user->current_streak_vote,
            //'challengeSubmitted' => $challengeSubmitted,
            'itemOwned' => $itemOwned,
            'hairOwned' => $hairOwned,
            //'challenges' => [],
            'dailyLoginReward' => [3,4]
        ];

        return $data;

    }
    
    public function createRandomName(){

        $firstNames = array("Ethan", "Olivia", "Liam", "Ava", "Noah", "Emma", "James", "Sophia", "Benjamin", "Isabella");
        $lastNames = array("Smith", "Johnson", "Williams", "Jones", "Brown", "Davis", "Miller", "Wilson", "Moore", "Taylor");

        $randomFirstName = $firstNames[array_rand($firstNames)];
        $randomLastName = $lastNames[array_rand($lastNames)];

        $randomFullName = $randomFirstName . " " . $randomLastName;

       return $randomFullName;

    }

}
