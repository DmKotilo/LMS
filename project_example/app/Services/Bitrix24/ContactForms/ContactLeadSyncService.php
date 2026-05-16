<?php

declare(strict_types=1);

namespace App\Services\Bitrix24\ContactForms;

use ContactForm\Models\CallContactForm;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContactLeadSyncService
{
    private const FORM_TITLES = [
        'call_form' => 'Форма: Остались вопросы?',
        'question_form' => 'Форма: Обратная связь',
        'interview' => 'Форма: Присоединяйся к команде',
    ];

    public function sync(CallContactForm $form): void
    {
        if (!config('services.bitrix24.enabled', false)) {
            return;
        }

        try {
            $contactId = $this->createContact($form);
            $this->createLead($form, $contactId);
        } catch (\Throwable $exception) {
            Log::channel('bitrix24')->error('Ошибка синхронизации заявки с Bitrix24', [
                'form_id' => $form->id,
                'form_type' => $form->type->value ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function createContact(CallContactForm $form): ?int
    {
        $fields = [
            'NAME' => $form->name ?: 'Посетитель сайта',
            'SOURCE_ID' => 'WEB',
        ];

        if ($form->email) {
            $fields['EMAIL'] = [[
                'VALUE' => $form->email,
                'VALUE_TYPE' => 'WORK',
            ]];
        }

        $phone = $this->formatPhone($form->phone);
        if ($phone) {
            $fields['PHONE'] = [[
                'VALUE' => $phone,
                'VALUE_TYPE' => 'WORK',
            ]];
        }

        $response = $this->sendRequest('crm.contact.add', [
            'fields' => $fields,
        ]);

        $data = $response->json();
        if (!isset($data['result'])) {
            throw new \RuntimeException('Bitrix24 contact creation returned unexpected payload');
        }

        $contactId = (int) $data['result'];

        Log::channel('bitrix24')->info('Создан контакт в Bitrix24 из формы', [
            'form_id' => $form->id,
            'contact_id' => $contactId,
        ]);

        return $contactId;
    }

    private function createLead(CallContactForm $form, ?int $contactId): void
    {
        $fields = [
            'TITLE' => $this->leadTitle($form),
            'SOURCE_ID' => 'WEB',
            'COMMENTS' => $this->buildComment($form),
            'OPENED' => 'Y',
        ];

        if (!empty($form->utm_campaign)) {
            $fields['UTM_CAMPAIGN'] = (string) $form->utm_campaign;
        }
        if (!empty($form->utm_content)) {
            $fields['UTM_CONTENT'] = (string) $form->utm_content;
        }
        if (!empty($form->utm_medium)) {
            $fields['UTM_MEDIUM'] = (string) $form->utm_medium;
        }
        if (!empty($form->utm_source)) {
            $fields['UTM_SOURCE'] = (string) $form->utm_source;
        }
        if (!empty($form->utm_term)) {
            $fields['UTM_TERM'] = (string) $form->utm_term;
        }

        if ($form->email) {
            $fields['EMAIL'] = [[
                'VALUE' => $form->email,
                'VALUE_TYPE' => 'WORK',
            ]];
        }

        $phone = $this->formatPhone($form->phone);
        if ($phone) {
            $fields['PHONE'] = [[
                'VALUE' => $phone,
                'VALUE_TYPE' => 'WORK',
            ]];
        }

        if ($contactId) {
            $fields['CONTACT_ID'] = $contactId;
        }

        $response = $this->sendRequest('crm.lead.add', [
            'fields' => $fields,
        ]);

        $data = $response->json();
        if (!isset($data['result'])) {
            throw new \RuntimeException('Bitrix24 lead creation returned unexpected payload');
        }

        Log::channel('bitrix24')->info('Создан лид в Bitrix24 из формы', [
            'form_id' => $form->id,
            'lead_id' => $data['result'],
        ]);
    }

    private function leadTitle(CallContactForm $form): string
    {
        return self::FORM_TITLES[$form->type->value] ?? 'Форма обратной связи';
    }

    private function buildComment(CallContactForm $form): string
    {
        $parts = array_filter([
            "Имя: {$form->name}",
            $form->phone ? "Телефон: {$form->phone}" : null,
            $form->email ? "Email: {$form->email}" : null,
            $form->post ? "Должность: {$form->post}" : null,
            $form->time_interval ? "Удобное время: {$form->time_interval}" : null,
            $form->comment ? "Комментарий: {$form->comment}" : null,
            $form->type ? 'Тип формы: ' . $form->type->toString() : null,
        ]);
        $utmParts = array_filter([
            !empty($form->utm_source) ? "UTM_SOURCE: {$form->utm_source}" : null,
            !empty($form->utm_medium) ? "UTM_MEDIUM: {$form->utm_medium}" : null,
            !empty($form->utm_campaign) ? "UTM_CAMPAIGN: {$form->utm_campaign}" : null,
            !empty($form->utm_content) ? "UTM_CONTENT: {$form->utm_content}" : null,
            !empty($form->utm_term) ? "UTM_TERM: {$form->utm_term}" : null,
        ]);
        if (!empty($utmParts)) {
            $parts[] = '---';
            $parts[] = 'UTM метки:';
            $parts = array_merge($parts, $utmParts);
        }

        return implode(PHP_EOL, $parts);
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


