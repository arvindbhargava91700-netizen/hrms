<?php

namespace App\Services;

use App\Models\PartnerSubscription;
use App\Models\User;

class PackageService
{
    /**
     * Get the active PartnerSubscription with its package and modules for a partner.
     */
    public static function getActiveSubscription(string $partnerId): ?PartnerSubscription
    {
        return PartnerSubscription::with(['package.systemModules'])
            ->where('partner_id', $partnerId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now()->startOfDay());
            })
            ->latest('starts_at')
            ->first();
    }

    /**
     * Get active package for a partner (shortcut).
     */
    public static function getActivePackage(string $partnerId)
    {
        return static::getActiveSubscription($partnerId)?->package;
    }

    /**
     * Check if a partner has access to a module by slug.
     * If the package has no modules at all, access is granted to all (free-for-all mode).
     */
    public static function hasModule(string $partnerId, string $moduleSlug): bool
    {
        $subscription = static::getActiveSubscription($partnerId);

        // No active subscription → deny access to optional modules
        if (!$subscription || !$subscription->package) {
            return false;
        }

        $modules = $subscription->package->systemModules;

        // Package has no module restrictions → all modules open
        if ($modules->isEmpty()) {
            return true;
        }

        return $modules->contains('slug', $moduleSlug);
    }

    /**
     * Check how many listings the partner is allowed to have.
     * Returns null if unlimited.
     */
    public static function getListingLimit(string $partnerId): ?int
    {
        $pkg = static::getActivePackage($partnerId);
        return $pkg?->listing_limit;
    }

    /**
     * Check how many categories (listing categories) the partner is allowed.
     * Returns null if unlimited.
     */
    public static function getCategoryLimit(string $partnerId): ?int
    {
        $pkg = static::getActivePackage($partnerId);
        return $pkg?->category_limit;
    }

    /**
     * Calculate commission for a given transaction.
     *
     * @param string $partnerId
     * @param float  $amount       The transaction amount
     * @param string $paymentMode  'online' | 'offline' | 'cash'
     * @return array ['commission' => float, 'partner_amount' => float, 'type' => string, 'rate' => float]
     */
    public static function calculateCommission(string $partnerId, float $amount, string $paymentMode = 'online'): array
    {
        $pkg = static::getActivePackage($partnerId);
        $isOffline = in_array(strtolower($paymentMode), ['offline', 'cash', 'manual']);

        $type = 'fixed';
        $rate = 5;

        if ($pkg) {
            $ranges = $isOffline
                ? ($pkg->offline_commission_ranges ?? [])
                : ($pkg->commission_ranges ?? []);

            $matchedRange = null;
            if (is_array($ranges) && count($ranges) > 0) {
                // Sort by min_amount ascending
                usort($ranges, fn($a, $b) => ((float)($a['min_amount'] ?? 0)) <=> ((float)($b['min_amount'] ?? 0)));

                foreach ($ranges as $range) {
                    $min = (float) ($range['min_amount'] ?? 0);
                    $max = isset($range['max_amount']) && $range['max_amount'] !== '' && $range['max_amount'] !== null
                        ? (float) $range['max_amount']
                        : null;

                    if ($amount >= $min && ($max === null || $amount <= $max)) {
                        $matchedRange = $range;
                        break;
                    }
                }
            }

            if ($matchedRange) {
                $type = $matchedRange['type'] ?? 'percent';
                $rate = (float) ($matchedRange['value'] ?? 0);
            } else {
                if ($isOffline) {
                    $type = $pkg->offline_commission_type ?? 'fixed';
                    $rate = (float) ($pkg->offline_commission_value ?? 0);
                } else {
                    $type = $pkg->commission_type ?? 'fixed';
                    $rate = (float) ($pkg->commission_value ?? 5);
                }
            }
        }

        $commission = $type === 'percent'
            ? ($amount * $rate / 100)
            : $rate;

        // Commission can't exceed transaction amount
        $commission = max(0, min($commission, $amount));

        return [
            'commission'     => round($commission, 2),
            'partner_amount' => round($amount - $commission, 2),
            'type'           => $type,
            'rate'           => $rate,
            'mode'           => $isOffline ? 'offline' : 'online',
        ];
    }

    /**
     * Determine payment mode from gateway string.
     */
    public static function detectPaymentMode(?string $gateway): string
    {
        $offlineGateways = ['cash', 'offline', 'manual', 'cash_offline'];
        return in_array(strtolower($gateway ?? ''), $offlineGateways) ? 'offline' : 'online';
    }

    /**
     * Enforces package limits when a subscription changes or downgrades.
     * Marks excess active listings as draft.
     */
    public static function enforceLimits(string $partnerId): void
    {
        $listingLimit = self::getListingLimit($partnerId);
        $categoryLimit = self::getCategoryLimit($partnerId);

        // If both limits are null, unlimited, do nothing
        if ($listingLimit === null && $categoryLimit === null) {
            return;
        }

        // Apply Category Limit first
        if ($categoryLimit !== null) {
            $uniqueCategories = \App\Models\Listing::where('partner_id', $partnerId)
                ->where('status', '!=', 'draft')
                ->selectRaw('category_id, count(*) as count')
                ->groupBy('category_id')
                ->orderByDesc('count') // keep categories with most listings
                ->get();

            if ($uniqueCategories->count() > $categoryLimit) {
                $allowedCategoryIds = $uniqueCategories->take($categoryLimit)->pluck('category_id')->toArray();
                \App\Models\Listing::where('partner_id', $partnerId)
                    ->where('status', '!=', 'draft')
                    ->whereNotIn('category_id', $allowedCategoryIds)
                    ->update(['status' => 'draft']);
            }
        }

        // Apply Listing Limit
        if ($listingLimit !== null) {
            $activeListings = \App\Models\Listing::where('partner_id', $partnerId)
                ->where('status', '!=', 'draft')
                ->orderByDesc('updated_at') // keep recently updated ones active
                ->get();

            if ($activeListings->count() > $listingLimit) {
                $allowedListingIds = $activeListings->take($listingLimit)->pluck('id')->toArray();
                \App\Models\Listing::where('partner_id', $partnerId)
                    ->where('status', '!=', 'draft')
                    ->whereNotIn('id', $allowedListingIds)
                    ->update(['status' => 'draft']);
            }
        }
    }
}
