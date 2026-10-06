<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Msg91Service
{
    public function sendSms(string $mobile, string $templateId, array $parameters = [])
    {
        try {
            
            $payload = [
                'template_id'      => $templateId,
                'short_url'        => '0',
                'short_url_expiry' => '',
                'realTimeResponse' => '1',
                'recipients'       => [
                    array_merge(
                        ['mobiles' => '91' . $mobile],
                        $parameters
                    )
                ]
            ];

            $response = Http::withHeaders([
                'authkey'      => env('MSG91_AUTH_KEY'),
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post(
                rtrim(env('MSG91_BASE_URL'), '/') . '/api/v5/flow/',
                $payload
            );

            return $response->json();

        } catch (\Throwable $th) {
            Log::info('MSG91 SMS', [
                'mobile' => $mobile,
                'error'  => $th->getMessage(),
            ]);
        }

    }
}