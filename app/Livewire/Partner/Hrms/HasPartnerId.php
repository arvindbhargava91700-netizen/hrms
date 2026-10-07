<?php

namespace App\Livewire\Partner\Hrms;

use Illuminate\Support\Facades\Auth;

trait HasPartnerId
{
    public function getPartnerId()
    {
        $user = Auth::user();

        // A super admin has no implicit partner owner. Queries using the
        // partner scope are handled globally, while writes must opt in to an
        // explicit partner selection.
        if ($user->isSuperAdmin()) {
            return null;
        }
        
        if ($user->role === 'employee') {
            return $user->parent_id;
        }
        
        if ($user->isAdmin()) {
            // For admins, get the first partner or a selected partner
            // You can customize this logic based on your needs
            $partner = \App\Models\User::where('role', 'partner')->first();
            return $partner ? $partner->id : $user->id;
        }
        
        return $user->id;
    }

    protected function requirePartnerId()
    {
        return $this->getPartnerId();
    }

    public function getTeamEmployeeIds($viewAnyPermission = null)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return \App\Models\User::whereNotIn('role', ['super_admin', 'admin'])
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                ->pluck('id')
                ->toArray();
        }
        
        // If user specifically requested 'me' scope, return only current user (if not admin)
        if (request('scope') === 'me') {
            return $user->isAdmin() ? [] : [$user->id];
        }

        // If user specifically requested 'team' scope and has team access
        if (request('scope') === 'team') {
            return \App\Models\User::whereIn('id', $user->getTeamIds())
                ->whereNotIn('role', ['super_admin', 'admin'])
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                ->pluck('id')
                ->toArray();
        }

        // Partner, Admin, or User with viewAny permission sees ALL employees
        if (($user->role === 'partner' || $user->isAdmin()) || ($viewAnyPermission && $user->canAccess($viewAnyPermission))) {
            return \App\Models\User::where(function ($q) {
                $q->where('parent_id', $this->getPartnerId())
                  ->orWhere('id', $this->getPartnerId());
            })
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
            ->pluck('id')
            ->toArray();
        }

        if ($viewAnyPermission) {
            $module = str_replace('_viewAny', '', $viewAnyPermission);
            
            if ($user->canAccess($module . '_viewBranch') || $user->canAccess($module . '_viewbranch')) {
                return \App\Models\User::where('parent_id', $this->getPartnerId())
                    ->where('role', 'employee')
                    ->where('branch_id', $user->branch_id)
                    ->whereNotIn('role', ['super_admin', 'admin'])
                    ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                    ->pluck('id')
                    ->toArray();
            }

            if ($user->canAccess($module . '_viewTeam') || $user->canAccess($module . '_viewteam')) {
                return \App\Models\User::whereIn('id', $user->getTeamIds())
                    ->whereNotIn('role', ['super_admin', 'admin'])
                    ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                    ->pluck('id')
                    ->toArray();
            }
            
            if ($user->canAccess($module . '_viewOwn') || $user->canAccess($module . '_viewown')) {
                return $user->isAdmin() ? [] : [$user->id];
            }
        }
        
        // Fallback if no specific permission string provided or no specific match
        $teamIds = $user->getTeamIds();
        
        // If the user has any viewBranch permission, include branch employees in the fallback
        $hasBranchView = $user->permissions->contains(function ($perm) {
            return str_ends_with(strtolower($perm->name), '_viewbranch');
        }) || $user->roles->flatMap->permissions->contains(function ($perm) {
            return str_ends_with(strtolower($perm->name), '_viewbranch');
        });

        if ($hasBranchView) {
            $branchIds = \App\Models\User::where('parent_id', $this->getPartnerId())
                ->where('role', 'employee')
                ->where('branch_id', $user->branch_id)
                ->whereNotIn('role', ['super_admin', 'admin'])
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                ->pluck('id')
                ->toArray();
            $merged = array_unique(array_merge($teamIds, $branchIds));
            return \App\Models\User::whereIn('id', $merged)
                ->whereNotIn('role', ['super_admin', 'admin'])
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                ->pluck('id')
                ->toArray();
        }

        return \App\Models\User::whereIn('id', $teamIds)
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
            ->pluck('id')
            ->toArray();
    }
}
