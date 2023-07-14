<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Packages\Jwt;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // $token = $request->bearerToken();

        // if (empty($token)) {
        //     return response(['result' => false, 'message' => 'token does not exist'], 200);
        // }

        // $token = Jwt::verifyToken($token);

        // if (!$token) {
        //     return response(['result' => false, 'message' => 'token does not exist'], 200);
        // } else if ($token['expireTime'] < time()) {
        //     return response(['result' => false, 'message' => 'Token is expired'], 200);
        // }

        return $next($request);
    }
}
