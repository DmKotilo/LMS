<?php

declare(strict_types=1);

namespace App\Services\Bitrix24;

use App\Modules\UserSetting\src\Enums\OftenTypeEnum;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class BitrixSubscriptionService
{
    private const FIELD_SMS = 'UF_CRM_1760432070848';
    private const FIELD_EMAIL = 'UF_CRM_1760432091372';
    private const FIELD_MESSENGER = 'UF_CRM_1760432113775';
    private const FIELD_FREQUENCY = 'UF_CRM_1760432628371';

    private const BOOL_MAP = [
        self::FIELD_SMS => [true => 9052, false => 9054],
        self::FIELD_EMAIL => [true => 9056, false => 9058],
        self::FIELD_MESSENGER => [true => 9060, false => 9062],
    ];

    private const FREQUENCY_MAP = [
        OftenTypeEnum::Daily->value => 9064,
        OftenTypeEnum::Weekly->value => 9066,
        OftenTypeEnum::SalesOnly->value => 9068,
    ];

    private const FREQUENCY_LABELS = [
        '' => 'Не выбрано',
        OftenTypeEnum::Daily->value => 'Каждый день',
        OftenTypeEnum::Weekly->value => 'Каждую неделю',
        OftenTypeEnum::SalesOnly->value => 'Только скидка',
    ];

    public function syncUser($user): void
    {
        if (!config('services.bitrix24.enabled', false)) {
            return;
        }

        $settings = $user->setting;
        if (!$settings) {
            return;
        }

        $fields = [];

        $smsField = $this->mapBooleanField(self::FIELD_SMS, $settings->sms_notifications);
        if ($smsField !== null) {
            $fields[self::FIELD_SMS] = $smsField;
        }

        $emailField = $this->mapBooleanField(self::FIELD_EMAIL, $settings->email_notifications);
        if ($emailField !== null) {
            $fields[self::FIELD_EMAIL] = $emailField;
        }

        $messengerField = $this->mapBooleanField(self::FIELD_MESSENGER, $settings->messenger_notifications);
        if ($messengerField !== null) {
            $fields[self::FIELD_MESSENGER] = $messengerField;
        }

        $fields[self::FIELD_FREQUENCY] = $this->mapFrequency($settings->often_type);

        $this->syncContact(
            fields: $fields,
            email: $user->email,
            phone: $user->phone !== null ? (string) $user->phone : null,
            name: $user->name ?? $user->email ?? 'Пользователь сайта'
        );
    }

    public function syncEmailOnly(string $email, ?string $name, bool $isActive): void
    {
        if (!config('services.bitrix24.enabled', false)) {
            return;
        }

        $emailField = $this->mapBooleanField(self::FIELD_EMAIL, $isActive);
        if ($emailField === null) {
            return;
        }

        $fields = [
            self::FIELD_EMAIL => $emailField,
        ];

        $this->syncContact(
            fields: $fields,
            email: $email,
            phone: null,
            name: $name ?? $email
        );
    }

    private function mapBooleanField(string $field, ?bool $value): ?array
    {
        if (!array_key_exists($field, self::BOOL_MAP) || !is_bool($value)) {
            return null;
        }

        $options = self::BOOL_MAP[$field];

        return [
            [
                'NAME' => 'Да',
                'VALUE' => $options[true],
                'IS_SELECTED' => $value === true,
            ],
            [
                'NAME' => 'Нет',
                'VALUE' => $options[false],
                'IS_SELECTED' => $value === false,
            ],
        ];
    }

    private function mapFrequency(?string $oftenType): array
    {
        $options = [[
            'NAME' => self::FREQUENCY_LABELS[''],
            'VALUE' => '',
            'IS_SELECTED' => $oftenType === null,
        ]];

        foreach (self::FREQUENCY_MAP as $type => $bitrixValue) {
            $options[] = [
                'NAME' => self::FREQUENCY_LABELS[$type] ?? $type,
                'VALUE' => $bitrixValue,
                'IS_SELECTED' => $oftenType === $type,
            ];
        }

        return $options;
    }

    private function syncContact(array $fields, ?string $email, ?string $phone, ?string $name): void
    {
        if (empty($fields)) {
            return;
        }

        try {
            $contactId = $this->findContactId($phone, $email);

            if ($contactId) {
                $this->updateContact($contactId, $fields);
                return;
            }

            $this->createContact($fields, $email, $phone, $name);

        } catch (\Throwable $exception) {
            Log::channel('bitrix24')->error('Ошибка синхронизации подписки с Bitrix24', [
                'email' => $email,
                'phone' => $phone,
                'fields' => $fields,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function findContactId(?string $phone, ?string $email): ?int
    {
        if ($phone) {
            $formatted = $this->formatPhone($phone);
            $variants = array_filter([
                $formatted,
                ltrim($formatted, '+'),
                preg_replace('/[^0-9]/', '', $formatted),
            ]);

            foreach ($variants as $variant) {
                $response = $this->sendRequest('crm.duplicate.findbycomm', [
                    'type' => 'PHONE',
                    'values' => [$variant],
                    'entity_type' => 'CONTACT',
                ]);

                $data = $response->json();
                if (isset($data['result']['CONTACT'][0])) {
                    return (int) $data['result']['CONTACT'][0];
                }
            }
        }

        if ($email) {
            $response = $this->sendRequest('crm.duplicate.findbycomm', [
                'type' => 'EMAIL',
                'values' => [mb_strtolower(trim($email))],
                'entity_type' => 'CONTACT',
            ]);

            $data = $response->json();
            if (isset($data['result']['CONTACT'][0])) {
                return (int) $data['result']['CONTACT'][0];
            }
        }

        return null;
    }

    private function updateContact(int $contactId, array $fields): void
    {
        $this->sendRequest('crm.contact.update', [
            'id' => $contactId,
            'fields' => $fields,
        ]);

        Log::channel('bitrix24')->info('Контакт в Bitrix24 обновлён данными подписки', [
            'contact_id' => $contactId,
            'fields' => $fields,
        ]);
    }

    private function createContact(array $fields, ?string $email, ?string $phone, ?string $name): void
    {
        $contactData = [
            'NAME' => $name ?? 'Пользователь сайта',
            'SOURCE_ID' => 'WEB',
            'OPENED' => 'Y',
        ];

        if ($email) {
            $contactData['EMAIL'] = [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']];
        }

        if ($phone) {
            $contactData['PHONE'] = [['VALUE' => $this->formatPhone($phone), 'VALUE_TYPE' => 'WORK']];
        }

        $contactData = array_merge($contactData, $fields);

        $this->sendRequest('crm.contact.add', [
            'fields' => $contactData,
        ]);

        Log::channel('bitrix24')->info('Контакт в Bitrix24 создан с данными подписки', [
            'email' => $email,
            'fields' => $fields,
        ]);
    }

    private function formatPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }

        if (!str_starts_with($digits, '7')) {
            $digits = '7' . $digits;
        }

        return '+' . $digits;
    }

    private function sendRequest(string $method, array $payload): Response
    {
        $baseUrl = rtrim(config('services.bitrix24.url'), '/');

        return Http::acceptJson()
            ->retry(3, 200, function (\Exception $exception, PendingRequest $request) {
                return $exception instanceof ConnectionException;
            })
            ->post($baseUrl . '/' . ltrim($method, '/'), $payload);
    }
}

