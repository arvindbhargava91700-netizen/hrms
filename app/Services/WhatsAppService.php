<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class WhatsAppService
{

    public static function sendMessage($mobile, $message)
    {

        $response = Http::get(env('WHATSBOT_API_URL').'/api/send_sms', [


            'api_token' => env('WHATSBOT_API_TOKEN'),


            'mobile'    => '91'. $mobile,


            'message'   => $message,


            // 'device_id' => env('WHATSBOT_DEVICE_ID'),


        ]);

        return $response->json();
        
    }

    public function sendWhatsappMessage($mobile, $message)
    {
        return self::sendMessage($mobile, $message);
    }

}