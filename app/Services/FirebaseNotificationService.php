<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Send a push notification to a specific FCM token.
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @param bool $logToDatabase
     * @return bool
     */
    public function sendNotification(string $token, string $title, string $body, array $data = [], ?string $imageUrl = null, bool $logToDatabase = true): bool
    {
        try {
            $envPath = env('FIREBASE_JSON_FILE', 'storage/app/firebase-credentials.json');
            $credentialsPath = base_path($envPath);
            if (!file_exists($credentialsPath)) {
                Log::warning('Firebase credentials file not found. Skipping push notification.');
                return false;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $credentials['project_id'] ?? null;
            if (!$projectId) {
                Log::warning('Firebase project_id not found in credentials.');
                return false;
            }

            $accessToken = $this->getAccessToken();

            $notificationPayload = [
                'title' => $title,
                'body' => $body,
            ];
            
            if ($imageUrl) {
                $notificationPayload['image'] = $imageUrl;
            }

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => $notificationPayload,
                ]
            ];

            if (!empty($data)) {
                $stringData = [];
                foreach ($data as $key => $value) {
                    $stringData[(string)$key] = (string)$value;
                }
                $payload['message']['data'] = $stringData;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                // Log to database
                if ($logToDatabase) {
                    try {
                        $user = \App\Models\User::where('fcm_token', $token)->first();
                        if ($user) {
                            \App\Models\AppNotification::create([
                                'user_id' => $user->id,
                                'title' => $title,
                                'message' => $body,
                                'image' => $imageUrl,
                                'data' => $data,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to log AppNotification: ' . $e->getMessage());
                    }
                }

                return true;
            }

            Log::error('FCM sending failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'token' => $token
            ]);

            if ($response->status() === 404 || $response->status() === 410) {
                $this->clearInvalidToken($token);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error sending Firebase notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear invalid/expired token from the user table.
     */
    protected function clearInvalidToken(string $token): void
    {
        try {
            \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
        } catch (\Exception $e) {
            Log::error('Failed to clear invalid FCM token: ' . $e->getMessage());
        }
    }

    /**
     * Generate OAuth2 Access Token using the service account JSON
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'firebase_fcm_access_token';
        if ($token = cache($cacheKey)) {
            return $token;
        }

        $envPath = env('FIREBASE_JSON_FILE', 'storage/app/firebase-credentials.json');
        $credentialsPath = base_path($envPath);
        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials file not found at {$credentialsPath}");
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        $privateKey = $credentials['private_key'] ?? null;
        $clientEmail = $credentials['client_email'] ?? null;

        if (!$privateKey || !$clientEmail) {
            throw new \Exception('Invalid firebase-credentials.json format. private_key and client_email are required.');
        }

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        
        $now = time();
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]));

        $signature = '';
        if (!openssl_sign("$header.$payload", $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception('Failed to sign JWT for Firebase authentication.');
        }

        $assertion = "$header.$payload." . $this->base64UrlEncode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to retrieve Firebase OAuth access token: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];
        
        cache([$cacheKey => $accessToken], now()->addMinutes(55));

        return $accessToken;
    }

    /**
     * Base64URL encoding helper
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
