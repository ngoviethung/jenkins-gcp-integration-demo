<?php

namespace App\Http\Controllers;

use App\Http\Services\MasterdataService;
use Response;
use App\User;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function validate_api_token(){

        $token = request()->header('Authorization');
        //$token = request()->get('api_token');
        if(!$token || !($user = User::loadByToken($token))) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'code'    => 401,
                'data'    => [
                    'message' => 'Unauthorized'
                ]
            ]);
            exit();
        }

        return $user;

    }

    public function photo_upload($file, $path)
    {

        $random = rand(1000000, 9999999);
        $filename = date('Y-m-d') . time() . $random . '.jpg';
        $file->move($path, $filename);
        return $filename;
    }
    public function sendResponse($result, $message = 'sucsess')
    {

        $res = [
            'code' => 200,
            'message' => $message,
            'data' => $result,
        ];

        return response()->json($res);
    }

    public function sendError($type = 'server_error', $message_error = 'error',  $code = 404, $message_to_screen = 'Error')
    {
        $res = [
            'code' => $code,
            'message_to_screen' => $message_to_screen,
            'error' =>  [
                'message' => $message_error,
                'type' => $type
            ],
        ];

        return response()->json($res, $code);
    }
    public function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
