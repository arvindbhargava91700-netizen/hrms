<?php

namespace Tests\Feature;

use App\Http\Controllers\InvoiceReceiptController;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class InvoiceReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_returns_pdf_bytes(): void
    {
        [$admin, $invoice] = $this->createInvoiceBundle();

        $request = Request::create('/admin/invoices/invoice/' . $invoice->id . '/receipt/download', 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = app(InvoiceReceiptController::class)->download($request, 'invoice', $invoice->id);
        $content = $response->getContent();

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-1.4', $content);
        $this->assertGreaterThan(1000, strlen($content));
    }

    public function test_show_renders_printable_receipt_page(): void
    {
        [$partner, $invoice] = $this->createInvoiceBundle();

        $request = Request::create('/partner/invoices/invoice/' . $invoice->id . '/receipt', 'GET');
        $request->setUserResolver(fn () => $partner);

        $response = app(InvoiceReceiptController::class)->show($request, 'invoice', $invoice->id);
        $html = $response->render();

        $this->assertStringContainsString('Invoice Receipt', $html);
        $this->assertStringContainsString('Print', $html);
        $this->assertStringContainsString('Download PDF', $html);
    }

    private function createInvoiceBundle(): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $partner = User::factory()->create(['role' => 'partner', 'status' => 'active']);

        $category = Category::create([
            'name' => 'Gym',
            'slug' => 'gym-receipt-test',
            'icon' => 'bi-heart-pulse',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $listing = Listing::create([
            'partner_id' => $partner->id,
            'category_id' => $category->id,
            'title' => 'Receipt Test Listing',
            'status' => 'approved',
        ]);

        $package = Package::create([
            'listing_id' => $listing->id,
            'name' => 'Receipt Test Plan',
            'occupancy_type' => 'standard',
            'duration_days' => 30,
            'price' => 1500,
            'type' => 'monthly',
        ]);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'starts_at' => now()->subDays(2),
            'expires_at' => now()->addDays(28),
            'status' => 'active',
            'auto_renew' => false,
        ]);

        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-' . strtoupper(str()->random(8)),
            'amount' => 1500,
            'tax' => 270,
            'total' => 1770,
            'due_date' => now()->addDays(5),
            'status' => 'paid',
        ]);

        Payment::create([
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoice->id,
            'gateway' => 'razorpay',
            'gateway_ref' => 'PAY-' . strtoupper(str()->random(8)),
            'amount' => 1770,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return [$partner, $invoice];
    }
}
