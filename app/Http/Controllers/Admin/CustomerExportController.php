<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $query = User::query()
            ->where('role', 'customer')
            ->withCount([
                'subscriptions as subscriptions_count',
                'subscriptions as active_subscriptions_count' => fn ($subQuery) => $subQuery->where('status', 'active'),
                'payments as payments_count',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->string('email_verified')->toString() === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($request->string('email_verified')->toString() === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        if ($request->string('mobile_verified')->toString() === 'verified') {
            $query->whereNotNull('mobile_verified_at');
        } elseif ($request->string('mobile_verified')->toString() === 'unverified') {
            $query->whereNull('mobile_verified_at');
        }

        if ($request->string('subscription')->toString() === 'has_active') {
            $query->whereHas('subscriptions', fn ($subQuery) => $subQuery->where('status', 'active'));
        } elseif ($request->string('subscription')->toString() === 'no_active') {
            $query->whereDoesntHave('subscriptions', fn ($subQuery) => $subQuery->where('status', 'active'));
        }

        $sort = $request->string('sort')->toString();
        $query = match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'name' => $query->orderBy('name', 'asc'),
            'email' => $query->orderBy('email', 'asc'),
            default => $query->latest(),
        };

        $filename = 'customers-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Name',
                'Email',
                'Mobile',
                'Status',
                'Email Verified',
                'Mobile Verified',
                'Subscriptions',
                'Active Subscriptions',
                'Payments',
                'Registered At',
            ]);

            $query->chunk(200, function ($customers) use ($output) {
                foreach ($customers as $customer) {
                    fputcsv($output, [
                        $customer->name,
                        $customer->email,
                        $customer->mobile,
                        $customer->status,
                        $customer->email_verified_at?->format('Y-m-d H:i:s'),
                        $customer->mobile_verified_at?->format('Y-m-d H:i:s'),
                        $customer->subscriptions_count ?? 0,
                        $customer->active_subscriptions_count ?? 0,
                        $customer->payments_count ?? 0,
                        $customer->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
