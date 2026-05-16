<?php

namespace App\Services;

class OrganizationSchemaService
{
    /**
     * Генерация статической Schema.org разметки для организации
     *
     * @return array
     */
    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Prime Foods',
            'legalName' => 'ООО «ТД ПРАЙМ ФУДС»',
            'url' => 'https://shop.primefoods.ru',
            'logo' => asset('images/Logo.svg'),
            'description' => 'Официальный интернет-магазин Prime Foods. Премиальная мраморная говядина PrimeBeef, стейки и мясные деликатесы с доставкой по Москве и всей России.',
            
            // Адрес
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'RU',
                'addressLocality' => 'Москва',
                'streetAddress' => 'ул. Рябиновая, д.45, стр.4',
                'postalCode' => '121471',
            ],
            
            // Контакты
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+7 800 101-47-28',
                'email' => 'sales@primefoods.ru',
                'contactType' => 'sales',
                'areaServed' => 'RU',
                'availableLanguage' => 'Russian',
            ],
            
            // Дополнительные поля
            // 'sameAs' => [
            //     // Добавьте ссылки на соцсети если есть
            //     'https://vk.com/primefoods',
            //     'https://t.me/primefoods',
            // ],
        ];
    }

    /**
     * Получить данные организации
     *
     * @return array
     */
    public static function get(): array
    {
        return (new self())->generate();
    }
}

