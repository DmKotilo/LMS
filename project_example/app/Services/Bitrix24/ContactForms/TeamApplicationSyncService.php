<?php

declare(strict_types=1);

namespace App\Services\Bitrix24\ContactForms;

use ContactForm\Models\CallContactForm;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamApplicationSyncService
{
    public function sync(CallContactForm $form): void
    {
        if (!config('services.bitrix24.enabled', false)) {
            return;
        }

        try {
            $entityTypeId = (int) config('services.bitrix24.team_form.entity_type_id', 1042);
            $categoryId = config('services.bitrix24.team_form.category_id');
            $stageId = config('services.bitrix24.team_form.stage_id');

            $comment = $form->comment;
            $utmLines = array_filter([
                !empty($form->utm_source) ? "UTM_SOURCE: {$form->utm_source}" : null,
                !empty($form->utm_medium) ? "UTM_MEDIUM: {$form->utm_medium}" : null,
                !empty($form->utm_campaign) ? "UTM_CAMPAIGN: {$form->utm_campaign}" : null,
                !empty($form->utm_content) ? "UTM_CONTENT: {$form->utm_content}" : null,
                !empty($form->utm_term) ? "UTM_TERM: {$form->utm_term}" : null,
            ]);
            if (!empty($utmLines)) {
                $prefix = $comment ? ($comment . PHP_EOL . PHP_EOL) : '';
                $comment = $prefix . 'UTM метки:' . PHP_EOL . implode(PHP_EOL, $utmLines);
            }

            $fields = array_filter([
                'title' => 'Резюме с сайта #' . $form->id,
                'ufCrm10_1739815994' => $form->name,
                'ufCrm10_1739816025' => $form->email,
                'ufCrm10_1739816040' => $form->phone,
                'ufCrm_676E43549C2BA' => $form->post,
                'ufCrm10_1739816055' => $comment,
                'categoryId' => $categoryId,
                'stageId' => $stageId,
            ], static fn ($value) => $value !== null && $value !== '');

            $payload = [
                'entityTypeId' => $entityTypeId,
                'fields' => $fields,
            ];

            $response = $this->sendRequest('crm.item.add', $payload);
            $data = $response->json();

            if (!isset($data['result']['item']['id'])) {
                throw new \RuntimeException('Bitrix24 team application creation returned unexpected payload');
            }

            Log::channel('bitrix24')->info('Создана заявка в Bitrix24 (команда)', [
                'form_id' => $form->id,
                'item_id' => $data['result']['item']['id'],
            ]);
        } catch (\Throwable $exception) {
            Log::channel('bitrix24')->error('Ошибка синхронизации заявки в команду с Bitrix24', [
                'form_id' => $form->id,
                'error' => $exception->getMessage(),
            ]);
        }
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


