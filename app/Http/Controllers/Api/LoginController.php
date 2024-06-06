<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use JWTAuth;
use Exception;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Repositories\UserRepository;

class LoginController extends AppBaseController
{

    protected $user_repo;

     /**
     * Constructor.
     *
     * @param UserRepository $user_repo  The user repo
     */

    public function __construct(UserRepository $user_repo)
    {
    
        $this->user_repo = $user_repo;
    }

    
    public function identifier(Request $request, User $user)
    {
        try
        {

            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $message = isset($error['uuid'][0]) ? $error['uuid'][0] : '';
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $uuid = $request->uuid;
            $ip = $this->get_client_ip();
            $name = $user->service()->createRandomName();

            $condition = ['uuid' => $uuid, 'is_guest' => 1];
            $data = ['ip' => $ip, 'name' => $name];
            $user = $this->user_repo->firstOrCreate($condition, $data);

            $token = JWTAuth::fromUser($user);
            $data = $user->service()->getUserInfo($user->id);
            $data['token'] = $token;

            return $this->sendResponse($data);
        }catch(exception $e){

            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }


    public function loginGoogle(Request $request, User $user){
        try
        {
            $user_id = $request->get('user_id');

            $ip = $this->get_client_ip();
            $validator = Validator::make($request->all(), [
                'id_token' => 'required',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['id_token'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $id_token = $request->id_token;
            $google_response = $user->service()->getUserInfoGoogle($id_token);
            $google_data = json_decode($google_response, true);
            
            if (!$google_data) {
                throw new Exception('Authentication failed.');
            }

            $user = $user->service()->loginGoogle($user_id, $ip, $google_data);
            if($user === null){
                throw new Exception('Authentication failed.');
            }

            $token = JWTAuth::fromUser($user);
            $data = $user->service()->getUserInfo($user->id);
            $data['token'] = $token;
           
            return $this->sendResponse($data);
        }catch(exception $e){
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }
    }
    public function linkToGoogle(Request $request, User $user){
        try
        {
            $user_id = $request->get('user_id');

            $validator = Validator::make($request->all(), [
                'id_token' => 'required',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['id_token'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $id_token = $request->id_token;
            $google_response = $user->service()->getUserInfoGoogle($id_token);
            $google_data = json_decode($google_response, true);
            
            if (!$google_data) {
                throw new Exception('Authentication failed.');
            }

            $user = $user->service()->linkToGoogle($user_id, $google_data);
            if($user === null){
                throw new Exception('Authentication failed.');
            }
            if(isset($user['exited'])){
                throw new Exception('Google already linked.');
            }
            $data = $user->service()->getUserInfo($user->id);

           
            return $this->sendResponse($data);
        }catch(exception $e){
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }
    }

    public function loginFacebook(Request $request, User $user){
        try
        {
            $user_id = $request->get('user_id');
            $ip = $this->get_client_ip();
            $validator = Validator::make($request->all(), [
                'id_token' => 'access_token',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['access_token'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $access_token = $request->access_token;
            $facebook_response = file_get_contents("https://graph.facebook.com/me?fields=id,name,email,picture&access_token=$access_token");
            $facebook_data = json_decode($facebook_response);

            if(!isset($facebook_data->id)) {
                throw new \Exception('Authentication failed.');
            }
            
            $user = $user->service()->loginFacebook($user_id, $ip, $facebook_data);
        
            $token = JWTAuth::fromUser($user);

            $data = $user->service()->getUserInfo($user->id);
            $data['token'] = $token;

            return $this->sendResponse($data);
        }catch(exception $e){
            return $this->sendError($e->getMessage());
        }

    }

    public function linkToFacebook(Request $request, User $user){
        try
        {
            $user_id = $request->get('user_id');
        
            $validator = Validator::make($request->all(), [
                'id_token' => 'access_token',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->messages();
                $fields = ['access_token'];
                $message = '';
                foreach ($fields as $field) {
                    if (isset($error[$field][0])) {
                        $message = $error[$field][0];
                        break;
                    }
                }
                return $this->sendError('param_required', $message, 404, 'Error');
            }
            $access_token = $request->access_token;
            $facebook_response = file_get_contents("https://graph.facebook.com/me?fields=id,name,email,picture&access_token=$access_token");
            $facebook_data = json_decode($facebook_response);

            if(!isset($facebook_data->id)) {
                throw new \Exception('Authentication failed.');
            }
            $user = $user->service()->linkToFacebook($user_id, $facebook_data);
            if(isset($user['exited'])){
                throw new Exception('Facebook already linked.');
            }
            
            $token = JWTAuth::fromUser($user);
            $data = $user->service()->getUserInfo($user->id);

            return $this->sendResponse($data);
        }catch(exception $e){
            return $this->sendError($e->getMessage());
        }

    }

}
