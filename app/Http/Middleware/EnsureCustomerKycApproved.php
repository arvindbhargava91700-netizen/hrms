<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerKycApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // if ($user && $user->role === 'customer' && !$user->hasApprovedCustomerKyc()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'error_code' => 'KYC_REQUIRED',
        //         'message' => 'Please complete and get your KYC profile approved to access this feature.'
        //     ], 403);
        // }

        return $next($request);
    }
}
