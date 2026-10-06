<?php

namespace App\Http\Controllers\Api\Hrms\Traits;

use App\Models\User;

trait HasHrmsApiFilters
{
    /**
     * Get the Partner ID for the currently authenticated HRMS API user.
     */
    public function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->role === 'employee' ? $user->parent_id : $user->id;
    }

    /**
     * Fetch allowed employee IDs for the authenticated API user based on the requested permission.
     * Hierarchy: viewAny > viewBranch > viewTeam > viewOwn
     */
    public function getTeamEmployeeIds($viewAnyPermission = null)
    {
        $user = auth('hrms_api')->user();
        
        // Partner or User with viewAny permission sees ALL employees under the partner
        if ($user->role === 'partner' || ($viewAnyPermission && $user->canAccess($viewAnyPermission))) {
            return User::where('parent_id', $this->getPartnerId())
                ->where('role', 'employee')
                ->pluck('id')
                ->toArray();
        }

        if ($viewAnyPermission) {
            $module = str_replace(['_viewAny', '_viewany'], '', $viewAnyPermission);
            
            if ($user->canAccess($module . '_viewBranch') || $user->canAccess($module . '_viewbranch')) {
                return User::where('parent_id', $this->getPartnerId())
                    ->where('role', 'employee')
                    ->where('branch_id', $user->branch_id)
                    ->pluck('id')
                    ->toArray();
            }

            if ($user->canAccess($module . '_viewTeam') || $user->canAccess($module . '_viewteam')) {
                return $user->getTeamIds();
            }
            
            if ($user->canAccess($module . '_viewOwn') || $user->canAccess($module . '_viewown')) {
                return [$user->id];
            }
        }
        
        // Fallback if no specific permission string provided or no specific match
        $teamIds = $user->getTeamIds();
        
        // If the user has any viewBranch permission globally, include branch employees in the fallback
        $hasBranchView = $user->permissions->contains(function ($perm) {
            return str_ends_with(strtolower($perm->name), '_viewbranch');
        }) || $user->roles->flatMap->permissions->contains(function ($perm) {
            return str_ends_with(strtolower($perm->name), '_viewbranch');
        });

        if ($hasBranchView) {
            $branchIds = User::where('parent_id', $this->getPartnerId())
                ->where('role', 'employee')
                ->where('branch_id', $user->branch_id)
                ->pluck('id')
                ->toArray();
            return array_unique(array_merge($teamIds, $branchIds));
        }

        return $teamIds;
    }
}
