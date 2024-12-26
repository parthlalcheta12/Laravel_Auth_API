<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */ 
    public function handle($request, Closure $next)
    { 
        try{
            $user = auth()->user();
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                   'status' => 400,
                   'message' => 'User not found',
                   'user' => null,
               ]);
           }
        }catch(\Tymon\JWTAuth\Exceptions\TokenInvalidException $e){     //NOT CATCHING...
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => "Invalid token",
                'data' => null
            ]);
        }
        return $next($request);
    }
}   
