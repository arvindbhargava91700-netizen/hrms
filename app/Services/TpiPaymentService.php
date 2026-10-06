<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TpiPaymentService
{
    protected string $baseUrl;
    protected string $relationshipManagerId;

    public function __construct()
    {
        // For production, these should ideally come from config('services.tpipay.base_url') etc.
        $this->baseUrl = config('services.tpipay.base_url', 'https://api.tpipay.ai');
        $this->relationshipManagerId = config('services.tpipay.rm_id', '87ecf09b-7bf0-4ec3-a46e-51f1f0ed44b0');
    }

    public function registerMerchant(array $data)
    {
        $response = Http::post("{$this->baseUrl}/merchants/upsert", $data);

        if (!$response->successful()) {
            Log::error('TPI Register Merchant Failed: ' . $response->body());
            throw new \Exception('Failed to register merchant with TPI Pay: ' . $response->body());
        }

        return $response->json();
    }

    public function initiateKyc(string $merchantId, string $relationshipManagerId, string $reference, string $callbackUrl)
    {
        $payload = [
            'merchantId' => (string) $merchantId,
            'relationshipManagerId' => $relationshipManagerId,
            'reference' => $reference,
            'callbackUrl' => $callbackUrl,
        ];

        $response = Http::post("{$this->baseUrl}/api/forward/kyc/initiate", $payload);

        if (!$response->successful()) {
            Log::error('TPI Initiate KYC Failed: ' . $response->body());
            throw new \Exception('Failed to initiate KYC with TPI Pay: ' . $response->body());
        }

        return $response->json();
    }

    // public function uploadDocuments(string $kycId, array $documents)
    // {
    //     $request = Http::withHeaders([
    //         'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    //         'Accept' => 'application/json',
    //     ])->asMultipart();

    //     foreach ($documents as $doc) {
    //         $request->attach(
    //             'files',
    //             $doc['content'],
    //             $doc['filename']
    //         );
    //         $request->attach(
    //             'documentTypes',
    //             $doc['type']
    //         );
    //     }

    //     $response = $request->post("{$this->baseUrl}/api/forward/kyc/{$kycId}/documents");

    //     if (!$response->successful()) {
    //         Log::error("TPI Upload Documents Failed: " . $response->body());
    //         throw new \Exception('Failed to upload documents to TPI Pay: ' . $response->body());
    //     }

    //     return $response->json();
    // }

    public function uploadDocuments(string $kycId, array $documents)
    {
        $results = [];

        foreach ($documents as $doc) {

            if (
                !isset($doc['type']) ||
                !isset($doc['content']) ||
                !isset($doc['filename'])
            ) {
                throw new \Exception('Invalid document data supplied.');
            }

            $url = "{$this->baseUrl}/api/forward/kyc/{$kycId}/document/{$doc['type']}";

            $response = Http::acceptJson()
                ->attach(
                    'file', // Change this to 'document' or 'files' only if the API documentation specifies a different field name.
                    $doc['content'],
                    $doc['filename']
                )
                ->post($url);

            Log::info('TPI Upload Document', [
                'url' => $url,
                'document_type' => $doc['type'],
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            if ($response->failed()) {
                throw new \Exception(
                    "Failed uploading {$doc['type']} ({$response->status()}): {$response->body()}"
                );
            }

            $results[$doc['type']] = $response->json();
        }

        return $results;
    }

    public function checkKycStatus(string $kycId)
    {
        $response = Http::get("{$this->baseUrl}/api/forward/kyc/{$kycId}/status");

        if (!$response->successful()) {
            Log::error('TPI Check KYC Status Failed: ' . $response->body());
            throw new \Exception('Failed to check KYC status: ' . $response->body());
        }

        return $response->json();
    }

    public function createPayment(array $data)
    {
        $payload = array_merge($data, [
            'action' => 'create',
            'requestFlow' => 'CUSTOM_CHECKOUT',
        ]);

        $response = Http::post("{$this->baseUrl}/api/forward/payment", $payload);

        if (!$response->successful()) {
            Log::error('TPI Create Payment Failed: ' . $response->body());
            throw new \Exception('Failed to create payment link: ' . $response->body());
        }

        return $response->json();
    }

    public function checkPaymentStatus(string $paymentId)
    {
        $payload = [
            'paymentId' => $paymentId,
            'action' => 'status',
        ];

        $response = Http::post("{$this->baseUrl}/api/forward/payment", $payload);

        if (!$response->successful()) {
            Log::error('TPI Check Payment Status Failed: ' . $response->body());
            throw new \Exception('Failed to check payment status: ' . $response->body());
        }

        return $response->json();
    }

    public function refundPayment(string $merchantId, string $paymentId, float $refundAmount)
    {
        $payload = [
            'merchantId' => $merchantId,
            'action' => 'refund',
            'paymentId' => $paymentId,
            'refundAmount' => $refundAmount,
        ];

        $response = Http::post("{$this->baseUrl}/api/forward/payment", $payload);

        if (!$response->successful()) {
            Log::error('TPI Refund Payment Failed: ' . $response->body());
            throw new \Exception('Failed to refund payment: ' . $response->body());
        }

        return $response->json();
    }
}
