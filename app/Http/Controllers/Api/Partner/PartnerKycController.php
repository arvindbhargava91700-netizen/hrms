<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\KycRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PartnerKycController extends Controller
{
    /**
     * Get KYC Requirements
     */
    public function requirements()
    {
        $requirements = KycRequirement::active()->orderBy('sort_order')->get();

        if ($requirements->isEmpty()) {
            $requirements = collect([
                [
                    'id' => null,
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
                    'id' => null,
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
                    'id' => null,
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
                    'id' => null,
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
                    'id' => null,
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
                    'id' => null,
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
                    'id' => null,
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
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $requirements
        ]);
    }

    /**
     * Show Partner's KYC
     */
    public function show(Request $request)
    {
        $kyc = KycDocument::where('partner_id', auth('partner_api')->id())->first();

        if (!$kyc) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'KYC profile not found.'
            ]);
        }

        $data = $kyc->toArray();
        
        // Convert paths to full URLs in submission_data
        if (!empty($data['submission_data'])) {
            foreach ($data['submission_data'] as $key => &$payload) {
                if (($payload['type'] ?? 'text') === 'document' && !empty($payload['files'])) {
                    if (isset($payload['files']['front'])) {
                        $payload['files']['front_url'] = $kyc->getStoredFileUrl($payload['files']['front']);
                    }
                    if (isset($payload['files']['back'])) {
                        $payload['files']['back_url'] = $kyc->getStoredFileUrl($payload['files']['back']);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Submit Partner KYC
     */
    public function submit(Request $request)
    {
        $partner = auth('partner_api')->user();
        $kyc = KycDocument::where('partner_id', $partner->id)->first();

        if ($kyc && $kyc->status === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Approved KYC cannot be changed.'
            ], 400);
        }

        $requirements = KycRequirement::active()->orderBy('sort_order')->get();
        if ($requirements->isEmpty()) {
            $requirements = collect($this->getDefaultRequirements());
        }

        $rules = [];
        foreach ($requirements as $requirement) {
            $key = $requirement['key'] ?? $requirement->key;
            $required = $requirement['is_required'] ?? $requirement->is_required;
            $fieldType = $requirement['field_type'] ?? $requirement->field_type;
            $hasValueField = $requirement['has_value_field'] ?? $requirement->has_value_field;
            $documentMode = $requirement['document_mode'] ?? $requirement->document_mode;

            if ($fieldType === 'document') {
                if ($hasValueField) {
                    $rules["documentNumbers.$key"] = ($required && !$kyc ? 'required' : 'nullable') . '|string|max:255';
                }

                $frontRule = ($required && !$kyc) ? 'required' : 'nullable';
                $rules["documentFiles.$key.front"] = "{$frontRule}|file|mimes:jpg,jpeg,png,pdf|max:5120";

                if ($documentMode === 'front_back') {
                    $backRule = ($required && !$kyc) ? 'required' : 'nullable';
                    $rules["documentFiles.$key.back"] = "{$backRule}|file|mimes:jpg,jpeg,png,pdf|max:5120";
                }
            } else {
                $rules["textValues.$key"] = ($required && !$kyc ? 'required' : 'nullable') . '|string|max:255';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $submissionData = $kyc ? $kyc->submission_data : [];

        foreach ($requirements as $requirement) {
            $key = $requirement['key'] ?? $requirement->key;
            $fieldType = $requirement['field_type'] ?? $requirement->field_type;

            if ($fieldType === 'document') {
                $frontPath = $submissionData[$key]['files']['front'] ?? null;
                $backPath = $submissionData[$key]['files']['back'] ?? null;

                if ($request->hasFile("documentFiles.$key.front")) {
                    $frontPath = $request->file("documentFiles.$key.front")->store("kyc/{$partner->id}/{$key}/front", 'public');
                }

                if ($request->hasFile("documentFiles.$key.back")) {
                    $backPath = $request->file("documentFiles.$key.back")->store("kyc/{$partner->id}/{$key}/back", 'public');
                }

                $submissionData[$key] = [
                    'label' => $requirement['label'] ?? $requirement->label,
                    'type' => 'document',
                    'value_label' => $requirement['value_label'] ?? $requirement->value_label ?? null,
                    'value' => $request->input("documentNumbers.$key", $submissionData[$key]['value'] ?? null),
                    'document_mode' => $requirement['document_mode'] ?? $requirement->document_mode ?? 'single',
                    'files' => array_filter([
                        'front' => $frontPath,
                        'back' => $backPath,
                    ]),
                ];
            } else {
                $submissionData[$key] = [
                    'label' => $requirement['label'] ?? $requirement->label,
                    'type' => 'text',
                    'input_type' => $requirement['input_type'] ?? $requirement->input_type ?? 'text',
                    'value' => $request->input("textValues.$key", $submissionData[$key]['value'] ?? null),
                ];
            }
        }

        $documentNumbers = $request->input('documentNumbers', []);
        $textValues = $request->input('textValues', []);

        $kyc = KycDocument::updateOrCreate(
            ['partner_id' => $partner->id],
            [
                'submission_data' => $submissionData,
                'status' => 'pending',
                'submitted_at' => now(),
                'reviewed_at' => null,
                'aadhaar_number' => $documentNumbers['aadhaar_card'] ?? $textValues['aadhaar_number'] ?? ($kyc->aadhaar_number ?? null),
                'pan_number' => $documentNumbers['pan_card'] ?? $textValues['pan_number'] ?? ($kyc->pan_number ?? null),
                'gst_number' => $textValues['gst_number'] ?? ($kyc->gst_number ?? null),
                'bank_details' => [
                    'account_name' => $textValues['account_name'] ?? ($kyc->bank_details['account_name'] ?? null),
                    'bank_name' => $textValues['bank_name'] ?? ($kyc->bank_details['bank_name'] ?? null),
                    'account_number' => $textValues['account_number'] ?? ($kyc->bank_details['account_number'] ?? null),
                    'ifsc_code' => $textValues['ifsc_code'] ?? ($kyc->bank_details['ifsc_code'] ?? null),
                ],
            ]
        );

        try {
            Mail::to($partner->email)->queue(new \App\Mail\PartnerKycSubmittedMail($partner));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send api partner KYC submitted mail: ' . $e->getMessage());
        }

        if ($partner->fcm_token) {
            try {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $partner->fcm_token,
                    'KYC Submitted',
                    'Your KYC documents have been submitted successfully and are pending review.',
                    [], null, false
                );
            } catch (\Exception $e) {
                // ignore
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'KYC documents submitted successfully and are pending review.'
        ]);
    }

    private function getDefaultRequirements()
    {
        return [
            [
                'label' => 'Aadhaar Card',
                'key' => 'aadhaar_card',
                'field_type' => 'document',
                'input_type' => 'text',
                'has_value_field' => true,
                'document_mode' => 'front_back',
                'value_label' => 'Aadhaar Number',
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
                'is_required' => true,
            ],
            [
                'label' => 'GST Number',
                'key' => 'gst_number',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'is_required' => false,
            ],
            [
                'label' => 'Account Holder Name',
                'key' => 'account_name',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'is_required' => true,
            ],
            [
                'label' => 'Bank Name',
                'key' => 'bank_name',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'is_required' => true,
            ],
            [
                'label' => 'Account Number',
                'key' => 'account_number',
                'field_type' => 'text',
                'input_type' => 'number',
                'has_value_field' => false,
                'document_mode' => 'single',
                'is_required' => true,
            ],
            [
                'label' => 'IFSC Code',
                'key' => 'ifsc_code',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'is_required' => true,
            ],
        ];
    }
}
