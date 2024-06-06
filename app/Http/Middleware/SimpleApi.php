<?php

namespace App\Http\Middleware;

use App\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
class SimpleApi extends Middleware
{

    public function handle($request, Closure $next, ...$guards)
    {
        $token = $request->header('Authorization');
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

        auth()->loginUsingId($user->id);

        return $next($request);
    }
}
