<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Models\Token;
use Log;

class VerifyJWTToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public $attributes;
    public function handle($request, Closure $next)
    {

        try {

            $user = JWTAuth::parseToken()->authenticate();
            $request->attributes->add(['user_id' => $user->id]);

        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return $this->sendError('token_invalid', 'Token is invalid', 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return $this->sendError('token_expired', 'Token is expired', 498);
            }else{
                return $this->sendError('token_require', 'Authorization Token not found', 400);
            }
        }
        return $next($request);
    }

    private function sendError($type = 'error', $message = 'error', $code = 404)
    {
        $response = [
            'code' => $code,
            'message' => 'Unauthorized',
            'error' =>  [
                'message' => $message,
                'type' => $type
            ],
        ];
        return response()->json($response, $code);
    }
}
