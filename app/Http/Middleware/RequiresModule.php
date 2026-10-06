<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\PartnerSubscription;
use App\Models\SystemModule;

class RequiresModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $moduleSlug): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check subscription for partner and employee roles
        if (in_array($user->role, ['partner', 'employee'])) {
            $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;
            
            $module = SystemModule::where('slug', $moduleSlug)->where('is_active', true)->first();
            
            if (!$module) {
                if ($user->role === 'partner') {
                    return redirect()->route('partner.platform-plans')->with('error', 'Module not found.');
                }
                abort(403, 'Module not found.');
            }

            // Check if partner has an active platform subscription whose package includes this module
            $activeSub = PartnerSubscription::with('package.systemModules')
                ->where('partner_id', $partnerId)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })->first();

            $hasAccess = false;
            if ($activeSub && $activeSub->package) {
                if ($activeSub->package->systemModules->contains('id', $module->id)) {
                    $hasAccess = true;
                }
            }

            if (!$hasAccess) {
                if ($user->role === 'partner') {
                    return redirect()->route('partner.platform-plans')->with('error', 'Your current plan does not include access to the ' . $module->name . ' module. Please upgrade your plan.');
                }
                abort(403, 'Your organization does not have access to this module under its current plan.');
            }
        }

        return $next($request);
    }
}
