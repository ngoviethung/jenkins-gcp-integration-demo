<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;
use App\Http\Controllers\AppBaseController;

use App\Models\Challenge;
use App\Models\User;
use App\Models\UserChallenge;
use App\Models\UserVoteChallenge;
use Illuminate\Http\Request;
use Exception;
use App\Http\Resources\Api\ChallengeResource;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use App\Repositories\ChallengeRepository;
use App\Events\Challenge\ChallengeWasSubmit;
use App\Events\Challenge\ChallengeWasResult;
use App\Services\Challenge\VottingService;
use App\Services\User\UserService;
use App\Events\Vote\VoteWasSubmit;




class ChallengeController extends AppBaseController
{

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


    public function getResultChallenge(Request $request, Challenge $challenge, User $user)
    {
        try {

            $user_id = $request->get('user_id');
            $validator = Validator::make($request->all(), [
                'challenge_id' => 'required',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['challenge_id'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }

            
            $change_id = $user->service()->createChange($user_id);

            $challenge_id = $request->challenge_id;
            $my_challenge = $challenge->service()->getResultChallengeByUser($user_id, $challenge_id );
            $leaderboard = $challenge->service()->getLeaderBoard($challenge_id);

            
            event(new ChallengeWasResult($user_id, $challenge_id, $my_challenge, $change_id));

            $changes = $user->service()->getChange($change_id);

            $data = [
                'chalenge_id' => $challenge_id,
                'mine' => $my_challenge,
                'changes' => $changes,
                'leaderboard' => $leaderboard
            ];
            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }
    }

    
    public function getChallenges(Request $request, Challenge $challenge)
    {
        try {
            $user_id = $request->get('user_id');

            $startDate = Carbon::now()->subDays(200)->toDateTimeString();
            $endDate = Carbon::now()->addDays(500)->toDateTimeString();

            $challenges = $challenge->service()->getChallenges($startDate, $endDate);
            $challenges_submited = $challenge->service()->getChallengeSubmitted($user_id);
            
            $data = [
                'challenges' => $challenges,
                'challengeSubmitted' => $challenges_submited
            ];

            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }
    }

    

    public function submitChallenge(Request $request, User $user)
    {
        try {
            

            $user_id = $request->get('user_id');

            $input_data = $request->getContent();
            $input_data = json_decode($input_data);
            if(!isset($input_data->challenge_id)
                or !isset($input_data->isTuck)
                or !isset($input_data->skin_id)
                or !isset($input_data->list_item)
                or !isset($input_data->hair)
                or !isset($input_data->makeup)){

                return $this->sendError('param_required', "Missing field.", 404, 'Error');
            }

            $challenge_id = $input_data->challenge_id;
            $check = Challenge::where('_id', $challenge_id)->count();
            if($check == 0){
                return $this->sendError('param_invalid', "challenge_id not found.", 404, 'Error');
            }

            $input_data->user_id = $user_id;
            $data = json_encode($input_data);

            $where = [
                'user_id' => $user_id,
                'challenge_id' => $challenge_id,
            ];
            $data_create = [
                'input_data' => $input_data,
            ];
            $record = UserChallenge::updateOrCreate($where, $data_create);


            $mqService = new \App\Services\RabbitMQService();
            $status = $mqService->publish($data, 'merge_image');
            if($status == 0){
                throw new Exception("Server is overloaded.");
            }
            
            $change_id = $user->service()->createChange($user_id);
            event(new ChallengeWasSubmit($user_id, $data, $change_id));
            
            $changes = $user->service()->getChange($change_id);
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

    public function getVote(Request $request, Challenge $challenge)
    {
        try {
            

            $user_id = $request->get('user_id');
            /*
            $validator = Validator::make($request->all(), [
                'challenge_id' => 'required',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['challenge_id'];
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

            $votting_service = new VottingService();
            $user_service = new UserService();

            $current_streak_vote = $user_service->getCurrentStreakVote($user_id);
            $challenge_id = $votting_service->getRandomChallengeIdVotting();
            if($challenge_id == null){
                return $this->sendResponse([]);
            }

            $challenge_info = $votting_service->getChallengeInfoVotting($user_id, $challenge_id, $current_streak_vote);
            $next_vote = $votting_service->getNextVote($user_id, $challenge_id, null);
            $reward_vote = $votting_service->getRewardVote($user_id, $current_streak_vote);

            $data = [
                'challenge' => $challenge_info,
                'reward_vote' => $reward_vote,
                'vote_list' => $next_vote
            ];

            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }
    
    public function submitVote(Request $request, User $user)
    {
        try {
            $user_id = $request->get('user_id');

            $validator = Validator::make($request->all(), [
                'list_id_vote' => 'required|json',
                'list_id_no_vote' => 'required|json',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['list_id_vote', 'list_id_no_vote'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $list_id_vote = json_decode($request->list_id_vote);
            $list_id_no_vote = json_decode($request->list_id_no_vote);
            $arr_id_votted = array_merge($list_id_vote, $list_id_no_vote);

            if(empty($list_id_vote) or empty($list_id_no_vote)){
                return $this->sendError('param_invalid', 'paramter is invalid', 404, 'Error');
            }

            foreach($list_id_vote as $_id){
                $data = [
                    'user_id' => $user_id,
                    '_id' => $_id
                ];
                $data = json_encode($data);
                $mqService = new \App\Services\RabbitMQService();
                $status = $mqService->publish($data, 'vote');
                if($status == 0){
                    throw new Exception("Server is overloaded.");
                }
            }

            //get next vote
            $votting_service = new VottingService();
            $user_service = new UserService();

            $current_streak_vote = $user_service->getCurrentStreakVote($user_id);
            $challenge_id = $votting_service->getRandomChallengeIdVotting();
            if($challenge_id == null){
                return $this->sendResponse([]);
            }

            $challenge_info = $votting_service->getChallengeInfoVotting($user_id, $challenge_id, $current_streak_vote);
            $next_vote = $votting_service->getNextVote($user_id, $challenge_id, $arr_id_votted);
            $reward_vote = $votting_service->getRewardVote($user_id, $current_streak_vote);
            

            
            $change_id = $user->service()->createChange($user_id);
            event(new VoteWasSubmit($user_id, $current_streak_vote, $change_id));
            $changes = $user->service()->getChange($change_id);
            $user_info = $user->service()->getUserCurrencyAndExp($user_id);

            $data = [
                'changes' => $changes,
                'user_info' => $user_info,
                'next_vote_data' => [
                    'challenge' => $challenge_info,
                    'reward_vote' => $reward_vote,
                    'vote_list' => $next_vote
                ]
            ];


            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }
    }



    public function vote($data = false){
        try {
            if($data === false){
                $data = '{
                  "user_id": 1,
                  "_id": "660b7afd373f29598e087882"
                }';
            }
            $data = json_decode($data, true);
            $_id = $data['_id'];
            $user_challenge = UserChallenge::where('_id', $_id)->first();
            if(!$user_challenge){
                return 0;
            }
            $data_insert = [
                'user_id' => $data['user_id'],
                'challenge_id' => $user_challenge->challenge_id,
                'user_challenge_id' => $data['_id'],
            ];
            $check_vote = UserVoteChallenge::where($data_insert)->count();
            if($check_vote == 0){
                UserVoteChallenge::create($data_insert);
                UserChallenge::where('_id', $_id)->increment('vote');
            }

            return 1;
        } catch (exception $e) {
            return 0;
        }
    }

    

}
