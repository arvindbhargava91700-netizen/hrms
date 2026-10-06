<?php

namespace Tests\Feature;

use App\Models\KycDocument;
use App\Models\KycRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KycDynamicFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_dynamic_kyc_field(): void
    {
        KycRequirement::create([
            'label' => 'Passport Card',
            'key' => 'passport_card',
            'field_type' => 'document',
            'input_type' => 'text',
            'has_value_field' => true,
            'document_mode' => 'front_back',
            'value_label' => 'Passport Number',
            'placeholder' => 'Passport number',
            'help_text' => 'Upload front and back',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('kyc_requirements', [
            'label' => 'Passport Card',
            'key' => 'passport_card',
            'field_type' => 'document',
            'document_mode' => 'front_back',
        ]);
    }

    public function test_kyc_document_exposes_dynamic_submission_summary(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('kyc/partner/aadhaar/front.jpg', 'front');
        Storage::disk('public')->put('kyc/partner/aadhaar/back.jpg', 'back');

        $partner = User::factory()->create([
            'role' => 'partner',
            'status' => 'active',
        ]);

        $kyc = KycDocument::create([
            'partner_id' => $partner->id,
            'submission_data' => [
                'aadhaar_card' => [
                    'label' => 'Aadhaar Card',
                    'type' => 'document',
                    'value_label' => 'Aadhaar Number',
                    'value' => '123412341234',
                    'document_mode' => 'front_back',
                    'files' => [
                        'front' => 'kyc/partner/aadhaar/front.jpg',
                        'back' => 'kyc/partner/aadhaar/back.jpg',
                    ],
                ],
            ],
            'status' => 'pending',
        ]);

        $summary = $kyc->submission_summary;

        $this->assertSame('Aadhaar Card', $summary['aadhaar_card']['label']);
        $this->assertSame('123412341234', $summary['aadhaar_card']['value']);
        $this->assertSame('front_back', $summary['aadhaar_card']['document_mode']);
        $this->assertNotNull($kyc->getStoredFileUrl($summary['aadhaar_card']['files']['front']));
    }

}
