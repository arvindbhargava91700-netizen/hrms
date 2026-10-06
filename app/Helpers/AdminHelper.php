<?php

namespace App\Helpers;

class AdminHelper
{
    /**
     * Masks contact information (email or mobile) for restricted admin users.
     */
    public static function maskContact($type, $value, $userRole = null)
    {
        // Don't mask if empty
        if (empty($value)) {
            return $value;
        }

        // Don't mask for super admins or those with permission
        if (auth()->check()) {
            if (auth()->user()->role === 'super_admin' || auth()->user()->can('admin_view_contact_info')) {
                return $value;
            }
        }

        // We only mask for partners and customers. Admin staff shouldn't be masked based on earlier instruction,
        // but if $userRole is not provided, we mask by default to be safe on other modules.
        if ($userRole === 'admin' || $userRole === 'super_admin') {
            return $value;
        }

        if ($type === 'email') {
            $parts = explode('@', $value);
            return str_repeat('*', max(1, strlen($parts[0] ?? ''))) . '@' . ($parts[1] ?? '');
        }

        if ($type === 'mobile') {
            return str_repeat('*', max(0, strlen($value) - 4)) . substr($value, -4);
        }

        return $value;
    }
}
