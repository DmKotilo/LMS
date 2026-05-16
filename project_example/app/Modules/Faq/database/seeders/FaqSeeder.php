<?php

namespace Faq\Database\Seeders;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Faq\src\Enums\FaqSectionPageSlugEnum;
use App\Modules\Faq\src\Models\FaqSection;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Cart\Models\UserCart;
use Combo\Models\Combo;
use Faq\Models\Faq;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Product\Models\Product;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $description = '<div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
<p>Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после сборки заказа вес может незначительно поменяться.</p>
<p>Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС, отправителем указан PrimeFoods.</p>
</div>
';


        $sections = [
            'Доставка' => [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ],
            'Оплата' => [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ],
            'Качество мяса' => [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ],
            'Хранение' => [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ],
            'Страница промоакций' => [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ],
            'Страница комбо-наборов' => [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ],
        ];


        // Создаем обычные секции (page_slug = null)
        foreach ($sections as $sectionName => $faqs) {
            $section = FaqSection::updateOrCreate(
                ['name' => $sectionName],
                [
                    'is_default' => false, // Явно указываем, что не системная (если поле остается)
                    'page_slug' => null,   // Явно указываем, что не системная
                ]
            );
            $this->createFaqs($section, $faqs, $description);
        }

        // Создаем/обновляем системные секции
        $systemSections = [
            'Страница промоакций' => FaqSectionPageSlugEnum::Promo,
            'Страница комбо-наборов' => FaqSectionPageSlugEnum::Combo,
        ];

        foreach ($systemSections as $sectionName => $pageSlugEnum) {
            $section = FaqSection::updateOrCreate(
                ['name' => $sectionName],
                [
                    'is_default' => true,
                    'page_slug' => $pageSlugEnum,
                ]
            );
            $this->createFaqs($section, [
                'Как оформить заказ на сайте?',
                'Безопасность онлайн платежей',
                'Правила доставки товаров',
                'Возврат товаров',
            ], $description);
        }
    }
    private function createFaqs(FaqSection $section, array $faqNames, string $description): void
    {
        $position = 1;
        foreach ($faqNames as $faqName) {
            Faq::updateOrCreate(
                [
                    'section_id' => $section->id,
                    'name' => $faqName,
                ],
                [
                    'position' => $position++,
                    'description' => $description,
                    'button_text' => 'Подробнее',
                    'link_button' => '#',
                ]
            );
        }
    }


}
