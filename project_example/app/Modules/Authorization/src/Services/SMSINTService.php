<?php

namespace Authorization\Services;

use Authorization\Exceptions\CodeService\CodeServiceException;
use Authorization\Interfaces\Services\VerifyCodeServiceInterface;
use Authorization\Models\VerifyCode;
use Carbon\Carbon;

class SMSINTService
{

    protected function getAuthUrl()
    {
        return $baseUrl = "https://lcab.smsint.ru/json/v1.0/";
    }

    public function send(string $phone, $text)
    {
        $urlMethod = 'sms/send/text';

        // Нормализуем номер телефона для SMS сервиса
        // Убираем все нецифровые символы (включая +)
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        // Если номер начинается с 8, заменяем на 7
        if (str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }
        
        // Если номер не начинается с 7, добавляем 7
        if (!str_starts_with($digits, '7')) {
            $digits = '7' . $digits;
        }
        
        // Берем последние 11 цифр (7 + 10 цифр номера)
        $normalizedPhone = substr($digits, -11);

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', $this->getAuthUrl().$urlMethod, [
            'headers' => [
                'X-Token'     => env('SMS_SERVICE_API_KEY', null),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        "recipient" => $normalizedPhone,
                        'text' => $text,
                    ],
                ]
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $content = json_decode($response->getBody(), true);
    }
}
