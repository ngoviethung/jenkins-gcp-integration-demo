<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Models\MemberBuyItem;
use App\Models\Topic;
use App\Models\Type;
use Illuminate\Http\Request;
use Cache;
use DB;
use Log;
use App\Models\User;
use Exception;
use JWTAuth;
use Carbon\Carbon;

class UserController extends AppBaseController
{

    public function getUserInfo(Request $request, User $user)
    {
        try
        {
            $user_id = $request->get('user_id');

            $this->resetCurrentStreakVote($user_id);
            
            $data = $user->service()->getUserInfo($user_id);
            
            return $this->sendResponse($data);
        }catch(exception $e){

            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }

    public function resetCurrentStreakVote($user_id){

        try
        {
            $user = User::find($user_id);

            $time = Carbon::now();
            $datetime = $time->toDateTimeString();
            $last_open_app = $user->last_open_app;
            $current_streak_vote = $user->current_streak_vote;

            if($last_open_app === NULL){
                User::where('_id', $user_id)->update(['last_open_app' => $datetime, 'current_streak_vote' => 0]);
            } else {
                $last_open_app = Carbon::parse($last_open_app)->startOfDay();
                $now = Carbon::parse($datetime);
                if ($now->diffInDays($last_open_app) >= 1) {
                    User::where('_id', $user_id)->update(['last_open_app' => $datetime, 'current_streak_vote' => 0]);
                }
            }
            return true;
        }catch(exception $e){
            return false;
        }
    }


    public function logout(Request $request){
        try
        {
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->sendResponse([]);
            
        }catch(exception $e){
            return $this->sendError(['message' => $e->getMessage()]);
        }
    }

    public function deleteUser(Request $request){
        try
        {
            $user_id = $request->get('user_id');
            JWTAuth::invalidate(JWTAuth::getToken());
            User::where('_id', $user_id)->delete();

            return $this->sendResponse([]);

        }catch(exception $e){
            return $this->sendError(['message' => $e->getMessage()]);
        }
    }


    public function login() {
        try {
            $credentials = \request()->only(['email', 'password']);

            $password = md5(md5($credentials['password']));

            $user = DB::connection('mysql2')->table('admins')
                ->where(['email' => $credentials['email'], 'password' => $password])->first();

            if(!$user) {
                throw new \Exception('Wrong email or password.');
            }

            if(!$user->remember_token) {
                $api_token = md5(time() . $user->id);

                DB::connection('mysql2')->table('admins')
                    ->where('id', $user->id)->update(['remember_token' => $api_token]);
            }
            $user = DB::connection('mysql2')->table('admins')->select(['id', 'name', 'email', 'remember_token'])->find($user->id);

            $user->api_token =  $user->remember_token;

            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => [
                    'user' => $user,
                    'topics' => Topic::get(['id', 'name'])->toArray(),
                    'types' => Type::get(['id', 'name'])->toArray(),
                ]
            ]);


        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }


}

