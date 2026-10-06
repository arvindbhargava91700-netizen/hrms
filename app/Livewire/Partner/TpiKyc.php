<?php

namespace App\Livewire\Partner;

use App\Services\TpiPaymentService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class TpiKyc extends Component
{
    use WithFileUploads;
    use HasPartnerWorkspaceScope;

    // Required Form Data
    public $legal_name;
    public $business_name;
    public $email;
    public $contact_number;
    public $business_category = '';
    public $password;

    // Optional Form Data
    public $business_type;
    public $business_code;
    public $address;
    public $bank_name;
    public $account_number;
    public $ifsc_code;
    public $branch_name;
    public $account_holder_name;
    public $pin_code;
    public $state;
    public $city;
    
    // Documents
    public $requiredDocuments = [];
    public $uploadedDocuments = [];
    public $existingDocuments = [];

    public function mount()
    {
        $user = $this->workspacePartner();
        $merchant = $user->tpiMerchant;
        if ($merchant) {
            $this->legal_name = $merchant->details['legal_name'] ?? $this->legal_name;
            $this->business_name = $merchant->details['business_name'] ?? $this->business_name;
            $this->email = $merchant->details['email'] ?? $this->email;
            $this->contact_number = $merchant->details['contact_number'] ?? $this->contact_number;
            $this->business_category = $merchant->details['business_category'] ?? $this->business_category;

            $this->business_type = $merchant->details['business_type'] ?? null;
            $this->business_code = $merchant->details['business_code'] ?? null;
            $this->address = $merchant->details['address'] ?? null;
            $this->bank_name = $merchant->details['bank_name'] ?? null;
            $this->account_number = $merchant->details['account_number'] ?? null;
            $this->ifsc_code = $merchant->details['ifsc_code'] ?? null;
            $this->branch_name = $merchant->details['branch_name'] ?? null;
            $this->account_holder_name = $merchant->details['account_holder_name'] ?? null;
            $this->pin_code = $merchant->details['pin_code'] ?? null;
            $this->state = $merchant->details['state'] ?? null;
            $this->city = $merchant->details['city'] ?? null;
            
            $this->existingDocuments = $merchant->details['documents'] ?? [];
            
            if ($merchant->tpi_kyc_id && in_array(strtoupper($merchant->tpi_kyc_status), ['PENDING', 'INITIATED'])) {
                try {
                    $service = app(TpiPaymentService::class);
                    $statusResp = $service->checkKycStatus($merchant->tpi_kyc_id);
                    if (isset($statusResp['overallStatus'])) {
                        $merchant->update(['tpi_kyc_status' => $statusResp['overallStatus']]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update TPI KYC status: ' . $e->getMessage());
                }
            }
        }

        if ($this->business_type) {
            $this->fetchRequiredDocuments($this->business_type);
        }
    }

    public function updatedBusinessType($value)
    {
        if ($value) {
            $this->fetchRequiredDocuments($value);
        } else {
            $this->requiredDocuments = [];
        }
    }

    public function fetchRequiredDocuments($type)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get("https://api.tpipay.ai/kyc/public/required-documents/{$type}");
            if ($response->successful()) {
                $this->requiredDocuments = $response->json();
            } else {
                $this->requiredDocuments = [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch required documents: ' . $e->getMessage());
            $this->requiredDocuments = [];
        }
    }

    public function submit(TpiPaymentService $tpiService)
    {
        $this->validate([
            'legal_name' => 'required|string',
            'business_name' => 'required|string',
            'email' => 'required|email',
            'contact_number' => 'required|string',
            'business_category' => 'required|string',
            'password' => 'required|string|min:6',
            'business_type' => 'required|string',
            'business_code' => 'nullable|string',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'branch_name' => 'nullable|string',
            'account_holder_name' => 'nullable|string',
            'pin_code' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'uploadedDocuments' => 'array',
            'uploadedDocuments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $user = $this->workspacePartner();
            $merchant = $user->tpiMerchant;

            $payload = [
                'legal_name' => $this->legal_name,
                'business_name' => $this->business_name,
                'email' => $this->email,
                'contact_number' => $this->contact_number,
                'business_category' => $this->business_category,
                'operation' => 'create',
                'password' => $this->password,
                'business_type' => $this->business_type,
                'business_code' => $this->business_code,
                'address' => $this->address,
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'ifsc_code' => $this->ifsc_code,
                'branch_name' => $this->branch_name,
                'account_holder_name' => $this->account_holder_name,
                'pin_code' => $this->pin_code,
                'state' => $this->state,
                'city' => $this->city,
            ];

            if (!$merchant || !$merchant->tpi_merchant_id) {
                // 1. Register Merchant
                $registerResp = $tpiService->registerMerchant($payload);

                $merchantId = $registerResp['id'] ?? null;
                $kyc_id     = $registerResp['kycId'] ?? null;   
                
                if (!$merchantId) {
                    throw new \Exception('Merchant ID not returned from TPI Pay.');
                }

                $merchant = $user->tpiMerchant()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tpi_merchant_id'   => $merchantId,
                        'tpi_password'      => $this->password,
                        'tpi_kyc_id'            => $kyc_id,
                        'details'           => array_merge($payload, $registerResp)
                    ]
                );
            } else {
                $details = $merchant->details;
                $details = array_merge($details, $payload);
                $merchant->update(['details' => $details]);
            }

            // Extract relationshipManagerId
            $rmId = $merchant->details['relationship_manager_id'] ?? '236f71e6-d7a7-48e7-a825-f376ca28457f'; // fallback from user screenshot

            // 2. Initiate KYC
            if (!$merchant->tpi_kyc_id) {
                $kycResp = $tpiService->initiateKyc(
                    $merchant->tpi_merchant_id,
                    $rmId,
                    'EXT-REF-' . $user->id,
                    'https://app.feetrack.in/kyc-status' // Use real domain instead of route() which generates 0.0.0.0 locally, causing WAF 403 blocks
                );

                $kycId = $kycResp['kycId'] ?? null;
                if (!$kycId) {
                    throw new \Exception('KYC ID not returned from TPI Pay.');
                }

                $merchant->update(['tpi_kyc_id' => $kycId, 'tpi_kyc_status' => 'PENDING']);
            }

            // 3. Upload Documents
            if (!empty($this->uploadedDocuments)) {
                $savedDocuments = $merchant->details['documents'] ?? [];
                $documentsToUpload = [];

                foreach ($this->uploadedDocuments as $docType => $file) {
                    if ($file) {
                        $fileStream = fopen($file->getRealPath(), 'r');
                        $filename = $docType . '.' . $file->extension();
                        
                        $documentsToUpload[] = [
                            'content' => $fileStream,
                            'filename' => $filename,
                            'type' => $docType,
                        ];

                        // Save the file in storage for preview
                        $path = $file->store('kyc_documents', 'public');
                        $savedDocuments[$docType] = $path;
                    }
                }
                
                if (count($documentsToUpload) > 0) {
                    $tpiService->uploadDocuments($merchant->tpi_kyc_id, $documentsToUpload);
                }
                
                $details = $merchant->details;
                $details['documents'] = $savedDocuments;
                $merchant->update(['details' => $details]);
            }

            session()->flash('success', 'TPI Pay setup updated successfully!');
            $this->redirect(route('partner.tpi-kyc'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $merchant = $this->workspacePartner()->tpiMerchant;

        return view('livewire.partner.tpi-kyc', compact('merchant'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'TPI Gateway KYC',
                'pageSubtitle' => 'Complete your payment gateway setup',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }

    protected function workspacePartner(): \App\Models\User
    {
        return auth()->user()->isSuperAdmin()
            ? \App\Models\User::findOrFail($this->requirePartnerIdForWrite())
            : auth()->user();
    }
}
