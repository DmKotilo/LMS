<?php

namespace Order\Services;

use App\Modules\Order\src\Http\Resources\OrderUserBitrixResource;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Order\Models\Order;
use Order\Models\OrderItem;
use Order\Enums\OrderDeliveryTypeEnum;
use Product\Models\Product;
use Combo\Models\Combo;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;

class OrderBitrixService
{
    private const BITRIX_CATALOG_IBLOCK_ID = 25;
    private const DEFAULT_VAT_RATE = 10.0;
    /**
     * Основной метод для отправки заказа в Битрикс24
     * Создает контакт (если не существует) и лид.
     * UTM передаются отдельно (не из БД), в Bitrix уходят как UTM_CAMPAIGN, UTM_SOURCE и т.д.
     *
     * @param  array<string, string>  $utm  метки utm_campaign, utm_content, utm_medium, utm_source, utm_term
     */
    public static function push(Order $order, array $utm = []): void
    {
        // Проверяем, включена ли интеграция с Битрикс24
        if (!config('services.bitrix24.enabled', true)) {
            Log::channel('bitrix24')->info('Bitrix24 integration is disabled', ['order_id' => $order->id]);
            return;
        }

        Log::channel('bitrix24')->info('Starting Bitrix24 integration for order', ['order_id' => $order->id]);

        try {
            // Форматируем телефон для Битрикс24
            $formattedPhone = self::formatPhone($order->phone);

            Log::channel('bitrix24')->info('Starting contact search', [
                'order_id' => $order->id,
                'phone' => $order->phone,
                'formatted_phone' => $formattedPhone,
                'email' => $order->email
            ]);

            // Ищем контакт по телефону
            $contactId = null;
            if ($formattedPhone) {
                $contactId = self::findContactByPhone($formattedPhone);
                Log::channel('bitrix24')->info('Contact search by phone result', [
                    'order_id' => $order->id,
                    'formatted_phone' => $formattedPhone,
                    'contact_id' => $contactId
                ]);
            }

            // Если не найден по телефону, ищем по email
            if (!$contactId && $order->email) {
                $contactId = self::findContactByEmail($order->email);
                Log::channel('bitrix24')->info('Contact search by email result', [
                    'order_id' => $order->id,
                    'email' => $order->email,
                    'contact_id' => $contactId
                ]);
            }

            // Если контакт не найден, создаем новый
            if (!$contactId) {
                Log::channel('bitrix24')->info('Contact not found, creating new contact', [
                    'order_id' => $order->id,
                    'phone' => $formattedPhone,
                    'email' => $order->email
                ]);
                $contactId = self::createContact($order, $formattedPhone);
            } else {
                self::updateContactIfNeeded($contactId, $order, $formattedPhone);
                Log::channel('bitrix24')->info('Existing contact found, skipping creation', [
                    'order_id' => $order->id,
                    'contact_id' => $contactId
                ]);
            }

            // Создаем лид
            $leadId = self::createLead($order, $contactId, $utm);

            // Сохраняем lead_id в заказ
            $order->update(['bitrix_lead_id' => $leadId]);

            // Привязываем товары к лиду
            self::attachLeadProductRows($leadId, $order);

            Log::channel('bitrix24')->info('Order successfully processed in Bitrix24', [
                'order_id' => $order->id,
                'contact_id' => $contactId,
                'lead_id' => $leadId
            ]);

        } catch (\Exception $e) {
            // Пытаемся получить lead_id из заказа, если он был создан
            $order->refresh();
            $leadId = $order->bitrix_lead_id ?? null;

            Log::channel('bitrix24')->error('Bitrix24 integration failed', [
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    //обновление существующего контакта
    private static function updateContactIfNeeded(int $contactId, Order $order, string $phone): void
    {
        if (!$order->last_name) {
            return;
        }

        $fields = [
            'LAST_NAME' => $order->last_name,
            'ASSIGNED_BY_ID' => 3009,
        ];

        if ($order->name) {
            $fields['NAME'] = $order->name;
        }

        if ($phone) {
            $fields['PHONE'] = [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']];
        }

        self::sendRequest('crm.contact.update', [
            'id' => $contactId,
            'fields' => $fields,
        ]);

        Log::channel('bitrix24')->info('Contact updated with name data', [
            'contact_id' => $contactId,
            'fields' => $fields,
        ]);
    }


    /**
     * Форматирует номер телефона для Битрикс24
     */
    private static function formatPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Убираем все символы кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Если номер начинается с 8, заменяем на 7
        if (str_starts_with($phone, '8')) {
            $phone = '7' . substr($phone, 1);
        }

        // Если номер не начинается с 7, добавляем 7
        if (!str_starts_with($phone, '7')) {
            $phone = '7' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Ищет контакт по номеру телефона используя crm.duplicate.findbycomm
     */
    private static function findContactByPhone(string $phone): ?int
    {
        $method = 'crm.duplicate.findbycomm';

        // Пробуем разные варианты формата телефона
        $phoneVariants = [
            $phone, // +79991234567
            substr($phone, 1), // 79991234567 (без +)
            preg_replace('/[^0-9]/', '', $phone), // только цифры
        ];

        foreach ($phoneVariants as $phoneVariant) {
            // Используем правильный метод API Bitrix24 для поиска по телефону
            $body = [
                'type' => 'PHONE',
                'values' => [$phoneVariant],
                'entity_type' => 'CONTACT', // Ищем только среди контактов
            ];

            $response = self::sendRequest($method, $body);
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Логируем ответ для отладки
            Log::channel('bitrix24')->debug('Bitrix24 contact search by phone using duplicate.findbycomm', [
                'phone' => $phone,
                'phone_variant' => $phoneVariant,
                'request_body' => $body,
                'response' => $responseData
            ]);

            // Метод возвращает структуру: {"result": {"CONTACT": [id1, id2, ...]}, "time": {...}}
            if (isset($responseData['result']['CONTACT']) && is_array($responseData['result']['CONTACT']) && !empty($responseData['result']['CONTACT'])) {
                // Возвращаем первый найденный контакт
                $contactId = (int) $responseData['result']['CONTACT'][0];
                Log::channel('bitrix24')->info('Contact found by phone', [
                    'phone' => $phone,
                    'phone_variant' => $phoneVariant,
                    'contact_id' => $contactId,
                    'all_found_contacts' => $responseData['result']['CONTACT']
                ]);
                return $contactId;
            }
        }

        return null;
    }

    /**
     * Ищет контакт по email используя crm.duplicate.findbycomm
     */
    private static function findContactByEmail(string $email): ?int
    {
        $method = 'crm.duplicate.findbycomm';

        // Нормализуем email (нижний регистр, без пробелов)
        $normalizedEmail = mb_strtolower(trim($email));

        // Используем правильный метод API Bitrix24 для поиска по email
        $body = [
            'type' => 'EMAIL',
            'values' => [$normalizedEmail],
            'entity_type' => 'CONTACT', // Ищем только среди контактов
        ];

        $response = self::sendRequest($method, $body);
        $responseData = json_decode($response->getBody()->getContents(), true);

        // Логируем ответ для отладки
        Log::channel('bitrix24')->debug('Bitrix24 contact search by email using duplicate.findbycomm', [
            'email' => $email,
            'normalized_email' => $normalizedEmail,
            'request_body' => $body,
            'response' => $responseData
        ]);

        // Метод возвращает структуру: {"result": {"CONTACT": [id1, id2, ...]}, "time": {...}}
        if (isset($responseData['result']['CONTACT']) && is_array($responseData['result']['CONTACT']) && !empty($responseData['result']['CONTACT'])) {
            // Возвращаем первый найденный контакт
            $contactId = (int) $responseData['result']['CONTACT'][0];
            Log::channel('bitrix24')->info('Contact found by email', [
                'email' => $email,
                'normalized_email' => $normalizedEmail,
                'contact_id' => $contactId,
                'all_found_contacts' => $responseData['result']['CONTACT']
            ]);
            return $contactId;
        }

        return null;
    }

    /**
     * Создает новый контакт в Битрикс24
     */
    private static function createContact(Order $order, string $formattedPhone): int
    {
        $method = 'crm.contact.add';

        $contactData = [
            'NAME' => $order->name,
            'LAST_NAME' => $order->last_name,
            'ASSIGNED_BY_ID' => 3009,
            'SECOND_NAME' => $order->second_name ?? null,
            'PHONE' => [['VALUE' => $formattedPhone, 'VALUE_TYPE' => 'WORK']],
            'TYPE_ID' => 'CLIENT',
            'SOURCE_ID' => 'WEB',
            'UF_SITE_ID' => $order->id, // ID заказа на сайте
        ];

        // Добавляем email если есть
        if ($order->email) {
            $contactData['EMAIL'] = [['VALUE' => $order->email, 'VALUE_TYPE' => 'WORK']];
        }

        // Добавляем дату рождения если есть
        if ($order->user && $order->user->birth_date) {
            $contactData['BIRTHDATE'] = $order->user->birth_date->format('Y-m-d');
        }

        // Оборачиваем поля в объект fields согласно документации Bitrix24
        $requestBody = ['fields' => $contactData];

        $response = self::sendRequest($method, $requestBody);
        $responseData = json_decode($response->getBody()->getContents(), true);

        // Логируем ответ для отладки
        Log::channel('bitrix24')->debug('Bitrix24 contact creation response', [
            'order_id' => $order->id,
            'contact_data' => $contactData,
            'request_body' => $requestBody,
            'response' => $responseData
        ]);

        if (!isset($responseData['result'])) {
            Log::channel('bitrix24')->error('Failed to create contact in Bitrix24', [
                'order_id' => $order->id,
                'contact_data' => $contactData,
                'request_body' => $requestBody,
                'response' => $responseData
            ]);
            throw new \Exception('Failed to create contact in Bitrix24: ' . json_encode($responseData));
        }

        Log::channel('bitrix24')->info('Contact created successfully in Bitrix24', [
            'order_id' => $order->id,
            'contact_id' => $responseData['result']
        ]);

        return (int) $responseData['result'];
    }

    /**
     * Создает лид в Битрикс24.
     *
     * @param  array<string, string>  $utm  метки utm_campaign, utm_content, utm_medium, utm_source, utm_term
     */
    private static function createLead(Order $order, int $contactId, array $utm = []): int
    {
        $method = 'crm.lead.add';

        // Получаем способ доставки
        $deliveryMethod = self::getDeliveryMethodId($order);

        // Получаем город
        $cityId = self::getCityId($order);

        // Рассчитываем общую сумму скидки
        $calculatedDiscount = 0.0;
        if (!is_null($order->cart_sum) && !is_null($order->price_final)) {
            $baseSum = (float) $order->cart_sum + (float) ($order->delivery_price ?? 0);
            $calculatedDiscount = max(0.0, $baseSum - (float) $order->price_final);
        }
        if ($calculatedDiscount <= 0.0) {
            $calculatedDiscount = (float) ($order->personal_discount ?? 0)
                + (float) ($order->promocode_discount ?? 0)
                + (float) ($order->bonus_spent ?? 0);
        }

        $deliveryZoneName = null;
        if ($order->deliveryAddress && $order->deliveryAddress->deliveryZone) {
            $deliveryZoneName = $order->deliveryAddress->deliveryZone->name;
        }

        $deliveryDateFormatted = null;
        if ($order->delivery_date) {
            $deliveryDateFormatted = $order->delivery_date->format('Y-m-d');
        }

        $leadData = [
            'TITLE' => 'Заказ #' . $order->id . ' от ' . $order->name . $order->last_name,
            'ASSIGNED_BY_ID' => 3009,
            'CONTACT_ID' => $contactId,
            'NAME' => $order->name,
            'LAST_NAME' => $order->last_name,
            'SECOND_NAME' => $order->second_name ?? null,
            'SOURCE_ID' => 'WEB',
            'STATUS_ID' => 'NEW',
            'OPPORTUNITY' => $order->price_final ?? 0,
            'CURRENCY_ID' => 'RUB',
            'COMMENTS' => $order->comment ?? 'Заказ с сайта Primefoods',
            'UF_CRM_1655907258432' => $order->orderPromocode?->promocode,
            'UF_CRM_METRIKA_ID' => $order->metrica_client_id,
            'UF_CRM_1591193327' => $deliveryMethod,
            'UF_CRM_1591786690' => $cityId, // Город
            'UF_CRM_1760438385799' => (float) ($order->delivery_price ?? 0), // Стоимость доставки
            'UF_CRM_1760437867519' => (float) $calculatedDiscount, // Общая сумма скидки
            'PROPERTY_1164' => $order->id, // ID заказа на сайте
            'UF_CRM_1591786644' => $deliveryDateFormatted,
            'UF_CRM_1728980327251' => $deliveryZoneName,
            'UF_CRM_1594983143' => (int) ($order->delivery_price ?? 0),
            'UF_CRM_LEAD_IS_SITE' => true, //не работает в битриксе, создан новый UF_CRM_LEAD_IS_SITE_STR
            'UF_CRM_LEAD_IS_SITE_STR' => 'Y', // Для передеачи LEAD_IS_SIT
            'UF_CRM_1770374921' => true, // тоже Для передеачи LEAD_IS_SIT
        ];

        // UTM-метки в Битрикс — ключи в API в верхнем регистре: UTM_CAMPAIGN, UTM_SOURCE и т.д.
        if (!empty($utm['utm_campaign'])) {
            $leadData['UTM_CAMPAIGN'] = (string) $utm['utm_campaign'];
        }
        if (!empty($utm['utm_content'])) {
            $leadData['UTM_CONTENT'] = (string) $utm['utm_content'];
        }
        if (!empty($utm['utm_medium'])) {
            $leadData['UTM_MEDIUM'] = (string) $utm['utm_medium'];
        }
        if (!empty($utm['utm_source'])) {
            $leadData['UTM_SOURCE'] = (string) $utm['utm_source'];
        }
        if (!empty($utm['utm_term'])) {
            $leadData['UTM_TERM'] = (string) $utm['utm_term'];
        }

        // Добавляем телефон и email в лид
        if ($order->phone) {
            $leadData['PHONE'] = [['VALUE' => self::formatPhone($order->phone), 'VALUE_TYPE' => 'WORK']];
        }

        if ($order->email) {
            $leadData['EMAIL'] = [['VALUE' => $order->email, 'VALUE_TYPE' => 'WORK']];
        }

        // Добавляем поля доставки в зависимости от способа доставки
        if ($order->delivery_type?->value === OrderDeliveryTypeEnum::courier->value) {
            // Время доставки "С"
            if ($order->delivery_time_from) {
                $leadData['UF_CRM_1593851565'] = $order->delivery_time_from;
            }

            // Время доставки "По"
            if ($order->delivery_time_to) {
                $leadData['UF_CRM_1593851582'] = $order->delivery_time_to;
            }

            // Адрес доставки (для курьера)
            $deliveryAddress = $order->delivery_address_for_admin;
            if ($deliveryAddress) {
                $leadData['UF_CRM_1591112041002'] = $deliveryAddress;
            }
        } elseif ($order->delivery_type?->value === OrderDeliveryTypeEnum::pickup->value) {
            // Адрес пункта самовывоза
            $pickupAddress = $order->delivery_address_for_admin;
            if ($pickupAddress) {
                $leadData['UF_CRM_1591112041002'] = $pickupAddress;
            }
        }

        // Оборачиваем поля в объект fields согласно документации Bitrix24
        $requestBody = ['fields' => $leadData];

        $response = self::sendRequest($method, $requestBody);
        $responseData = json_decode($response->getBody()->getContents(), true);

        $leadId = isset($responseData['result']) ? (int) $responseData['result'] : null;

        // Логируем ответ для отладки
        Log::channel('bitrix24')->debug('Bitrix24 lead creation response', [
            'order_id' => $order->id,
            'contact_id' => $contactId,
            'lead_id' => $leadId,
            'lead_data' => $leadData,
            'request_body' => $requestBody,
            'response' => $responseData
        ]);

        if (!isset($responseData['result'])) {
            Log::channel('bitrix24')->error('Failed to create lead in Bitrix24', [
                'order_id' => $order->id,
                'contact_id' => $contactId,
                'lead_id' => $leadId,
                'lead_data' => $leadData,
                'request_body' => $requestBody,
                'response' => $responseData
            ]);
            throw new \Exception('Failed to create lead in Bitrix24: ' . json_encode($responseData));
        }

        Log::channel('bitrix24')->info('Lead created successfully in Bitrix24', [
            'order_id' => $order->id,
            'contact_id' => $contactId,
            'lead_id' => $leadId
        ]);

        return (int) $responseData['result'];
    }

    /**
     * Формирует и прикрепляет позиции товаров к лиду
     */
    private static function attachLeadProductRows(int $leadId, Order $order): void
    {
        // Обновляем заказ из БД, чтобы гарантированно получить все товары
        $order->refresh();

        // Загружаем товары заказа с их связанными товарами/комбо
        $order->load('items.item');

        $itemsCount = $order->items->count();
        Log::channel('bitrix24')->info('Starting product rows attachment', [
            'order_id' => $order->id,
            'lead_id' => $leadId,
            'items_count' => $itemsCount,
            'has_items_relation' => $order->relationLoaded('items')
        ]);

        $rows = [];
        $vatRateByProductId = [];
        $vatRateByVatId = [];

        foreach ($order->items as $orderItem) {
            $item = $orderItem->item;
            if (!$item) {
                continue;
            }

            // Если это обычный товар
            if ($item instanceof Product) {
                $bitrixProductId = self::ensureBitrixProductId($item);
                if ($bitrixProductId) {
                    $vatRate = self::resolveVatRate($item, $bitrixProductId, $vatRateByProductId, $vatRateByVatId);
                    
                    $quantity = (float) ($orderItem->quantity ?? 1);
                    // Защита от нуля: если количество = 0, заменяем на 1
                    if ($quantity <= 0) {
                        $quantity = 1.0;
                    }
                    
                    $row = [
                        'PRODUCT_ID' => $bitrixProductId,
                        'PRICE' => (float) ($orderItem->price_for_unit ?? $orderItem->price ?? 0),
                        'QUANTITY' => $quantity,
                        'CURRENCY_ID' => 'RUB',
                        'IS_GIFT' => $orderItem->is_gift ? 'Y' : 'N',
                        'taxIncluded' => 'Y',
                        'taxRate' => $vatRate,
                        'TAX_INCLUDED' => 'Y',
                        'TAX_RATE' => $vatRate,
//                        'taxIncluded' => 'Y',
//                        'taxRate' => $vatRate,
                        'discountRate' => $vatRate,
                    ];
                    if ($item->price_type === PriceTypeEnum::Per_Piece) {
                        $row['MEASURE_CODE'] = 9;
                    }

                    $rows[] = $row;
                }
                continue;
            }

            // Если это комбонабор
            if ($item instanceof Combo) {
                $comboId = $item->id;
                // Гарантируем, что продукты подгружены с pivot->price
                $comboProducts = $item->products()->withPivot('price')->get();
                foreach ($comboProducts as $product) {
                    $bitrixProductId = self::ensureBitrixProductId($product);
                    if (!$bitrixProductId) {
                        continue;
                    }
                    $vatRate = self::resolveVatRate($product, $bitrixProductId, $vatRateByProductId, $vatRateByVatId);
                    $priceOverride = $product->pivot?->price;
                    
                    $quantity = (float) ($orderItem->quantity ?? 1);
                    // Защита от нуля: если количество = 0, заменяем на 1
                    if ($quantity <= 0) {
                        $quantity = 1.0;
                    }
                    
                    $row = [
                        'PRODUCT_ID' => $bitrixProductId,
                        'PRICE' => (float) ($priceOverride ?? 0),
                        'QUANTITY' => $quantity,
                        'CURRENCY_ID' => 'RUB',
                        'COMMENT' => 'Комбонабор ID ' . $comboId,
                        'IS_GIFT' => $orderItem->is_gift ? 'Y' : 'N',
                        // 'TAX_INCLUDED' => 'Y',
                        // 'TAX_RATE' => $vatRate,
                        // Дублируем поля в формате crm.item.productrow.fields (по ТЗ)
                        'taxIncluded' => 'Y',
                        'taxRate' => $vatRate,
                        'discountRate' => $vatRate,
                    ];

                    // Если тип цены товара - за шт (не за кг), передаем MEASURE_CODE = 9
                    // Важно: crm.lead.productrows.set ожидает ключ MEASURE_CODE (UPPER_SNAKE_CASE),
                    // иначе Битрикс игнорирует значение и подставляет дефолтную единицу измерения товара.
                    if ($product->price_type === PriceTypeEnum::Per_Piece) {
                        $row['MEASURE_CODE'] = 9;
                    }

                    $rows[] = $row;
                }
            }
        }

        if (empty($rows)) {
            Log::channel('bitrix24')->warning('No product rows to attach to lead', [
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'items_count' => $itemsCount
            ]);
            return;
        }

        Log::channel('bitrix24')->info('Preparing to attach product rows', [
            'order_id' => $order->id,
            'lead_id' => $leadId,
            'rows_count' => count($rows),
            'rows' => $rows
        ]);

        $method = 'crm.lead.productrows.set';
        $body = [
            'id' => $leadId,
            'rows' => $rows,
        ];

        $response = self::sendRequest($method, $body);
        $responseData = json_decode($response->getBody()->getContents(), true);

        Log::channel('bitrix24')->debug('Bitrix24 lead productrows set response', [
            'order_id' => $order->id,
            'lead_id' => $leadId,
            'rows' => $rows,
            'response' => $responseData,
        ]);

        if (!isset($responseData['result']) || $responseData['result'] !== true) {
            Log::channel('bitrix24')->warning('Failed to attach product rows to lead in Bitrix24', [
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'rows' => $rows,
                'response' => $responseData,
            ]);
        }
    }

    /**
     * Определяет ставку НДС для товара в Битрикс24.
     * Если ставка не найдена, возвращает 10% по умолчанию.
     */
    private static function resolveVatRate(
        Product $product,
        ?int $bitrixProductId,
        array &$vatRateByProductId,
        array &$vatRateByVatId
    ): float {
        if ($bitrixProductId && isset($vatRateByProductId[$bitrixProductId])) {
            return $vatRateByProductId[$bitrixProductId];
        }

        $vatId = self::findBitrixVatId($product, $bitrixProductId);
        $usedDefault = false;
        if (!$vatId) {
            $rate = self::DEFAULT_VAT_RATE;
            $usedDefault = true;
        } else {
            if (!array_key_exists($vatId, $vatRateByVatId)) {
                $fetchedRate = self::getBitrixVatRateById($vatId);
                $vatRateByVatId[$vatId] = $fetchedRate ?? self::DEFAULT_VAT_RATE;
                if ($fetchedRate === null) {
                    $usedDefault = true;
                }
            }
            $rate = $vatRateByVatId[$vatId];
            if ($rate === self::DEFAULT_VAT_RATE) {
                $usedDefault = true;
            }
        }

        if ($bitrixProductId) {
            $vatRateByProductId[$bitrixProductId] = $rate;
        }

        Log::channel('bitrix24')->info('Bitrix24 vat rate resolved for product row', [
            'product_id' => $product->id,
            'bitrix_product_id' => $bitrixProductId,
            'xml_id' => $product->uuid_1c ?? null,
            'vat_id' => $vatId,
            'vat_rate' => $rate,
            'used_default_rate' => $usedDefault,
        ]);

        return $rate;
    }

    /**
     * Ищет НДС для товара в каталоге Битрикс24 по ID/XmlID.
     */
    private static function findBitrixVatId(Product $product, ?int $bitrixProductId): ?int
    {
        $filter = [
            'iblockId' => self::BITRIX_CATALOG_IBLOCK_ID,
        ];

        if ($bitrixProductId) {
            $filter['id'] = (string) $bitrixProductId;
        } elseif (!empty($product->uuid_1c)) {
            $filter['xmlId'] = (string) $product->uuid_1c;
        } else {
            return null;
        }

        $method = 'catalog.product.list';
        $body = [
            'select' => ['id', 'iblockId', 'name', 'vatId', 'xmlId'],
            'filter' => $filter,
        ];

        try {
            $response = self::sendRequest($method, $body);
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Проверяем наличие ошибок в ответе
            if (isset($responseData['error'])) {
                Log::channel('bitrix24')->warning('Bitrix24 catalog.product.list returned error', [
                    'product_id' => $product->id,
                    'bitrix_product_id' => $bitrixProductId,
                    'error' => $responseData['error'],
                ]);
                return null;
            }

            // Обрабатываем разные форматы ответа от catalog.product.list
            $products = [];
            if (isset($responseData['result'])) {
                // Если result - это массив товаров напрямую
                if (is_array($responseData['result']) && isset($responseData['result'][0])) {
                    $products = $responseData['result'];
                }
                // Если result содержит поле products
                elseif (isset($responseData['result']['products']) && is_array($responseData['result']['products'])) {
                    $products = $responseData['result']['products'];
                }
            }

            $vatId = null;
            if (!empty($products) && isset($products[0]['vatId'])) {
                $vatIdValue = $products[0]['vatId'];
                if (!empty($vatIdValue) && is_numeric($vatIdValue)) {
                    $vatId = (int) $vatIdValue;
                }
            }

            Log::channel('bitrix24')->debug('Bitrix24 catalog product vatId lookup', [
                'product_id' => $product->id,
                'bitrix_product_id' => $bitrixProductId,
                'xml_id' => $product->uuid_1c ?? null,
                'request_body' => $body,
                'response' => $responseData,
                'vat_id' => $vatId,
            ]);

            return $vatId;
        } catch (\Exception $e) {
            Log::channel('bitrix24')->error('Exception while fetching vatId from Bitrix24', [
                'product_id' => $product->id,
                'bitrix_product_id' => $bitrixProductId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Получает размер ставки НДС по ID ставки.
     */
    private static function getBitrixVatRateById(int $vatId): ?float
    {
        $method = 'catalog.vat.get';
        $body = [
            'id' => $vatId,
        ];

        try {
            $response = self::sendRequest($method, $body);
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Проверяем наличие ошибок в ответе
            if (isset($responseData['error'])) {
                Log::channel('bitrix24')->warning('Bitrix24 catalog.vat.get returned error', [
                    'vat_id' => $vatId,
                    'error' => $responseData['error'],
                ]);
                return null;
            }

            $rate = $responseData['result']['vat']['rate'] ?? null;

            Log::channel('bitrix24')->debug('Bitrix24 vat rate lookup', [
                'vat_id' => $vatId,
                'response' => $responseData,
                'rate' => $rate,
            ]);

            return is_numeric($rate) ? (float) $rate : null;
        } catch (\Exception $e) {
            Log::channel('bitrix24')->error('Exception while fetching vat rate from Bitrix24', [
                'vat_id' => $vatId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Обеспечивает наличие ID товара в Битрикс и возвращает его
     */
    private static function ensureBitrixProductId(Product $product): ?int
    {
        // Если уже сохранён ID в БД — используем его
        if (!empty($product->uuid_bitrix24)) {
            return (int) $product->uuid_bitrix24;
        }

        // Иначе создаём продукт в Битрикс
        $productData = self::makeBitrixProductPayload($product);
        // Оборачиваем поля в объект fields согласно документации Bitrix24
        $requestBody = ['fields' => $productData];
        $method = 'crm.product.add';
        $response = self::sendRequest($method, $requestBody);
        $responseData = json_decode($response->getBody()->getContents(), true);

        Log::channel('bitrix24')->debug('Bitrix24 product creation response', [
            'product_id' => $product->id,
            'product_data' => $productData,
            'request_body' => $requestBody,
            'response' => $responseData,
        ]);

        if (!isset($responseData['result'])) {
            Log::channel('bitrix24')->error('Failed to create product in Bitrix24', [
                'product_id' => $product->id,
                'product_data' => $productData,
                'request_body' => $requestBody,
                'response' => $responseData,
            ]);
            return null;
        }

        $bitrixId = (int) $responseData['result'];
        // Сохраняем ID в товар
        $product->uuid_bitrix24 = (string) $bitrixId;
        $product->save();
        return $bitrixId;
    }

    /**
     * Маппинг полей для создания товара в Битрикс по предоставленной спецификации
     */
    private static function makeBitrixProductPayload(Product $product): array
    {
        $now = now()->toIso8601String();
        $payload = [
            'NAME' => $product->name ?? ('Товар #' . $product->id),
            'CODE' => $product->slug ?? ('product_' . $product->id),
            'ACTIVE' => 'Y',
            'DESCRIPTION' => $product->description ?? null,
            'DESCRIPTION_TYPE' => 'html',
            'PRICE' => (float) ($product->total_price ?? 0),
            'CURRENCY_ID' => 'RUB',
            'PROPERTY_213' => $product->weight ?? 0, //средний вес
            'PROPERTY_1164' => $product->id, //id товара на сайте
            'PROPERTY_129' => $product->uuid_1c, //id товара на сайте

            // MEASURE — при наличии значения можно задать через env
        ];

        $payload['NAME'] = "(Web Test) ".$payload['NAME'] ;


        // Кастомные свойства, отмеченные «х», если есть данные

        // PROPERTY_145 — Картинки товара: пропускаем (передаётся отдельно, если понадобится)
        // PROPERTY_163 — Остаток склад ИМ: если есть соответствующее поле — можно заполнить
        if (property_exists($product, 'quantity') && is_numeric($product->quantity)) {
            $payload['PROPERTY_163'] = (int) $product->quantity;
        }


        return $payload;
    }

    /**
     * Получает ID способа доставки для Битрикс24
     * 9022 - курьерская доставка
     * 9020 - самовывоз
     */
    private static function getDeliveryMethodId(Order $order): ?string
    {
        // Получаем enum значение
        $deliveryType = $order->delivery_type?->value;

        if ($deliveryType === 'courier') {
            return '9022'; // Курьерская доставка
        } elseif ($deliveryType === 'pickup') {
            return '9020'; // Самовывоз
        }

        return null;
    }

    /**
     * Получает ID города для Битрикс24 из скриншота
     */
    private static function getCityId(Order $order): ?string
    {
        $cityId = null;
        $cityName = null;

        // Получаем city_id в зависимости от типа доставки
        if ($order->delivery_type?->value === 'courier' && $order->deliveryAddress) {
            $cityId = $order->deliveryAddress->city_id;
            if ($order->deliveryAddress->city) {
                $cityName = $order->deliveryAddress->city->name;
            }
        } elseif ($order->delivery_type?->value === 'pickup' && $order->pickupLocation) {
            $cityId = $order->pickupLocation->city_id;
            if ($order->pickupLocation->city_relation) {
                $cityName = $order->pickupLocation->city_relation->name;
            }
        }

        if (!$cityName) {
            return '9042'; // Другой город
        }

        // Маппинг городов из скриншота
        $cityMapping = [
            'Москва' => '515',
            'Санкт-Петербург' => '517',
            'Ростов-на-Дону' => '739',
            'Тула' => '741',
            'Нижний Новгород' => '743',
            'Великий Новгород' => '745',
            'Смоленск' => '747',
            'Краснодар' => '749',
            'Новосибирск' => '751',
            'Владивосток' => '753',
            'Пермь' => '755',
            'Калуга' => '757',
            'Уфа' => '759',
            'Саранск' => '761',
            'Ярославль' => '763',
            'Тверь' => '765',
            'Екатеринбург' => '767',
            'Самара' => '769',
            'Липецк' => '771',
            'Владимир' => '773',
            'Сочи' => '2901',
            'Воронеж' => '775',
            'Сергиев Посад' => '9040',
        ];

        return $cityMapping[$cityName] ?? '9042'; // По умолчанию "Другой город"
    }

    /**
     * Получить товары лида из Bitrix24
     *
     * @param int $leadId ID лида в Bitrix24
     * @return array Массив товаров лида
     */
    public static function getLeadProducts(int $leadId): array
    {
        $method = 'crm.lead.productrows.get';
        $body = [
            'id' => $leadId,
        ];

        try {
            $response = self::sendRequest($method, $body);
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseData['result'])) {
                Log::channel('bitrix24')->error('Failed to get lead products from Bitrix24', [
                    'lead_id' => $leadId,
                    'response' => $responseData,
                ]);
                return [];
            }

            Log::channel('bitrix24')->info('Lead products retrieved from Bitrix24', [
                'lead_id' => $leadId,
                'products_count' => count($responseData['result']),
            ]);

            return $responseData['result'];

        } catch (\Exception $e) {
            Log::channel('bitrix24')->error('Exception while getting lead products from Bitrix24', [
                'lead_id' => $leadId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Отправляет запрос к API Битрикс24
     */
    private static function sendRequest(string $method, array $body): Response
    {
        $url = config('services.bitrix24.url');

        return Http::acceptJson()
            ->retry(3, 100, function (\Exception $exception, PendingRequest $request) {
                return $exception instanceof ConnectionException;
            })
            ->post($url . $method, $body);
    }
}


