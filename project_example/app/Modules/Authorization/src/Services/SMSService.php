<?php

namespace Authorization\Services;

use Authorization\Exceptions\CodeService\CodeServiceException;
use Authorization\Interfaces\Services\VerifyCodeServiceInterface;
use Authorization\Models\VerifyCode;
use Carbon\Carbon;

//сервис не используется, не актуально

class SMSService
{

    protected $auth = [
        'login' => 'email',
        'API_KEY' => 'api_key'
    ];

    protected function getAuthUrl()
    {
        return $baseUrl = "https://{$this->auth['login']}:{$this->auth['API_KEY']}@gate.smsaero.ru/v2";
    }

    public function send(string | array $phone, $text)
    {
        $urlMethod = '/sms/send';
        $sign = 'SMS Aero';

        $client = new \GuzzleHttp\Client();

        $numberKey = is_array($phone) ? "numbers" : "number";

        $response = $client->request('GET', $this->getAuthUrl().$urlMethod, ["query" => [
            "$numberKey" => $phone,
            'text' => $text,
            'sign' => $sign,
        ]]);

        $statusCode = $response->getStatusCode();
        $content = json_decode($response->getBody(), true);
    }
}
