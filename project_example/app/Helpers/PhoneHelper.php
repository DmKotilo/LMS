<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Форматирует номер телефона к виду +79994023368
     * Убирает все лишние символы и приводит к единому формату
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function formatPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Убираем все символы кроме цифр
        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Если номер пустой после очистки, возвращаем null
        if (empty($digits)) {
            return null;
        }

        // Если номер содержит 11 цифр и начинается с 8, заменяем первую 8 на 7
        if (strlen($digits) == 11 && str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }

        // Если номер содержит 11 цифр и начинается с 7, убираем первую 7 (остается 10 цифр)
        if (strlen($digits) == 11 && str_starts_with($digits, '7')) {
            $digits = substr($digits, 1);
        }

        // Если номер содержит больше 11 цифр, берем последние 10 цифр после первой 7 или 8
        if (strlen($digits) > 11) {
            if (str_starts_with($digits, '7') || str_starts_with($digits, '8')) {
                $digits = substr($digits, 1);
            }
            // Берем последние 10 цифр
            $digits = substr($digits, -10);
        }

        // Если номер не начинается с 7 или 8 и содержит меньше 10 цифр, добавляем недостающие
        // Если содержит 10 или более цифр, берем последние 10
        if (!str_starts_with($digits, '7') && !str_starts_with($digits, '8')) {
            $digits = substr($digits, -10);
        }

        // Гарантируем, что у нас ровно 10 цифр после +7
        $digits = substr($digits, -10);

        return '+7' . $digits;
    }
}
