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

        if (in_array($user->role, ['partner', 'employee'])) {
            $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;
            
            $subscription = PackageService::getActiveSubscription($partnerId);

            if (!$subscription) {
                if ($user->role === 'partner') {
                    // Exclude some routes from redirecting to prevent loops
                    if (!$request->routeIs('partner.platform-plans')) {
                        return redirect()->route('partner.platform-plans')
                            ->with('error', 'Your subscription has expired or is inactive. Please renew your package to access all features.');
                    }
                } else {
                    abort(403, 'The organization subscription is inactive. Please contact the administrator.');
                }
            }
        }

        return $next($request);
    }
}
