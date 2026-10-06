<?php

namespace Tests\Feature;

use App\Models\KycDocument;
use App\Models\User;
use App\Http\Middleware\EnsurePartnerKycApproved;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerKycGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_unapproved_partner_is_redirected_to_kyc_before_accessing_modules(): void
    {
        $partner = User::factory()->create([
            'role' => 'partner',
            'status' => 'active',
        ]);

        KycDocument::create([
            'partner_id' => $partner->id,
            'aadhaar_number' => '123412341234',
            'pan_number' => 'ABCDE1234F',
            'gst_number' => null,
            'bank_details' => [
                'account_name' => 'Test Partner',
                'bank_name' => 'Test Bank',
                'account_number' => '1234567890',
                'ifsc_code' => 'TEST0001234',
            ],
            'status' => 'pending',
        ]);

        $this->assertSame(route('partner.kyc'), $partner->dashboard_route);

        $request = Request::create('/partner/listings', 'GET');
        $request->setUserResolver(fn () => $partner);

        $response = app(EnsurePartnerKycApproved::class)->handle($request, fn () => response('ok'));

        $this->assertSame(route('partner.kyc'), $response->getTargetUrl());
    }

    public function test_approved_partner_can_access_partner_modules(): void
    {
        $partner = User::factory()->create([
            'role' => 'partner',
            'status' => 'active',
        ]);

        KycDocument::create([
            'partner_id' => $partner->id,
            'aadhaar_number' => '123412341234',
            'pan_number' => 'ABCDE1234F',
            'gst_number' => null,
            'bank_details' => [
                'account_name' => 'Test Partner',
                'bank_name' => 'Test Bank',
                'account_number' => '1234567890',
                'ifsc_code' => 'TEST0001234',
            ],
            'status' => 'approved',
        ]);

        $this->assertSame(route('partner.dashboard'), $partner->dashboard_route);

        $request = Request::create('/partner/listings', 'GET');
        $request->setUserResolver(fn () => $partner);

        $response = app(EnsurePartnerKycApproved::class)->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }
}
