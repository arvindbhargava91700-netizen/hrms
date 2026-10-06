<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\KycDocument;
use App\Models\KycRequirement;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    public function __invoke(Request $request, string $module): StreamedResponse
    {
        return match ($module) {
            'partners' => $this->exportPartners($request),
            'customers' => $this->exportCustomers($request),
            'listings' => $this->exportListings($request),
            'subscriptions' => $this->exportSubscriptions($request),
            'payments' => $this->exportPayments($request),
            'categories' => $this->exportCategories($request),
            'kyc' => $this->exportKyc($request),
            'kyc-fields' => $this->exportKycFields($request),
            'reports' => $this->exportReports($request),
            default => abort(404),
        };
    }

    protected function exportPartners(Request $request): StreamedResponse
    {
        $query = User::query()
            ->where('role', 'partner')
            ->withCount('listings')
            ->with('kycDocument');

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

        return $this->downloadCsv($query->latest(), 'partners', function ($partner): array {
            return [
                $partner->name,
                $partner->email,
                $partner->mobile,
                $partner->status,
                $partner->listings_count ?? 0,
                $partner->kycDocument?->status ?? 'not_submitted',
                $partner->created_at?->format('Y-m-d H:i:s'),
            ];
        }, ['Name', 'Email', 'Mobile', 'Status', 'Listings', 'KYC Status', 'Registered At']);
    }

    protected function exportCustomers(Request $request): StreamedResponse
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

        return $this->downloadCsv($query->latest(), 'customers', function ($customer): array {
            return [
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
            ];
        }, ['Name', 'Email', 'Mobile', 'Status', 'Email Verified', 'Mobile Verified', 'Subscriptions', 'Active Subscriptions', 'Payments', 'Registered At']);
    }

    protected function exportListings(Request $request): StreamedResponse
    {
        $query = Listing::with(['partner', 'category']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        } else {
            $query->whereIn('status', ['pending', 'approved', 'rejected', 'suspended']);
        }

        return $this->downloadCsv($query->latest(), 'listings', function ($listing): array {
            return [
                $listing->title,
                $listing->category->name ?? 'N/A',
                $listing->partner->name ?? 'N/A',
                $listing->status,
                $listing->address,
                $listing->created_at?->format('Y-m-d H:i:s'),
            ];
        }, ['Title', 'Category', 'Partner', 'Status', 'Address', 'Created At']);
    }

    protected function exportSubscriptions(Request $request): StreamedResponse
    {
        $query = Subscription::with(['customer', 'package.listing']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('package', fn ($packageQuery) => $packageQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $sort = $request->string('sort')->toString();
        $query = match ($sort) {
            'expiring' => $query->orderBy('expires_at', 'asc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'customer' => $query->orderByRaw('(SELECT name FROM users WHERE users.id = subscriptions.customer_id) ASC'),
            default => $query->latest(),
        };

        return $this->downloadCsv($query, 'subscriptions', function ($subscription): array {
            return [
                $subscription->customer->name ?? 'N/A',
                $subscription->package->name ?? 'N/A',
                $subscription->package->listing->title ?? 'N/A',
                $subscription->status,
                $subscription->starts_at?->format('Y-m-d'),
                $subscription->expires_at?->format('Y-m-d'),
                $subscription->auto_renew ? 'Yes' : 'No',
                $subscription->created_at?->format('Y-m-d H:i:s'),
            ];
        }, ['Customer', 'Package', 'Listing', 'Status', 'Starts At', 'Expires At', 'Auto Renew', 'Created At']);
    }

    protected function exportPayments(Request $request): StreamedResponse
    {
        $query = Payment::with(['subscription.customer', 'invoice']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('gateway_ref', 'like', "%{$search}%")
                    ->orWhereHas('subscription.customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->downloadCsv($query->latest(), 'payments', function ($payment): array {
            return [
                $payment->subscription->customer->name ?? 'N/A',
                $payment->subscription->package->name ?? 'N/A',
                $payment->gateway,
                $payment->gateway_ref,
                $payment->amount,
                $payment->status,
                $payment->paid_at?->format('Y-m-d H:i:s'),
                $payment->created_at?->format('Y-m-d H:i:s'),
            ];
        }, ['Customer', 'Package', 'Gateway', 'Gateway Ref', 'Amount', 'Status', 'Paid At', 'Created At']);
    }

    protected function exportCategories(Request $request): StreamedResponse
    {
        $query = Category::withCount(['listings', 'customFields']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $isActive = $request->string('status')->toString() === 'active';
            $query->where('is_active', $isActive);
        }

        return $this->downloadCsv($query->orderBy('sort_order'), 'categories', function ($category): array {
            return [
                $category->name,
                $category->slug,
                $category->is_active ? 'active' : 'inactive',
                $category->listings_count ?? 0,
                $category->custom_fields_count ?? 0,
                $category->created_at?->format('Y-m-d H:i:s'),
            ];
        }, ['Name', 'Slug', 'Status', 'Listings', 'Fields', 'Created At']);
    }

    protected function exportKyc(Request $request): StreamedResponse
    {
        $query = KycDocument::with('partner');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('partner', fn ($partnerQuery) => $partnerQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->downloadCsv($query->latest(), 'kyc', function ($doc): array {
            $summary = $doc->submission_summary;
            return [
                $doc->partner->name ?? 'N/A',
                $doc->partner->email ?? 'N/A',
                $doc->status,
                implode(' | ', array_map(fn ($item) => ($item['label'] ?? '') . ': ' . ($item['value'] ?? 'documents attached'), $summary)),
                $doc->submitted_at?->format('Y-m-d H:i:s') ?? $doc->created_at?->format('Y-m-d H:i:s'),
                $doc->reviewed_at?->format('Y-m-d H:i:s'),
            ];
        }, ['Partner', 'Email', 'Status', 'Summary', 'Submitted At', 'Reviewed At']);
    }

    protected function exportKycFields(Request $request): StreamedResponse
    {
        $query = KycRequirement::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('label', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('field_type', $request->string('type'));
        }

        return $this->downloadCsv($query->orderBy('sort_order'), 'kyc-fields', function ($field): array {
            return [
                $field->label,
                $field->key,
                $field->field_type,
                $field->input_type,
                $field->document_mode,
                $field->is_required ? 'Yes' : 'No',
                $field->is_active ? 'Yes' : 'No',
            ];
        }, ['Label', 'Key', 'Type', 'Input Type', 'Document Mode', 'Required', 'Active']);
    }

    protected function exportReports(Request $request): StreamedResponse
    {
        $dateRange = $request->string('date_range')->toString() ?: 'this_month';

        $startDate = match ($dateRange) {
            'today' => Carbon::today(),
            'this_week' => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'this_year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $rows = collect([
            ['Metric', 'Value', 'Period Start', 'Generated At'],
            ['Revenue', Payment::where('status', 'paid')->where('paid_at', '>=', $startDate)->sum('amount'), $startDate->format('Y-m-d'), now()->format('Y-m-d H:i:s')],
            ['New Subscriptions', Subscription::where('starts_at', '>=', $startDate)->count(), $startDate->format('Y-m-d'), now()->format('Y-m-d H:i:s')],
            ['Payments', Payment::where('status', 'paid')->where('paid_at', '>=', $startDate)->count(), $startDate->format('Y-m-d'), now()->format('Y-m-d H:i:s')],
        ]);

        $filename = 'reports-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function downloadCsv($query, string $prefix, callable $rowResolver, array $headers): StreamedResponse
    {
        $filename = $prefix . '-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query, $rowResolver, $headers) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);

            $query->chunk(200, function ($rows) use ($output, $rowResolver) {
                foreach ($rows as $row) {
                    fputcsv($output, $rowResolver($row));
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
