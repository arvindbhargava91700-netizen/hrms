<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class InvoiceReceiptController extends Controller
{
    public function show(Request $request, string $type, string $id)
    {
        if ($type === 'job_post') {
            $receipt = $this->resolveJobPostReceipt($request, $id);
            return view('invoices.job_post_receipt', $receipt);
        }

        $receipt = $this->resolveReceipt($request, $type, $id);
        return view('invoices.receipt', $receipt);
    }

    public function download(Request $request, string $type, string $id)
    {
        if ($type === 'job_post') {
            $receipt = $this->resolveJobPostReceipt($request, $id);
            $html = view('invoices.job_post_receipt', $receipt)->render();
            $filename = 'job-post-receipt-' . ($receipt['transaction']->id ?? 'unknown') . '.pdf';
        } else {
            $receipt = $this->resolveReceipt($request, $type, $id);
            $html = view('invoices.receipt', $receipt)->render();
            $filename = ($receipt['invoice']->invoice_number ?? $receipt['payment']?->receipt_number ?? $receipt['payment']?->gateway_ref ?? 'invoice-receipt') . '.pdf';
        }

        $pdf = $this->renderPdfFromHtml($html, $receipt);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveJobPostReceipt(Request $request, string $id): array
    {
        $user = $request->user();
        $query = \App\Models\TransactionHistory::with(['user'])->where('type', 'job_post');

        if ($user->role === 'partner') {
            $query->where('user_id', $user->id);
        }

        $transaction = $query->findOrFail($id);
        $jobPost = \App\Models\JobPost::find($transaction->reference_id);

        return [
            'title' => 'Job Post Billing Receipt',
            'type' => 'job_post',
            'id' => $transaction->id,
            'download_route' => $user->role === 'partner'
                ? 'partner.invoices.receipt.download'
                : 'admin.invoices.receipt.download',
            'transaction' => $transaction,
            'jobPost' => $jobPost,
            'partner' => $transaction->user,
        ];
    }

    private function resolveReceipt(Request $request, string $type, string $id): array
    {
        $user = $request->user();

        $invoiceQuery = Invoice::with(['subscription.customer', 'subscription.package.listing', 'booking.customer', 'booking.package.listing', 'payments']);
        $paymentQuery = Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice', 'booking.customer', 'booking.package.listing']);

        if ($user->role === 'partner') {
            $packageIds = Package::whereHas('listing', fn ($query) => $query->where('partner_id', $user->id))->pluck('id');
            $invoiceQuery->where(function ($q) use ($packageIds) {
                $q->whereHas('subscription', fn ($query) => $query->whereIn('package_id', $packageIds))
                  ->orWhereHas('booking', fn ($query) => $query->whereIn('package_id', $packageIds));
            });
            $paymentQuery->where(function ($q) use ($packageIds) {
                $q->whereHas('subscription', fn ($query) => $query->whereIn('package_id', $packageIds))
                  ->orWhereHas('booking', fn ($query) => $query->whereIn('package_id', $packageIds));
            });
        } elseif ($user->role === 'customer' || empty($user->role)) {
            $invoiceQuery->where(function ($q) use ($user) {
                $q->whereHas('subscription', fn ($query) => $query->where('customer_id', $user->id))
                  ->orWhereHas('booking', fn ($query) => $query->where('customer_id', $user->id));
            });
            $paymentQuery->where(function ($q) use ($user) {
                $q->whereHas('subscription', fn ($query) => $query->where('customer_id', $user->id))
                  ->orWhereHas('booking', fn ($query) => $query->where('customer_id', $user->id));
            });
        }

        if ($type === 'payment') {
            $payment = $paymentQuery->findOrFail($id);
            $invoice = $payment->invoice;
            $subscription = $payment->subscription;
            $booking = $payment->booking;

            return [
                'title' => 'Payment Receipt',
                'type' => 'payment',
                'id' => $payment->id,
                'download_route' => $user->role === 'partner'
                    ? 'partner.invoices.receipt.download'
                    : 'admin.invoices.receipt.download',
                'invoice' => $invoice,
                'payment' => $payment,
                'subscription' => $subscription,
                'booking' => $booking,
                'customer' => $subscription?->customer ?? $booking?->customer,
                'listing' => $subscription?->package?->listing ?? $booking?->package?->listing,
                'amount' => $payment->amount,
            ];
        }

        $invoice = $invoiceQuery->findOrFail($id);
        $subscription = $invoice->subscription;
        $payment = $invoice->payments()->latest()->first();

        return [
            'title' => 'Invoice Receipt',
            'type' => 'invoice',
            'id' => $invoice->id,
            'download_route' => $user->role === 'partner'
                ? 'partner.invoices.receipt.download'
                : 'admin.invoices.receipt.download',
            'invoice' => $invoice,
            'payment' => $payment,
            'subscription' => $subscription,
            'booking' => $invoice->booking,
            'customer' => $subscription?->customer ?? $invoice->booking?->customer,
            'listing' => $subscription?->package?->listing ?? $invoice->booking?->package?->listing,
            'amount' => $invoice->total,
        ];
    }

    private function renderPdfFromHtml(string $html, array $receipt): string
    {
        $tempDir = storage_path('app/tmp');
        File::ensureDirectoryExists($tempDir);

        $htmlFile = tempnam($tempDir, 'receipt_') . '.html';
        $pdfFile = tempnam($tempDir, 'receipt_') . '.pdf';

        File::put($htmlFile, $html);

        try {
            $browserBinary = $this->findBrowserBinary();

            if (! $browserBinary) {
                return SimplePdf::makeReceipt($receipt);
            }

            $process = new Process([
                $browserBinary,
                '--headless=new',
                '--disable-gpu',
                '--no-first-run',
                '--no-default-browser-check',
                '--disable-dev-shm-usage',
                '--no-sandbox',
                '--print-to-pdf=' . $pdfFile,
                '--print-to-pdf-no-header',
                'file:///' . str_replace('\\', '/', $htmlFile),
            ]);
            $process->setTimeout(60);
            $process->run();

            if (! $process->isSuccessful() || ! File::exists($pdfFile) || filesize($pdfFile) < 100) {
                \Illuminate\Support\Facades\Log::error('PDF Generation Failed', [
                    'exit_code' => $process->getExitCode(),
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                    'binary' => $browserBinary,
                ]);
                return SimplePdf::makeReceipt($receipt);
            }

            return File::get($pdfFile);
        } finally {
            if (File::exists($htmlFile)) {
                File::delete($htmlFile);
            }
            if (File::exists($pdfFile)) {
                File::delete($pdfFile);
            }
        }
    }

    private function findBrowserBinary(): ?string
    {
        $candidates = [
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\EdgeWebView\\Application\\msedge.exe',
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
