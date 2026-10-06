<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePartnerKycApproved
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user || !$user->isPartner()) {
            return $next($request);
        }

        // if ($user->hasApprovedKyc()) {
            return $next($request);
        // }

        // if ($request->routeIs('partner.kyc') || $request->routeIs('partner.dashboard')) {
        //     return $next($request);
        // }

        // return redirect()
        //     ->route('partner.kyc')
        //     ->with('error', 'Please complete and get your KYC approved before accessing partner modules.');
    }
}
