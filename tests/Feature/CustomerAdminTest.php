<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\CustomerExportController;
use App\Livewire\Admin\CustomerView;
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

class CustomerAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_export_applies_filters(): void
    {
        $this->createCustomerBundle('Alice Example', 'alice@example.com', 'active', true);
        $this->createCustomerBundle('Bob Example', 'bob@example.com', 'suspended', false);

        $request = Request::create('/admin/customers/export', 'GET', [
            'search' => 'Alice',
            'status' => 'active',
            'subscription' => 'has_active',
            'sort' => 'name',
        ]);

        $response = app(CustomerExportController::class)($request);

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('Alice Example', $csv);
        $this->assertStringNotContainsString('Bob Example', $csv);
        $this->assertStringStartsWith('attachment; filename=customers-', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_customer_detail_page_shows_relations(): void
    {
        $bundle = $this->createCustomerBundle('Alice Example', 'alice@example.com', 'active', true);

        $component = app(CustomerView::class);
        $component->mount($bundle['customer']->id);

        $this->assertSame('Alice Example', $component->customer->name);
        $this->assertCount(1, $component->customer->subscriptions);
        $this->assertCount(1, $component->customer->payments);
        $this->assertCount(1, $component->customer->subscriptions->first()->invoices);
    }

    private function createCustomerBundle(string $name, string $email, string $status, bool $activeSubscription): array
    {
        $customer = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'mobile' => fake()->unique()->numerify('9#########'),
            'role' => 'customer',
            'status' => $status,
            'email_verified_at' => now(),
        ]);

        $partner = User::factory()->create([
            'name' => $name . ' Partner',
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->unique()->numerify('8#########'),
            'role' => 'partner',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Hostel',
            'slug' => 'hostel-' . str()->slug($name),
            'icon' => 'bi-house',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $listing = Listing::create([
            'partner_id' => $partner->id,
            'category_id' => $category->id,
            'title' => $name . ' Listing',
            'status' => 'approved',
        ]);

        $package = Package::create([
            'listing_id' => $listing->id,
            'name' => 'Standard Plan',
            'occupancy_type' => 'standard',
            'duration_days' => 30,
            'price' => 5000,
            'type' => 'monthly',
        ]);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(20),
            'status' => $activeSubscription ? 'active' : 'cancelled',
            'auto_renew' => true,
        ]);

        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-' . strtoupper(str()->random(8)),
            'amount' => 5000,
            'tax' => 0,
            'total' => 5000,
            'due_date' => now()->addDays(5),
            'status' => 'paid',
        ]);

        Payment::create([
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoice->id,
            'gateway' => 'razorpay',
            'gateway_ref' => 'PAY-' . strtoupper(str()->random(8)),
            'amount' => 5000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return compact('customer', 'partner', 'category', 'listing', 'package', 'subscription', 'invoice');
    }
}
