<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class EnsureApiAuthenticated
{
    public function handle(Request $request, Closure $next, $guard = 'api')
    {
        try {
            $user = auth($guard)->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please login first',
                ], 401);
            }
            
            auth()->shouldUse($guard);
            
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired. Please login again.',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid. Please login again.',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please login first',
            ], 401);
        }

        return $next($request);
    }
}
