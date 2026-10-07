<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\PackageService;

class RequireActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $next($request);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (empty($partnerId)) {
            return $next($request);
        }

        $subscription = PackageService::getActiveSubscription($partnerId);

        if (!$subscription) {
            if ($user->isPartner()) {
                // Exclude some routes from redirecting to prevent loops
                if (!$request->routeIs('partner.platform-plans') && !$request->routeIs('partner.subscription.callback')) {
                    return redirect()->route('partner.platform-plans')
                        ->with('error', 'Your subscription has expired or is inactive. Please renew your package to access all features.');
                }
            } else {
                return $next($request);
            }
        }

        return $next($request);
    }
}
