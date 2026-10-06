<?php

namespace App\Livewire\Partner;

use App\Models\KycDocument;
use App\Models\KycRequirement;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class KycUpload extends Component
{
    use WithFileUploads;
    use HasPartnerWorkspaceScope;

    public array $requirements = [];
    public array $textValues = [];
    public array $documentNumbers = [];
    public array $documentFiles = [];

    public ?KycDocument $kyc = null;

    public function mount(): void
    {
        $this->requirements = $this->loadRequirements();
        $partner = auth()->user()->isSuperAdmin()
            ? \App\Models\User::findOrFail($this->requirePartnerIdForWrite())
            : auth()->user();
        $this->kyc = $partner->kycDocument;

        if ($this->kyc?->submission_data) {
            foreach ($this->kyc->submission_data as $key => $payload) {
                if (($payload['type'] ?? 'text') === 'document') {
                    $this->documentNumbers[$key] = $payload['value'] ?? '';
                    $this->documentFiles[$key] = $payload['files'] ?? [];
                } else {
                    $this->textValues[$key] = $payload['value'] ?? '';
                }
            }
        }
    }

    public function submit(): void
    {
        $partner = auth()->user()->isSuperAdmin()
            ? \App\Models\User::findOrFail($this->requirePartnerIdForWrite())
            : auth()->user();

        if ($this->kyc?->status === 'approved') {
            session()->flash('error', 'Approved KYC cannot be changed.');
            return;
        }

        $rules = [];
        foreach ($this->requirements as $requirement) {
            $key = $requirement['key'];
            $required = $requirement['is_required'];

            if ($requirement['field_type'] === 'document') {
                if (($requirement['has_value_field'] ?? true) === true) {
                    $rules["documentNumbers.$key"] = ($required ? 'required' : 'nullable') . '|string|max:255';
                }

                $frontRule = ($required || ($requirement['document_mode'] ?? 'single') !== 'single') ? 'required' : 'nullable';
                $rules["documentFiles.$key.front"] = "{$frontRule}|file|mimes:jpg,jpeg,png,pdf|max:5120";

                if (($requirement['document_mode'] ?? 'single') === 'front_back') {
                    $backRule = $required ? 'required' : 'nullable';
                    $rules["documentFiles.$key.back"] = "{$backRule}|file|mimes:jpg,jpeg,png,pdf|max:5120";
                }
            } else {
                $rules["textValues.$key"] = ($required ? 'required' : 'nullable') . '|string|max:255';
            }
        }

        $this->validate($rules);

        $submissionData = [];

        foreach ($this->requirements as $requirement) {
            $key = $requirement['key'];

            if ($requirement['field_type'] === 'document') {
                $frontPath = $this->documentFiles[$key]['front'] ?? null;
                $backPath = $this->documentFiles[$key]['back'] ?? null;

                if (isset($this->documentFiles[$key]['front']) && is_object($this->documentFiles[$key]['front'])) {
                    $frontPath = $this->documentFiles[$key]['front']->store("kyc/" . $partner->id . "/{$key}/front", 'public');
                }

                if (isset($this->documentFiles[$key]['back']) && is_object($this->documentFiles[$key]['back'])) {
                    $backPath = $this->documentFiles[$key]['back']->store("kyc/" . $partner->id . "/{$key}/back", 'public');
                }

                $submissionData[$key] = [
                    'label' => $requirement['label'],
                    'type' => 'document',
                    'value_label' => $requirement['value_label'] ?? null,
                    'value' => $this->documentNumbers[$key] ?? null,
                    'document_mode' => $requirement['document_mode'] ?? 'single',
                    'files' => array_filter([
                        'front' => $frontPath,
                        'back' => $backPath,
                    ]),
                ];
            } else {
                $submissionData[$key] = [
                    'label' => $requirement['label'],
                    'type' => 'text',
                    'input_type' => $requirement['input_type'] ?? 'text',
                    'value' => $this->textValues[$key] ?? null,
                ];
            }
        }

        $this->kyc = KycDocument::updateOrCreate(
            ['partner_id' => $partner->id],
            [
                'submission_data' => $submissionData,
                'status' => 'pending',
                'submitted_at' => now(),
                'reviewed_at' => null,
                'aadhaar_number' => $this->documentNumbers['aadhaar_card'] ?? $this->textValues['aadhaar_number'] ?? null,
                'pan_number' => $this->documentNumbers['pan_card'] ?? $this->textValues['pan_number'] ?? null,
                'gst_number' => $this->textValues['gst_number'] ?? null,
                'bank_details' => [
                    'account_name' => $this->textValues['account_name'] ?? null,
                    'bank_name' => $this->textValues['bank_name'] ?? null,
                    'account_number' => $this->textValues['account_number'] ?? null,
                    'ifsc_code' => $this->textValues['ifsc_code'] ?? null,
                ],
            ]
        );

        try {
            Mail::to($partner->email)->queue(new \App\Mail\PartnerKycSubmittedMail($partner));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send KYC submitted mail: ' . $e->getMessage());
        }

        if ($partner->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $partner->fcm_token,
                'KYC Submitted',
                'Your KYC documents have been submitted successfully and are pending review.'
            );
        }

        session()->flash('success', 'KYC documents submitted successfully and are pending review.');

        $this->redirect(route('partner.kyc'));
    }

    public function render()
    {
        return view('livewire.partner.kyc-upload')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'KYC Verification',
                'pageSubtitle' => 'Submit your business documents for approval',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }

    private function loadRequirements(): array
    {
        $requirements = KycRequirement::active()->orderBy('sort_order')->get();

        if ($requirements->isNotEmpty()) {
            return $requirements->map(fn (KycRequirement $requirement) => [
                'id' => $requirement->id,
                'label' => $requirement->label,
                'key' => $requirement->key,
                'field_type' => $requirement->field_type,
                'input_type' => $requirement->input_type,
                'has_value_field' => $requirement->has_value_field,
                'document_mode' => $requirement->document_mode,
                'value_label' => $requirement->value_label,
                'placeholder' => $requirement->placeholder,
                'help_text' => $requirement->help_text,
                'is_required' => $requirement->is_required,
            ])->all();
        }

        return [
            [
                'label' => 'Aadhaar Card',
                'key' => 'aadhaar_card',
                'field_type' => 'document',
                'input_type' => 'text',
                'has_value_field' => true,
                'document_mode' => 'front_back',
                'value_label' => 'Aadhaar Number',
                'placeholder' => '12 digit Aadhaar number',
                'help_text' => 'Upload Aadhaar front and back side images.',
                'is_required' => true,
            ],
            [
                'label' => 'PAN Card',
                'key' => 'pan_card',
                'field_type' => 'document',
                'input_type' => 'text',
                'has_value_field' => true,
                'document_mode' => 'front_back',
                'value_label' => 'PAN Number',
                'placeholder' => '10 character PAN',
                'help_text' => 'Upload PAN front and back side images.',
                'is_required' => true,
            ],
            [
                'label' => 'GST Number',
                'key' => 'gst_number',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => '15 character GSTIN',
                'help_text' => null,
                'is_required' => false,
            ],
            [
                'label' => 'Account Holder Name',
                'key' => 'account_name',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'Name on bank account',
                'help_text' => null,
                'is_required' => true,
            ],
            [
                'label' => 'Bank Name',
                'key' => 'bank_name',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'e.g. HDFC Bank',
                'help_text' => null,
                'is_required' => true,
            ],
            [
                'label' => 'Account Number',
                'key' => 'account_number',
                'field_type' => 'text',
                'input_type' => 'number',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'Bank account number',
                'help_text' => null,
                'is_required' => true,
            ],
            [
                'label' => 'IFSC Code',
                'key' => 'ifsc_code',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'Bank IFSC code',
                'help_text' => null,
                'is_required' => true,
            ],
        ];
    }
}
