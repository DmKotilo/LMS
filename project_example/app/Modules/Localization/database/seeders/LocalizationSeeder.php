<?php

namespace Localization\Database\Seeders;
use Illuminate\Database\Seeder;
use Localization\Models\Lang;
use Localization\Models\LangStaticKey;
use Localization\Models\LangStaticKeyGroup;

class LocalizationSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['title' => 'en'],
            ['title' => 'ru'],
            ['title' => 'fr'],
            ['title' => 'ch'],
        ];

        foreach ($languages as $langData) {
            Lang::updateOrCreate(['title' => $langData['title']], $langData);
        }

        $langStaticKeyArray = [
//
            [
                'title' => 'header',
                'description' => 'Шапка сайта',
                'keys' => [
                    ['key' => 'enter', 'description' => 'Кнопка "Войти"', 'default' => 'Войти'],
                    ['key' => 'deliveryAndPayment', 'description' => 'Ссылка "Доставка и оплата"', 'default' => 'Доставка и оплата'],
                    ['key' => 'recipes', 'description' => 'Ссылка "Рецепты"', 'default' => 'Рецепты'],
                    ['key' => 'promo', 'description' => 'Ссылка "Акции"', 'default' => 'Акции'],
                    ['key' => 'catalog', 'description' => 'Кнопка "Каталог"', 'default' => 'Каталог'],
                    ['key' => 'search', 'description' => 'Поле поиска', 'default' => 'Поиск'],
                    ['key' => 'chooseCity', 'description' => 'Выбор города', 'default' => 'Выберите город'],
                    ['key' => 'cart', 'description' => 'Кнопка "Корзина"', 'default' => 'Корзина'],
                    ['key' => 'roundTheClock', 'description' => 'Слово "Круглосуточно"', 'default' => 'Круглосуточно'],
                ],

            ],
            [
                'title' => 'main_page',
                'description' => 'Главная страница',
                'keys' => [
                    ['key' => 'all', 'description' => 'Кнопка "Все"', 'default' => 'Все'],
                    ['key' => 'allCombo', 'description' => 'Кнопка "Все наборы"', 'default' => 'Все наборы'],
                    ['key' => 'special_offers', 'description' => 'Блок: "Специальные предложения"', 'default' => 'Специальные предложения'],
                    ['key' => 'recommended_products', 'description' => 'Блок: "Рекомендуемые товары"', 'default' => 'Рекомендуемые товары'],
                    ['key' => 'product_categories', 'description' => 'Заголовок "Категории продукции"', 'default' => 'Категории продукции'],
                    ['key' => 'novelty', 'description' => 'Заголовок "Новинки"', 'default' => 'Новинки'],
                    ['key' => 'recipes_for_cooking', 'description' => 'Заголовок "Рецепты для приготовления"', 'default' => 'Рецепты для приготовления'],
                    ['key' => 'combo_packs', 'description' => 'Заголовок "Комбо наборы"', 'default' => 'Комбо наборы'],
                    ['key' => 'user_reviews', 'description' => 'Заголовок "Отзывы клиентов"', 'default' => 'Отзывы клиентов'],
                ],

            ],
            [
                'title' => 'info_banners',
                'description' => 'Информационные баннеры',
                'keys' => [
                    ['key' => 'reg_title', 'description' => 'Заголовок первого блока','default' => 'Зарегистрируйся и получи доступ к Программе Привилегий и закрытым предложениям для участников'],
                    ['key' => 'reg_description', 'description' => 'Описание первого блока','default' => 'Присоединяйтесь к нашей Программе Привилегий и получите доступ к уникальным предложениям, скидкам и мероприятиям, доступным только для участников. Не упустите шанс быть в курсе всех новинок и специальных акций!'],
                    ['key' => 'loyalty_title', 'description' => 'Заголовок второго блока','default' => 'Гибкая бонусная программа'],
                    ['key' => 'loyalty_description_step_1', 'description' => 'Описание второго блока часть 1','default' => 'CASHBACK за покупки'],
                    ['key' => 'loyalty_description_step_2', 'description' => 'Описание второго блока часть 2','default' => 'от 2% до 5% за каждый заказ'],
                    ['key' => 'loyalty_addition_description', 'description' => 'Доп описание второго блока','default' => 'Баланс бонусных баллов можно проверить в личном кабинете'],
                ]
            ],
            [
                'title' => 'recipe_card',
                'description' => 'Карточка рецепта',
                'keys' => [
                    ['key' => 'portion', 'description' => 'Порция', 'default' => '{n} порций | {n} порция | {n} порции | {n} порций'],
                    ['key' => 'difficult', 'description' => 'Сложность', 'default' => 'Сложность'],
                    ['key' => 'minute', 'description' => 'Минута', 'default' => '{n} минут | {n} минута | {n} минуты | {n} минут'],
                    ['key' => 'hour', 'description' => 'Час', 'default' => '{n} часов | {n} час | {n} часа | {n} часов'],
                ],

            ],
            [
                'title' => 'product_card',
                'description' => 'Карточка товара',
                'keys' => [
                    ['key' => 'average_weight', 'description' => 'Средний вес', 'default' => 'Средний вес'],
                    ['key' => 'in_cart', 'description' => 'В корзину', 'default' => 'В корзину'],
                    ['key' => 'buy_now', 'description' => 'Купить сейчас', 'default' => 'Купить сейчас'],
                ],

            ],
            [
                'title' => 'product_detail_card',
                'description' => 'Детальная карточка товара',
                'keys' => [
                    [
                        'key' => 'reviews',
                        'description' => 'Отзывов',
                        'default' => '{n} Отзывов | {n} Отзыв | {n} Отзыва | {n} Отзывов'
                    ],
                    [
                        'key' => 'cashback',
                        'description' => 'Фраза "+n% кэшбека"',
                        'default' => 'кэшбека',
                    ],
                    [
                        'key' => 'article',
                        'description' => 'Артикул',
                        'default' => 'Артикул'
                    ],
                    [
                        'key' => 'delivery_info',
                        'description' => 'Информация о доставке',
                        'default' => 'Информация о доставке'
                    ],
                    [
                        'key' => 'delivery_address',
                        'description' => 'Адрес доставки',
                        'default' => 'Адрес доставки'
                    ],
                    [
                        'key' => 'city',
                        'description' => 'Город',
                        'default' => 'Город'
                    ],
                    [
                        'key' => 'pickup',
                        'description' => 'Самовывоз',
                        'default' => 'Самовывоз'
                    ],
                    [
                        'key' => 'all_delivery_terms',
                        'description' => 'Все условия доставки',
                        'default' => 'Все условия доставки'
                    ],
                    [
                        'key' => 'about_product',
                        'description' => 'О товаре',
                        'default' => 'О товаре'
                    ],
                    [
                        'key' => 'delivery',
                        'description' => 'Доставка',
                        'default' => 'Доставка'
                    ],
                    [
                        'key' => 'cutting',
                        'description' => 'Разделка',
                        'default' => 'Разделка'
                    ],
                    [
                        'key' => 'manufacturer',
                        'description' => 'Производитель',
                        'default' => 'Производитель'
                    ],
                    [
                        'key' => 'chef_recipes',
                        'description' => 'Рецепты от Шеф-повара',
                        'default' => 'Рецепты от Шеф-повара'
                    ],
                    [
                        'key' => 'often_bought_with',
                        'description' => 'С этим товаром покупают',
                        'default' => 'С этим товаром покупают'
                    ],

                ],
            ],
            [
                'title' => 'cart',
                'description' => 'Окно корзины',
                'keys' => [
                    [
                        'key' => 'cart',
                        'description' => 'Корзина',
                        'default' => 'Корзина'
                    ],
                    [
                        'key' => 'goods',
                        'description' => 'товар',
                        'default' => '{n} товаров | {n} товар | {n} товара | {n} товаров'
                    ],
                    [
                        'key' => 'clear_cart',
                        'description' => 'Очистить корзину',
                        'default' => 'Очистить корзину'
                    ],
                    [
                        'key' => 'currency_rub',
                        'description' => 'руб',
                        'default' => 'руб'
                    ],
                    [
                        'key' => 'total',
                        'description' => 'Итого',
                        'default' => 'Итого'
                    ],
                    [
                        'key' => 'on',
                        'description' => 'слово "на" ',
                        'default' => 'на'
                    ],
                    [
                        'key' => 'cost_without_delivery',
                        'description' => 'Стоимость без учета доставки',
                        'default' => 'Стоимость без учета доставки'
                    ],
                    [
                        'key' => 'go_to_cart',
                        'description' => 'Перейти в корзину',
                        'default' => 'Перейти в корзину'
                    ],
                    [
                        'key' => 'checkout',
                        'description' => 'Оформить заказ',
                        'default' => 'Оформить заказ'
                    ],

                ]
            ],
            [
                'title' => 'cart_page',
                'description' => 'Страница корзины',
                'keys' => [
                    [
                        'key' => 'continue_shopping',
                        'description' => 'Продолжить покупки',
                        'default' => 'Продолжить покупки'
                    ],
                    [
                        'key' => 'secondItemSale',
                        'description' => '-10% на второй товар',
                        'default' => '-{n}% на второй товар'
                    ],
                    [
                        'key' => 'firstGift',
                        'description' => 'Фраза "Подарок при первом заказе"',
                        'default' => 'Подарок при первом заказе',
                    ],
                    [
                        'key' => 'daysLeft',
                        'description' => 'Фраза "Еще n день/дня/дней"',
                        'default' => 'Еще {count} дней|Еще {count} день|Еще {count} дня|Еще {count} дней',
                    ],
                    [
                        'key' => 'delivery',
                        'description' => 'Доставка',
                        'default' => 'Доставка'
                    ],
                    [
                        'key' => 'courier',
                        'description' => 'Курьер',
                        'default' => 'Курьер'
                    ],
                    [
                        'key' => 'pickup',
                        'description' => 'Самовывоз',
                        'default' => 'Самовывоз'
                    ],
                    [
                        'key' => 'transport_company',
                        'description' => 'Транспортная компания',
                        'default' => 'Транспортная компания'
                    ],
                    [
                        'key' => 'delivery_address',
                        'description' => 'Адрес доставки',
                        'default' => 'Адрес доставки'
                    ],
                    [
                        'key' => 'city',
                        'description' => 'Город',
                        'default' => 'Город'
                    ],
                    [
                        'key' => 'street',
                        'description' => 'Улица',
                        'default' => 'Улица'
                    ],
                    [
                        'key' => 'house',
                        'description' => 'Дом',
                        'default' => 'Дом'
                    ],
                    [
                        'key' => 'promo_code',
                        'description' => 'Промокод',
                        'default' => 'Промокод'
                    ],
                    [
                        'key' => 'apply',
                        'description' => 'Применить',
                        'default' => 'Применить'
                    ],
                    [
                        'key' => 'order_details',
                        'description' => 'Детали заказа',
                        'default' => 'Детали заказа'
                    ],
                    [
                        'key' => 'order_sum',
                        'description' => 'Сумма заказа',
                        'default' => 'Сумма заказа'
                    ],
                    [
                        'key' => 'order_weight',
                        'description' => 'Вес заказа',
                        'default' => 'Вес заказа'
                    ],
                    [
                        'key' => 'total',
                        'description' => 'Итого',
                        'default' => 'Итого'
                    ],
                    [
                        'key' => 'cost_without_delivery',
                        'description' => 'Стоимость без учета доставки',
                        'default' => 'Стоимость без учета доставки'
                    ],
                    [
                        'key' => 'checkout',
                        'description' => 'Оформить заказ',
                        'default' => 'Оформить заказ'
                    ],
                    [
                        'key' => 'order_one_click',
                        'description' => 'Заказать в один клик',
                        'default' => 'Заказать в один клик'
                    ],
                    [
                        'key' => 'return_exchange_terms',
                        'description' => 'Условия возврата и обмена товаров',
                        'default' => 'Условия возврата и обмена товаров'
                    ],

                ],
            ],
            [
                'title' => 'home_delivery_info',
                'description' => 'Информация о доставке на главной',
                'keys' => [
                    ['key' => 'title', 'description' => 'Заголовок', 'default' => 'Доставка мраморной говядины Prime Foods по России'],
                    ['key' => 'after_title', 'description' => 'Мини текст после заголовка', 'default' => 'Мраморная говядина стала еще ближе'],
                    ['key' => 'arrow_text_first', 'description' => 'Текст 1 стрелки', 'default' => 'Обратите внимание, если в карточке товара указан средний вес, то после сборки заказа вес может незначительно поменяться.'],
                    ['key' => 'arrow_text_second', 'description' => 'Текст 2 стрелки', 'default' => 'В случае оформления после 20:15 Ваш заказ будет обработан на следующий день.'],
                    ['key' => 'arrow_text_third', 'description' => 'Текст 3 стрелки', 'default' => 'После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС, отправителем указан Prime Foods.'],

                    ['key' => 'intervals_title', 'description' => 'Интервалы доставки', 'default' => 'Интервалы доставки'],
                    ['key' => 'intervals_subtitle', 'description' => 'Текст под интервалами: "любое 4-х часовое окно"', 'default' => 'любое 4-х часовое окно'],

                    ['key' => 'regions_title', 'description' => 'Регионы доставки', 'default' => 'Регионы доставки'],
                    //                    ['key' => 'region_moscow', 'description' => 'Москва'],
                    //                    ['key' => 'region_sochi', 'description' => 'Сочи'],
                    //                    ['key' => 'region_spb', 'description' => 'Санкт-Петербург'],
                    //                    ['key' => 'region_nizhny', 'description' => 'Нижний Новгород'], ИЗ БАЗЫ???

                    ['key' => 'order_title', 'description' => 'Заголовок "Оформление заказов"', 'default' => 'Оформление заказов'],
                    ['key' => 'order_weekends', 'description' => 'Надпись "Без выходных"', 'default' => 'Без выходных'],
                    ['key' => 'order_online', 'description' => 'Надпись "Через сайт – круглосуточно"', 'default' => 'Через сайт – круглосуточно'],

                    ['key' => 'button_more', 'description' => 'Кнопка "Подробнее"', 'default' => 'Подробнее'],

                    ['key' => 'pickup_point', 'description' => 'Тип: "Пункт выдачи"', 'default' => 'Пункт выдачи'],
                    ['key' => 'open_for', 'description' => 'Открыто до', 'default' => 'Открыто до'],
                    ['key' => 'pickup_point_moscow_address', 'description' => 'Адрес в Москве', 'default' => 'Москва, Рябиновая улица, 45, стр. 4'],

                    ['key' => 'meat_store', 'description' => 'Тип: "Магазин мяса"', 'default' => 'Магазин мяса'],
                    ['key' => 'pickup_point_spb_address', 'description' => 'Адрес в Питере', 'default' => 'Санкт-Петербург, Арсенальная улица, 78С'],

                ]
            ],
            [
                'title' => 'footer',
                'description' => 'Подвал сайта',
                'keys' => [
                    ['key' => 'payment_systems', 'description' => 'Текст "Платежные системы"', 'default' => 'Платежные системы'],
                    ['key' => 'privacy_policy', 'description' => 'Текст "Политика конфиденциальности"', 'default' => 'Политика конфиденциальности'],
                    ['key' => 'privacy_policy_checkbox_step_1', 'description' => 'Текст возле чб до ссылки', 'default' => 'Нажимая на кнопку «Подписаться» я подтверждаю, что ознакомился с '],
                    ['key' => 'privacy_policy_checkbox_link', 'description' => 'Текст:"Политой конф-и"(ссылка)', 'default' => 'политикой конфиденциальности '],
                    ['key' => 'privacy_policy_checkbox_step_2', 'description' => 'Текст возле чб после ссылки', 'default' => 'и даю соглашение на обработку персональных данных'],
                    ['key' => 'subscribe', 'description' => 'Текст "Подписаться"', 'default' => 'Подписаться'],
                    ['key' => 'cookie_usage_policy', 'description' => 'Текст "Политика использования файлов cookie', 'default' => 'Политика использования файлов cookie'],
                    ['key' => 'newsletter', 'description' => 'Текст "Рассылка', 'default' => 'Рассылка'],
                    ['key' => 'input_email', 'description' => 'Текст "Введите ваш e-mail', 'default' => 'Введите ваш e-mail'],
                    ['key' => 'contacts', 'description' => 'Текст "Контакты', 'default' => 'Контакты'],
                    ['key' => 'make_order', 'description' => 'Текст "Оформление заказа', 'default' => 'Оформление заказа'],
                    ['key' => 'order_time', 'description' => 'График приема заказов', 'default' => 'График приема заказов'],
                    ['key' => 'corporate_mail', 'description' => 'Корпоративная почта', 'default' => 'Корпоративная почта'],
                ],

            ],
            [
                'title' => 'catalog',
                'description' => 'Страница каталога',
                'keys' => [
                    ['key' => 'catalogTitle', 'description' => 'Заголовок "Каталог товаров"', 'default' => 'Каталог товаров'],
                    ['key' => 'sorting', 'description' => 'Кнопка "Сортировка"', 'default' => 'Сортировка'],
                    ['key' => 'showFirst', 'description' => 'Подзаголовок "Какие товары показать сначала"', 'default' => 'Какие товары показать сначала'],
                    ['key' => 'found', 'description' => 'Текст "Найдено"', 'default' => 'Найдено '],
                    [
                        'key' => 'secondItemSale',
                        'description' => '-10% на второй товар',
                        'default' => '-{n}% на второй товар'
                    ],
                    [
                        'key' => 'bonusPromotions',
                        'description' => 'Бонусные акции в фильтрах',
                        'default' => 'Бонусные акции'
                    ],
                    ['key' => 'products', 'description' => 'Текст "n товаров"', 'default' => '{n} товаров |{n} товар |{n} товара |{n} товаров'],

                ],
            ],
            [
                'title' => 'filters',
                'description' => 'Фильтры товаров',
                'keys' => [
                    ['key' => 'filters', 'description' => 'Заголовок "Фильтры"', 'default' => 'Фильтры'],
                    ['key' => 'price', 'description' => 'Поле "Цена"', 'default' => 'Цена'],
                    ['key' => 'weight', 'description' => 'Поле "Вес продукта (кг)"', 'default' => 'Вес продукта (кг)'],
                    ['key' => 'brand', 'description' => 'Поле "Бренд"', 'default' => 'Бренд'],
                    ['key' => 'packageType', 'description' => 'Поле "Тип упаковки"', 'default' => 'Тип упаковки'],
                    ['key' => 'apply', 'description' => 'Кнопка "Применить"', 'default' => 'Применить'],
                    ['key' => 'reset', 'description' => 'Кнопка "Сбросить"', 'default' => 'Сбросить'],
                ],
            ],
            [
                'title' => 'sorting',
                'description' => 'Варианты сортировки',
                'keys' => [
                    ['key' => 'popular', 'description' => 'Опция "Популярные"', 'default' => 'Популярные'],
                    ['key' => 'highRating', 'description' => 'Опция "Выше рейтинг покупателей"', 'default' => 'Выше рейтинг покупателей'],
                    ['key' => 'cheaper', 'description' => 'Опция "Дешевле"', 'default' => 'Дешевле'],
                    ['key' => 'expensive', 'description' => 'Опция "Дороже"', 'default' => 'Дороже'],
                    ['key' => 'new', 'description' => 'Опция "Новинки"', 'default' => 'Новинки'],
                ],
            ],
            [
                'title' => 'faq',
                'description' => 'Раздел часто задаваемых вопросов и информации о компании',
                'keys' => [
                    ['key' => 'title', 'description' => 'Заголовок "Часто задаваемые вопросы"', 'default' => 'Часто задаваемые вопросы'],
                    ['key' => 'mightBeInteresting', 'description' => 'Заголовок "Может быть интересно"', 'default' => 'Может быть интересно'],
                    ['key' => 'delivery', 'description' => 'Пункт "Доставка"', 'default' => 'Доставка'],
                    ['key' => 'payment', 'description' => 'Пункт "Оплата"', 'default' => 'Оплата'],
                    ['key' => 'returnPolicy', 'description' => 'Пункт "Условия возврата"', 'default' => 'Условия возврата'],
                    ['key' => 'contactInfo', 'description' => 'Пункт "Контактная информация"', 'default' => 'Контактная информация'],
                    ['key' => 'contactMessengers', 'description' => 'Пункт "Связаться через мессенджеры"', 'default' => 'Связаться через мессенджеры'],
                    ['key' => 'feedback', 'description' => 'Пункт "Обратная связь"', 'default' => 'Обратная связь'],
                    ['key' => 'noAnswer', 'description' => 'Фраза "Не нашли ответ? Задайте вопрос"', 'default' => 'Не нашли ответ? Задайте вопрос'],
                    ['key' => 'weWillContact', 'description' => 'Текст "Мы свяжемся с вами..."', 'default' => 'Мы свяжемся с вами и предоставим консультацию. Наши эксперты всегда помогут определиться с выбором'],
                    ['key' => 'yourName', 'description' => 'Поле "Ваше имя"', 'default' => 'Ваше имя'],
                    ['key' => 'contactExpert', 'description' => 'Кнопка "Связаться с экспертом"', 'default' => 'Связаться с экспертом'],
                    ['key' => 'qualityGuarantee', 'description' => 'Информация о гарантии качества продукции', 'default' => 'Информация о гарантии качества продукции'],
                    ['key' => 'fullCycle', 'description' => 'Текст "Полный цикл производства..."', 'default' => 'Полный цикл производства'],
                    ['key' => 'fullCycleDescription', 'description' => 'Описание "Полный цикл производства..."', 'default' => 'от разведения бычков до их откорма и разделки'],
                    ['key' => 'feeding200Days', 'description' => 'Текст "200 дневный откорм..."', 'default' => '200 дневный откорм'],
                    ['key' => 'feeding200DaysDescription', 'description' => 'Описание "200 дневный откорм..."', 'default' => 'только на основе пятикомпонентной кукурузной смеси, благодаря чему жир в мышечной ткани распределяется равномерно, а мясо обретает сочность и нежность и сладковатый вкус'],
                    ['key' => 'perfectGenetics', 'description' => 'Текст "Идеальная генетика..."', 'default' => 'Идеальная генетика'],
                    ['key' => 'perfectGeneticsDescription', 'description' => 'Текст "Идеальная генетика..."', 'default' => 'на производство идут только молодые бычки мясных пород, преимущественно абердин-ангус'],
                    ['key' => 'ecoFarms', 'description' => 'Текст "Животных выращивают..."', 'default' => 'Животных выращивают'],
                    ['key' => 'ecoFarmsDescription', 'description' => 'Текст "Животных выращивают..."', 'default' => 'в экологически чистых районах'],
                    ['key' => 'wideChoice', 'description' => 'Текст "Широчайший выбор..."', 'default' => 'Широчайший выбор'],
                    ['key' => 'wideChoiceDescription', 'description' => 'Текст "Широчайший выбор..."', 'default' => 'стейки, антрекоты, филе, мякоти, вырезка, грудинка, лопатка, ребрышки и т.д.'],
                    ['key' => 'highMarbling', 'description' => 'Текст "Высшая степень мраморности..."', 'default' => 'Высшая степень мраморности'],
                    ['key' => 'highMarblingDescription', 'description' => 'Текст "Высшая степень мраморности..."', 'default' => 'мраморность придает мясу невероятную мягкость и нежный, насыщенный вкус'],
                    ['key' => 'wholesaleRetail', 'description' => 'Текст "Продажа оптом и в розницу"', 'default' => 'Продажа оптом и в розницу'],
                    ['key' => 'fastDelivery', 'description' => 'Текст "Быстрая доставка"', 'default' => 'Быстрая доставка'],
                    ['key' => 'backToCatalog', 'description' => 'Кнопка "Назад в каталог"', 'default' => 'Назад в каталог'],
                ],
            ],
            [
                'title' => 'alerts',
                'description' => 'Оповещения пользователя',
                'keys' => [
                    [
                        'key' => 'cartReminderTitle',
                        'description' => 'Заголовок: Завершите покупку',
                        'default' => 'Завершите покупку',
                    ],
                    [
                        'key' => 'cartReminderMessage',
                        'description' => 'Текст: напоминание о корзине',
                        'default' => 'Не забудьте о товарах в вашей корзине. Завершите покупку прямо сейчас!',
                    ],
                    [
                        'key' => 'purchaseBonusTitle',
                        'description' => 'Заголовок: за покупку начислены баллы',
                        'default' => 'За покупку вам начислено ',
                    ],
                    [
                        'key' => 'purchaseBonusTitleTwo',
                        'description' => 'Заголовок:количество баллов ',
                        'default' => '{n} баллов|{n} балл|{n} балла|{n} баллов',
                    ],
                    [
                        'key' => 'purchaseBonusMessage',
                        'description' => 'Текст: срок действия бонусов',
                        'default' => 'Бонусы будут активны до {n}',
                    ],
                    [
                        'key' => 'purchaseBonusActiveMessage',
                        'description' => 'Текст: Активные бонусы за отзыв',
                        'default' => 'Вам начислено {n} бонусов, они станут активны через 24 часа',
                    ],
                    [
                        'key' => 'pointsExpireTitle',
                        'description' => 'Заголовок: скоро сгорят баллы',
                        'default' => 'Скоро сгорит ',
                    ],
                    [
                        'key' => 'pointsExpireBonusMessage',
                        'description' => 'Текст:{n} баллов',
                        'default' => '{n} баллов|{n} балл|{n} балла|{n} баллов',
                    ],
                    [
                        'key' => 'pointsExpireMessage',
                        'description' => 'Текст: ограниченный срок действия баллов',
                        'default' => 'Баллы действуют 90 дней. Успейте потратить!',
                    ],
                    [
                        'key' => 'reviewReplyTitle',
                        'description' => 'Заголовок: вам ответили на отзыв',
                        'default' => 'Вам ответили на отзыв',
                    ],
                    [
                        'key' => 'reviewReplyMessage',
                        'description' => 'Текст: ваш отзыв был полезен другим',
                        'default' => 'Другим пользователям был полезен ваш отзыв',
                    ],
                    [
                        'key' => 'buttonDetails',
                        'description' => 'Кнопка "Подробнее"',
                        'default' => 'Подробнее',
                    ],
                    [
                        'key' => 'buttonGoToCart',
                        'description' => 'Кнопка "Перейти в корзину"',
                        'default' => 'Перейти в корзину',
                    ],


                    [
                        'key' => 'birthdayBonusTitle',
                        'description' => 'Заголовок: Поздравление с днём рождения',
                        'default' => 'С днем рождения! ',
                    ],
                    [
                        'key' => 'birthdayBonusCount',
                        'description' => 'Текст: Количество бонусов за день рождения',
                        'default' => '{n} бонусов|{n} бонус|{n} бонуса|{n} бонусов',
                    ],
                    [
                        'key' => 'birthdayBonusMessage',
                        'description' => 'Текст: Сообщение о подарке за день рождения',
                        'default' => 'Вам доступен подарок. Успейте оформить заказ до {n}.',
                    ],

                    [
                        'key' => 'reviewBonusTitle',
                        'description' => 'Заголовок: Бонус за отзыв',
                        'default' => 'Ваш отзыв одобрен, вам начислено ',
                    ],
                    [
                        'key' => 'reviewBonusCount',
                        'description' => 'Текст: Количество бонусов за отзыв',
                        'default' => '{n} бонусов|{n} бонус|{n} бонуса|{n} бонусов',
                    ],
                    [
                        'key' => 'reviewBonusMessage',
                        'description' => 'Текст: Бонус за одобренный отзыв',
                        'default' => 'Бонусы будут активны до {n}.',
                    ],


                    [
                        'key' => 'subscribeBonusTitle',
                        'description' => 'Заголовок: Бонус за подписку на рассылку',
                        'default' => 'За подписку на рассылку вам начислено ',
                    ],
                    [
                        'key' => 'subscribeBonusCount',
                        'description' => 'Текст: Количество бонусов за подписку',
                        'default' => '{n} бонусов|{n} бонус|{n} бонуса|{n} бонусов',
                    ],
                    [
                        'key' => 'subscribeBonusMessage',
                        'description' => 'Текст: Бонус за подписку на рассылку',
                        'default' => 'Бонусы активны до {n}.',
                    ],


                    [
                        'key' => 'referralFriendTitle',
                        'description' => 'Заголовок: Бонус за приглашенного друга',
                        'default' => 'За приглашенного друга вам начислено ',
                    ],
                    [
                        'key' => 'referralFriendCount',
                        'description' => 'Текст: Количество бонусов за приглашенного друга',
                        'default' => '{n} бонусов|{n} бонус|{n} бонуса|{n} бонусов',
                    ],
                    [
                        'key' => 'referralFriendMessage',
                        'description' => 'Текст: Срок действия бонусов за приглашенного друга',
                        'default' => 'Бонусы будут активны до {n}.',
                    ],

                    [
                        'key' => 'referralRegistrationTitle',
                        'description' => 'Заголовок: Бонус за регистрацию по приглашению',
                        'default' => 'За регистрацию по приглашению вам начислено ',
                    ],
                    [
                        'key' => 'referralRegistrationCount',
                        'description' => 'Текст: Количество бонусов за регистрацию по приглашению',
                        'default' => '{n} бонусов|{n} бонус|{n} бонуса|{n} бонусов',
                    ],
                    [
                        'key' => 'referralRegistrationMessage',
                        'description' => 'Текст: Срок действия бонусов за регистрацию по приглашению',
                        'default' => 'Бонусы будут активны до {n}.',
                    ],

                    [
                        'key' => 'manualBonusTitle',
                        'description' => 'Заголовок: Администратор начислил бонусы',
                        'default' => 'Администратор начислил вам ',
                    ],
                    [
                        'key' => 'manualBonusCount',
                        'description' => 'Текст: Количество бонусов от администратора',
                        'default' => '{n} баллов|{n} балл|{n} балла|{n} баллов',
                    ],
                    [
                        'key' => 'manualBonusMessage',
                        'description' => 'Текст: Причина начисления и срок действия ',
                        'default' => 'Бонусы будут активны до {n}.',
                    ],
                    [
                        'key' => 'manualBonusWithdrawalTitle',
                        'description' => 'Заголовок: Администратор списал бонусы',
                        'default' => 'Администратор списал у вас ',
                    ],
                    [
                        'key' => 'manualBonusWithdrawalCount',
                        'description' => 'Текст: Количество списанных бонусов от администратора',
                        'default' => '{n} баллов|{n} балл|{n} балла|{n} баллов',
                    ],
                    [
                        'key' => 'manualBonusWithdrawalMessage',
                        'description' => 'Текст: Сообщение о списании бонусов',
                        'default' => 'Бонусы были списаны.',
                    ],
                ],
            ],
            [
                'title' => 'profile',
                'description' => 'Личный кабинет',
                'keys' => [
                    // меню слева
                    ['key' => 'personal_account', 'description' => 'Заголовок блока "Личный кабинет"', 'default' => 'Личный кабинет'],
                    ['key' => 'orders_history', 'description' => 'Ссылка "История заказов"', 'default' => 'История заказов'],
                    ['key' => 'favorites', 'description' => 'Ссылка "Избранное"', 'default' => 'Избранное'],
                    ['key' => 'my_reviews', 'description' => 'Ссылка "Мои отзывы"', 'default' => 'Мои отзывы'],
                    ['key' => 'settings', 'description' => 'Ссылка "Настройки"', 'default' => 'Настройки'],
                    ['key' => 'logout', 'description' => 'Ссылка "Выход из аккаунта"', 'default' => 'Выход из аккаунта'],
                    ['key' => 'feedback', 'description' => 'Кнопка "Обратная связь"', 'default' => 'Обратная связь'],

                    // бонусы
                    ['key' => 'points', 'description' => 'Поле "Баллы"', 'default' => '{n} баллов|{n} балл|{n} балла|{n} баллов'],
                    ['key' => 'soon_expire', 'description' => 'Текст "Скоро сгорят"', 'default' => 'Скоро сгорят'],
                    ['key' => 'bonus_balance', 'description' => 'Текст "Баланс бонусов"', 'default' => 'Баланс бонусов'],
                    ['key' => 'bonus_card', 'description' => 'Заголовок блока "Бонусная карта"', 'default' => 'Бонусная карта'],
                    ['key' => 'bonus_card_number', 'description' => 'Номер бонусной карты', 'default' => 'Номер бонусной карты'],
                    ['key' => 'card_level', 'description' => 'Уровень карты', 'default' => 'Уровень карты'],
                    ['key' => 'bonus_details', 'description' => 'Ссылка "Подробнее о бонусной программе"', 'default' => 'Подробнее о бонусной программе'],

                    // блоки на главной
                    ['key' => 'promo_and_discounts', 'description' => 'Кнопка "Промокоды и акции"', 'default' => 'Промокоды и акции'],
                    ['key' => 'promo_and_discounts_little', 'description' => 'Текст под кнопкой', 'default' => 'Больше выгоды от покупок'],
                    ['key' => 'fav_products', 'description' => 'Кнопка "Избранное"', 'default' => 'Избранное'],
                    ['key' => 'fav_products_little', 'description' => 'Текст под кнопкой 2', 'default' => 'Любимые товары'],
                    ['key' => 'fav_recipes', 'description' => 'Кнопка "Любимые рецепты"', 'default' => 'Любимые рецепты'],
                    ['key' => 'fav_recipes_little', 'description' => 'Текст под кнопкой 3', 'default' => 'от шеф-повара'],
                    ['key' => 'waiting_reviews', 'description' => 'Кнопка "Ждут отзыва"', 'default' => 'Ждут отзыва'],
                    ['key' => 'waiting_reviews_little', 'description' => 'Кнопка "Ждут отзыва"', 'default' => '{n} товаров | {n} товар | {n} товара | {n} товаров'],

                    // доставка
                    ['key' => 'delivery', 'description' => 'Заголовок блока "Доставка"', 'default' => 'Доставка'],
                    ['key' => 'delivery_status', 'description' => 'Статус заказа', 'default' => 'Статус заказа:'],
                    ['key' => 'delivery_total', 'description' => 'Итого', 'default' => 'Итого:'],
                    ['key' => 'delivery_description', 'description' => '2 товара на...', 'default' => '{n} товаров на | {n} товар на | {n} товара на | {n} товаров на '],

                    ['key' => 'delivery_not_expected', 'description' => 'Текст "Доставка не ожидается"', 'default' => 'Доставка не ожидается'],
                    ['key' => 'nearest_delivery', 'description' => 'Текст-заглушка "Здесь покажем ближайшие доставки"', 'default' => 'Здесь покажем ближайшие доставки'],

                    // заказы
                    ['key' => 'recent_orders', 'description' => 'Заголовок блока "Вы недавно заказывали"', 'default' => 'Вы недавно заказывали'],
                    ['key' => 'orders_not_expected', 'description' => 'Заголовок блока "Здесь покажем..."', 'default' => 'Здесь покажем ваши заказы за последнее время'],
                    ['key' => 'to_catalog', 'description' => 'Кнопка "В каталог"', 'default' => 'В каталог'],
                    ['key' => 'all_orders', 'description' => 'Кнопка "Все заказы"', 'default' => 'Все заказы'],
                    ['key' => 'repeat_order', 'description' => 'Кнопка "Повторить покупку"', 'default' => 'Повторить покупку'],

                    // подарочная карта
                    ['key' => 'gift_card', 'description' => 'Заголовок блока "Подарочная карта"', 'default' => 'Подарочная карта'],

                    // поддержка
                    ['key' => 'support', 'description' => 'Заголовок блока "Техническая поддержка"', 'default' => 'Техническая поддержка'],
                    ['key' => 'write_support', 'description' => 'Ссылка "Написать в поддержку"', 'default' => 'Написать в поддержку'],
                    ['key' => 'return_conditions', 'description' => 'Ссылка "Условия возврата и обмена товаров"', 'default' => 'Условия возврата и обмена товаров'],
                    ['key' => 'faq', 'description' => 'Ссылка "Частые вопросы"', 'default' => 'Частые вопросы'],
                ],
            ],
            [
                'title' => 'ordersHistory',
                'description' => 'Страница истории заказов',
                'keys' => [
                    ['key' => 'orderHistory', 'description' => 'Заголовок "История заказов"', 'default' => 'История заказов'],
                    ['key' => 'sorting', 'description' => 'Подпись "Сортировка:"', 'default' => 'Сортировка:'],
                    ['key' => 'sortByDate', 'description' => 'Сортировка по дате', 'default' => 'по дате'],
                    ['key' => 'sortByStatus', 'description' => 'Сортировка по статусу', 'default' => 'по статусу'],
                    ['key' => 'sortByOrderNumber', 'description' => 'Сортировка по номеру заказа', 'default' => 'по номеру заказа'],
                    ['key' => 'order', 'description' => 'Слово "Заказ"', 'default' => 'Заказ'],
                    ['key' => 'from', 'description' => 'Слово "от" (дата)', 'default' => 'от'],
                    ['key' => 'orderAmount', 'description' => 'Подпись "Сумма заказа:"', 'default' => 'Сумма заказа:'],
                    ['key' => 'orderWeight', 'description' => 'Подпись "Вес заказа:"', 'default' => 'Вес заказа:'],
                    ['key' => 'productQuantity', 'description' => 'Подпись "Количество товаров:"', 'default' => 'Количество товаров:'],
                    ['key' => 'total', 'description' => 'Подпись "Итого:"', 'default' => 'Итого:'],
                    ['key' => 'deliveryDate', 'description' => 'Подпись "Дата доставки:"', 'default' => 'Дата доставки:'],
                    ['key' => 'deliveryCost', 'description' => 'Подпись "Стоимость доставки:"', 'default' => 'Стоимость доставки:'],
                    ['key' => 'discount', 'description' => 'Подпись "Скидка:"', 'default' => 'Скидка:'],
                    ['key' => 'trackDelivery', 'description' => 'Кнопка "Отследить доставку"', 'default' => 'Отследить доставку'],
                    ['key' => 'repeatOrder', 'description' => 'Кнопка "Повторить заказ"', 'default' => 'Повторить заказ'],
                    ['key' => 'details', 'description' => 'Кнопка "Подробнее"', 'default' => 'Подробнее'],
                    ['key' => 'orderStatus', 'description' => 'Подпись "Статус заказа:"', 'default' => 'Статус заказа:'],
                    ['key' => 'backToCatalog', 'description' => 'Кнопка "Вернуться к каталогу"', 'default' => 'Вернуться к каталогу'],
                ],
            ],
            [
                'title' => 'profile_settings',
                'description' => 'Страница настроек профиля',
                'keys' => [
                    ['key' => 'settings', 'description' => 'Заголовок "Настройки"', 'default' => 'Настройки'],

                    // Уведомления
                    ['key' => 'notifications', 'description' => 'Подзаголовок "Уведомления"', 'default' => 'Уведомления'],
                    ['key' => 'notifyLikesComments', 'description' => 'Сообщать о новых лайках и ответах в комментариях', 'default' => 'Сообщать о новых лайках и ответах в комментариях'],
                    ['key' => 'notifyDiscounts', 'description' => 'Сообщать о новых скидках и акциях', 'default' => 'Сообщать о новых скидках и акциях'],
                    ['key' => 'notifyFavoriteCategories', 'description' => 'Сообщать о новых товарах из любимых категорий', 'default' => 'Сообщать о новых товарах из любимых категорий'],
                    ['key' => 'receiveEmailNews', 'description' => 'Получать рассылки на почту', 'default' => 'Получать рассылки на почту'],
                    ['key' => 'receiveSmsNews', 'description' => 'Получать СМС-рассылки', 'default' => 'Получать СМС-рассылки'],
                    ['key' => 'receiveMessengerNews', 'description' => 'Получать рассылки через мессенджеры', 'default' => 'Получать рассылки через мессенджеры'],

                    // Подсказки
                    ['key' => 'bindInPersonalCabinet', 'description' => 'Подсказка "Можно привязать во вкладке Личный кабинет"', 'default' => 'Можно привязать во вкладке «Личный кабинет»'],

                    // Частота уведомлений
                    ['key' => 'notificationFrequency', 'description' => 'Подпись "Частота уведомлений"', 'default' => 'Частота уведомлений'],
                    ['key' => 'daily', 'description' => 'Ежедневно', 'default' => 'Ежедневно'],
                    ['key' => 'weekly', 'description' => 'Еженедельно', 'default' => 'Еженедельно'],
                    ['key' => 'onlyWithPromos', 'description' => 'Только при наличии акций', 'default' => 'Только при наличии акций'],

                    // Кнопки
                    ['key' => 'saveChanges', 'description' => 'Кнопка "Сохранить изменения"', 'default' => 'Сохранить изменения'],

                    // Смена пароля
                    ['key' => 'changePassword', 'description' => 'Заголовок "Смена пароля"', 'default' => 'Смена пароля'],
                    ['key' => 'enterOldPassword', 'description' => 'Поле "Введите старый пароль"', 'default' => 'Введите старый пароль'],
                    ['key' => 'forgotPassword', 'description' => 'Ссылка "Забыли пароль?"', 'default' => 'Забыли пароль?'],
                    ['key' => 'oldPassword', 'description' => 'Поле "Старый пароль"', 'default' => 'Старый пароль'],
                    ['key' => 'newPassword', 'description' => 'Поле "Новый пароль"', 'default' => 'Новый пароль'],
                    ['key' => 'confirmPassword', 'description' => 'Поле "Подтверждение пароля"', 'default' => 'Подтверждение пароля'],
                    ['key' => 'buttonChangePassword', 'description' => 'Кнопка "Сменить пароль"', 'default' => 'Сменить пароль'],

                    // Удаление аккаунта
                    ['key' => 'deleteAccount', 'description' => 'Заголовок "Удаление аккаунта"', 'default' => 'Удаление аккаунта'],
                    ['key' => 'deleteAccountWarning', 'description' => 'Текст о том, что карта лояльности будет аннулирована и данные удалены', 'default' => 'Карта лояльности будет аннулирована, данные удалены безвозвратно'],
                    ['key' => 'buttonDeleteAccount', 'description' => 'Кнопка "Удалить аккаунт"', 'default' => 'Удалить аккаунт'],
                ],



            ],
            [
                'title' => 'my_reviews',
                'description' => 'Страница "Мои отзывы"',
                'keys' => [
                    ['key' => 'myReviews', 'description' => 'Заголовок "Мои отзывы"', 'default' => 'Мои отзывы'],
                    ['key' => 'products', 'description' => 'Вкладка "Товары"', 'default' => 'Товары'],
                    ['key' => 'recipes', 'description' => 'Вкладка "Рецепты"', 'default' => 'Рецепты'],
                    ['key' => 'backToCatalog', 'description' => 'Кнопка "Вернуться в каталог"', 'default' => 'Вернуться в каталог'],
                    ['key' => 'toCatalog', 'description' => 'Кнопка "В каталог"', 'default' => 'В каталог'],
                    ['key' => 'awaitingReview', 'description' => 'Подпись "Ждут отзыва"', 'default' => 'Ждут отзыва'],
                    ['key' => 'deliveryDate', 'description' => 'Подпись "Дата доставки:"', 'default' => 'Дата доставки:'],
                    ['key' => 'alreadyReviewed', 'description' => 'Фраза "Вы уже оценила"', 'default' => 'Вы уже оценила'],
                    ['key' => 'weAreWaitingForReviews', 'description' => 'Фраза "Ждём ваши оценки и отзывы"', 'default' => 'Ждём ваши оценки и отзывы'],
                    ['key' => 'rateProduct', 'description' => 'Фраза "Оцените товар"', 'default' => 'Оцените товар'],
                    ['key' => 'sorting', 'description' => 'Подпись "Сортировка:"', 'default' => 'Сортировка:'],
                    ['key' => 'sortByDate', 'description' => 'Сортировка по дате', 'default' => 'по дате'],
                    ['key' => 'editReview', 'description' => 'Кнопка "Редактировать отзыв"', 'default' => 'Редактировать отзыв'],
                    ['key' => 'editReviewTitle', 'description' => 'Заголовок "Редактирования отзыва"', 'default' => 'Редактирования отзыва'],
                    ['key' => 'deleteReview', 'description' => 'Кнопка "Удалить отзыв"', 'default' => 'Удалить отзыв'],
                    ['key' => 'addPhoto', 'description' => 'Кнопка "Добавьте фото"', 'default' => 'Добавьте фото'],
                    ['key' => 'photo', 'description' => 'Метка "Фото"', 'default' => 'Фото'],
                    ['key' => 'photoLimit', 'description' => 'Подпись "до n шт"', 'default' => 'до n шт'],
                    ['key' => 'shareImpressions', 'description' => 'Фраза "Расскажите впечатления"', 'default' => 'Расскажите впечатления'],
                    ['key' => 'comment', 'description' => 'Поле "Комментарий"', 'default' => 'Комментарий'],
                    ['key' => 'save', 'description' => 'Кнопка "Сохранить"', 'default' => 'Сохранить'],
                    ['key' => 'leaveReview', 'description' => 'Кнопка "Оставить отзыв"', 'default' => 'Оставить отзыв'],
                    ['key' => 'reviewCheckInfo', 'description' => 'Текст про проверку отзыва', 'default' => 'Проверяем отзыв на соответствие правилам платформы и законодательству. Это займет не более 24 часов.'],
                    ['key' => 'reviewRejectedInfo', 'description' => 'Текст про отклонение отзыва', 'default' => 'К сожалению ваш отзыв был отклонен. Пожалуйста, проверьте корректность языка, на котором написан отзыв.'],
                    ['key' => 'reviewPending', 'description' => 'Фраза "Ваш отзыв на модерации"', 'default' => 'Ваш отзыв на модерации'],
                    ['key' => 'reviewRejected', 'description' => 'Фраза "Ваш отзыв отклонен"', 'default' => 'Ваш отзыв отклонен'],
                    ['key' => 'helpful', 'description' => 'Кнопка/метка "полезно"', 'default' => 'полезно'],
                    ['key' => 'share', 'description' => 'Кнопка "Поделиться"', 'default' => 'Поделиться'],
                    ['key' => 'storeReply', 'description' => 'Метка "Ответ магазина"', 'default' => 'Ответ магазина'],
                ],
            ],
            [
                'title' => 'favorites',
                'description' => 'Страница "Избранное"',
                'keys' => [
                    ['key' => 'favorites', 'description' => 'Заголовок "Избранное"', 'default' => 'Избранное'],
                    ['key' => 'products', 'description' => 'Вкладка "Товары"', 'default' => 'Товары'],
                    ['key' => 'recipes', 'description' => 'Вкладка "Рецепты"', 'default' => 'Рецепты'],
                    ['key' => 'toCatalog', 'description' => 'Кнопка "В каталог"', 'default' => 'В каталог'],
                    ['key' => 'favoritesProductsInfo', 'description' => 'Текст: Здесь будут подборки из понравившихся вам товаров', 'default' => 'Здесь будут подборки из понравившихся вам товаров'],
                    ['key' => 'favoritesRecipesInfo', 'description' => 'Текст: Здесь будут подборки из понравившихся вам рецептов', 'default' => 'Здесь будут подборки из понравившихся вам рецептов'],
                ],
            ],
            [
                'title' => 'checkout',
                'description' => 'Страница оформления заказа',
                'keys' => [
                    // Основные этапы
                    ['key' => 'title', 'description' => 'Заголовок "Оформление заказа"', 'default' => 'Оформление заказа'],
                    ['key' => 'steps_cart', 'description' => 'Этап "Корзина"', 'default' => 'Корзина'],
                    ['key' => 'steps_delivery', 'description' => 'Этап "Данные для доставки"', 'default' => 'Данные для доставки'],
                    ['key' => 'steps_payment', 'description' => 'Этап "Оплата"', 'default' => 'Оплата'],
                    ['key' => 'steps_confirm', 'description' => 'Этап "Подтверждение"', 'default' => 'Подтверждение'],
                    [
                        'key' => 'secondItemSale',
                        'description' => '-10% на второй товар',
                        'default' => '-{n}% на второй товар'
                    ],
                    [
                        'key' => 'daysLeft',
                        'description' => 'Фраза "Еще n день/дня/дней"',
                        'default' => 'Еще {count} дней|Еще {count} день|Еще {count} дня|Еще {count} дней',
                    ],
                    // Инфо-подсказки к шагам
                    ['key' => 'fill_data_note', 'description' => 'Текст о заполнении данных и стоимости доставки', 'default' => 'Заполните данные от этого будет завесить стоимость доставки и итоговая сумма заказа'],
                    ['key' => 'payment_note', 'description' => 'Текст о способах оплаты', 'default' => 'Оплатите заказ онлайн или наличными курьеру'],
                    ['key' => 'confirmation_note', 'description' => 'Текст о подтверждении на почту и телефон', 'default' => 'Подтверждение придет на указанную вами почту или номер телефона. Так же вы сможете просмотреть детали заказа на сайте в личном кабинете'],
                    ['key' => 'await_call', 'description' => 'Ожидание звонка менеджера', 'default' => 'Ожидайте звонка менеджера'],
                    ['key' => 'payment_after_assembly', 'description' => 'Информация об оплате после сборки заказа', 'default' => 'Оплата осуществляется только после сборки заказа'],

                    // Получатель
                    ['key' => 'recipient', 'description' => 'Блок "Получатель"', 'default' => 'Получатель'],
                    ['key' => 'recipient_data', 'description' => 'Подзаголовок "Данные получателя"', 'default' => 'Данные получателя'],
                    ['key' => 'lastname', 'description' => 'Поле "Ваша фамилия"', 'default' => 'Ваша фамилия'],
                    ['key' => 'firstname', 'description' => 'Поле "Ваше имя"', 'default' => 'Ваше имя'],
                    ['key' => 'middlename', 'description' => 'Поле "Ваше отчество"', 'default' => 'Ваше отчество'],
                    ['key' => 'contact_details', 'description' => 'Заголовок "Контактные данные"', 'default' => 'Контактные данные'],
                    ['key' => 'email', 'description' => 'Поле "Ваш e-mail"', 'default' => 'Ваш e-mail'],
                    ['key' => 'phone', 'description' => 'Поле "Ваш номер телефона"', 'default' => 'Ваш номер телефона'],

                    // Доставка
                    ['key' => 'delivery', 'description' => 'Блок "Доставка"', 'default' => 'Доставка'],
                    ['key' => 'delivery_method', 'description' => 'Подзаголовок "Способ доставки"', 'default' => 'Способ доставки'],
                    ['key' => 'courier', 'description' => 'Опция "Курьер"', 'default' => 'Курьер'],
                    ['key' => 'pickup', 'description' => 'Опция "Самовывоз"', 'default' => 'Самовывоз'],
                    ['key' => 'pickup_points', 'description' => 'Заголовок "Пункты самовывоза"', 'default' => 'Пункты самовывоза'],
                    ['key' => 'pickup_point', 'description' => 'Элемент списка "Пункт самовывоза"', 'default' => 'Пункт самовывоза'],

                    ['key' => 'delivery_address', 'description' => 'Подзаголовок "Адрес доставки"', 'default' => 'Адрес доставки'],
                    ['key' => 'from_address_note', 'description' => 'Примечание "От адреса зависит ассортимент..."', 'default' => 'От адреса зависит ассортимент, цены и условия получения заказа'],
                    ['key' => 'select_city', 'description' => 'Поле выбора города', 'default' => 'Выберите город'],
                    ['key' => 'index', 'description' => 'Поле "Индекс"', 'default' => 'Индекс'],
                    ['key' => 'street', 'description' => 'Поле "Улица, номер дома"', 'default' => 'Улица, номер дома'],
                    ['key' => 'entrance', 'description' => 'Поле "Подъезд"', 'default' => 'Подъезд'],
                    ['key' => 'floor', 'description' => 'Поле "Этаж"', 'default' => 'Этаж'],
                    ['key' => 'apartment', 'description' => 'Поле "Квартира"', 'default' => 'Квартира'],

                    ['key' => 'delivery_date', 'description' => 'Дата доставки', 'default' => 'Дата доставки'],
                    ['key' => 'change_date', 'description' => 'Кнопка "Изменить дату"', 'default' => 'Изменить дату'],
                    ['key' => 'select_time_slot', 'description' => 'Выбор временного интервала доставки', 'default' => 'Выберите временной интервал доставки'],
                    ['key' => 'confirm_choice', 'description' => 'Кнопка "Подтвердить выбор"', 'default' => 'Подтвердить выбор'],

                    // Промокод
                    ['key' => 'apply', 'description' => 'Кнопка "Применить"', 'default' => 'Применить'],
                    ['key' => 'applied', 'description' => 'Статус "Применен"', 'default' => 'Применен'],
                    [
                        'key' => 'takePoints',
                        'description' => 'Фраза "Списать"',
                        'default' => 'Списать ',
                    ],
                    [
                         'key' => 'takePointsValue',
                         'description' => 'Фраза "{n} баллов"',
                         'default' => '{n} баллов|{n} балл|{n} балла|{n} баллов',
                    ],
                    [
                        'key' => 'cashback',
                        'description' => 'Фраза "+n% кэшбека"',
                        'default' => 'кэшбека',
                    ],
                    [
                        'key' => 'firstGift',
                        'description' => 'Фраза "Подарок при первом заказе"',
                        'default' => 'Подарок при первом заказе',
                    ],
                    [
                        'key' => 'pickUpBonus',
                        'description' => 'Фраза "+n баллов за самовывоз"',
                        'default' => '+{n} баллов за самовывоз|+{n} балл за самовывоз|+{n} баллов за самовывоз',
                    ],
                    [
                        'key' => 'pointsDate',
                        'description' => 'Фраза "Баллы действуют до: {n} "',
                        'default' => 'Баллы действуют до: {n}',
                    ],

                    // Детали доставки
                    ['key' => 'delivery_details_note', 'description' => 'Текст об изменении данных в зависимости от способа доставки', 'default' => 'В зависимости от выбранного способа доставки и адреса данные могут измениться'],
                    ['key' => 'delivery_term', 'description' => 'Метка "Срок доставки:"', 'default' => 'Срок доставки:'],
                    ['key' => 'delivery_zone', 'description' => 'Метка "Зона доставки:"', 'default' => 'Зона доставки:'],
                    ['key' => 'delivery_cost', 'description' => 'Метка "Стоимость доставки:"', 'default' => 'Стоимость доставки:'],


                    // Детали заказа
                    ['key' => 'order_summary', 'description' => 'Блок "Содержание заказа"', 'default' => 'Содержание заказа'],
                    ['key' => 'order_details', 'description' => 'Блок "Детали заказа"', 'default' => 'Детали заказа'],
                    ['key' => 'order_amount', 'description' => 'Метка "Сумма заказа:"', 'default' => 'Сумма заказа:'],
                    ['key' => 'order_weight', 'description' => 'Метка "Вес заказа:"', 'default' => 'Вес заказа:'],
                    ['key' => 'delivery_label', 'description' => 'Метка "Доставка:"', 'default' => 'Доставка:'],
                    ['key' => 'my_discount', 'description' => 'Метка "Моя скидка:"', 'default' => 'Моя скидка:'],
                    ['key' => 'promo_code_label', 'description' => 'Метка "Промокод:"', 'default' => 'Промокод:'],
                    ['key' => 'final_order_sum_note', 'description' => 'Сообщение о финальной сумме заказа', 'default' => 'Итоговая сумма заказа формируется после сборки заказа на складе'],

                    // Оплата
                    ['key' => 'payment', 'description' => 'Блок "Способ оплаты"', 'default' => 'Способ оплаты'],
                    ['key' => 'payment_cash', 'description' => 'Опция "Оплата наличными при получении"', 'default' => 'Оплата наличными при получении'],
                    ['key' => 'payment_card', 'description' => 'Опция "Оплата банковской картой"', 'default' => 'Оплата банковской картой'],

                    ['key' => 'awaiting_payment', 'description' => 'Статус "Ожидает оплаты"', 'default' => 'Ожидает оплаты'],
                    ['key' => 'payment_link_sent', 'description' => 'Сообщение о ссылке на оплату в смс', 'default' => 'Ссылка на оплату заказа направлена вам в смс'],
                    ['key' => 'no_sms_note', 'description' => 'Примечание, если смс не приходит', 'default' => 'Если смс не приходит, свяжитесь с технической поддержкой'],

                    // Подтверждение заказа
                    ['key' => 'your_order', 'description' => 'Заголовок "Ваш заказ"', 'default' => 'Ваш заказ'],
                    ['key' => 'success_confirmed', 'description' => 'Статус "успешно подтвержден"', 'default' => 'успешно подтвержден'],
                    ['key' => 'confirmation_email_sent', 'description' => 'Сообщение о письме на почту', 'default' => 'На указанную вами почту было отправлено письмо с подтверждением заказа'],
                    ['key' => 'go_to_account', 'description' => 'Кнопка "Перейти в личный кабинет"', 'default' => 'Перейти в личный кабинет'],

                    // Кнопки
                    ['key' => 'place_order', 'description' => 'Кнопка "Оформить заказ"', 'default' => 'Оформить заказ'],

                    // Поддержка
                    ['key' => 'tech_support', 'description' => 'Заголовок "Техническая поддержка"', 'default' => 'Техническая поддержка'],
                    ['key' => 'contact_us_if', 'description' => 'Текст "Свяжитесь с нами..."', 'default' => 'Свяжитесь с нами, если у вас возникли вопросы или проблемы с оформлением заказа'],
                    ['key' => 'or_write_us', 'description' => 'Текст "Или напишите нам на почту"', 'default' => 'Или напишите нам на почту'],
                    ['key' => 'support_email', 'description' => 'Почта поддержки (placeholder)', 'default' => 'support@example.com'],
                    ['key' => 'chat_with_support', 'description' => 'Ссылка "Чат с технической поддержкой"', 'default' => 'Чат с технической поддержкой'],
                    ['key' => 'return_policy', 'description' => 'Ссылка "Условия возврата и обмена товаров"', 'default' => 'Условия возврата и обмена товаров'],
                    ['key' => 'faq', 'description' => 'Ссылка "Частые вопросы"', 'default' => 'Частые вопросы'],
                    [
                        'key' => 'added_goods',
                        'description' => 'Добавленные товары',
                        'default' => 'Добавлено {n} товаров | Добавлен {n} товар | Добавлено {n} товара | Добавлено {n} товаров',
                    ],
                ],
            ],
            [
                'title' => 'promo_page',
                'description' => 'Страница промоакций',
                'keys' => [
                    ['key' => 'promotionsAndSpecialOffers', 'description' => 'Заголовок "Акции и спецпредложения"', 'default' => 'Акции и спецпредложения'],
                    ['key' => 'allPromotionTypes', 'description' => 'Фраза "Все типы акций"', 'default' => 'Все типы акций'],
                    ['key' => 'promotionValidUntil', 'description' => 'Фраза "Акция действует до"', 'default' => 'Акция действует до'],
                    ['key' => 'buyWithDiscount', 'description' => 'Кнопка "Купить со скидкой"', 'default' => 'Купить со скидкой'],
                    ['key' => 'recommendedProducts', 'description' => 'Заголовок "Рекомендуемые товары"', 'default' => 'Рекомендуемые товары'],
                    ['key' => 'all', 'description' => 'Кнопка/ссылка "Все"', 'default' => 'Все'],
                    ['key' => 'addToCart', 'description' => 'Кнопка "В корзину"', 'default' => 'В корзину'],
                    ['key' => 'buyNow', 'description' => 'Кнопка "Купить сейчас"', 'default' => 'Купить сейчас'],
                    ['key' => 'newsletterQuestion', 'description' => 'Фраза "Хотите быть в курсе новых акций и спецпредложений?"', 'default' => 'Хотите быть в курсе новых акций и спецпредложений?'],
                    ['key' => 'subscribeToNewsletter', 'description' => 'Фраза "Подписывайтесь на нашу рассылку"', 'default' => 'Подписывайтесь на нашу рассылку'],
                    ['key' => 'enterEmail', 'description' => 'Поле "Введите ваш e-mail"', 'default' => 'Введите ваш e-mail'],
                    ['key' => 'subscribe', 'description' => 'Кнопка "Подписаться"', 'default' => 'Подписаться'],
                    ['key' => 'customerReviews', 'description' => 'Блок "Отзывы клиентов"', 'default' => 'Отзывы клиентов'],
                    ['key' => 'faq', 'description' => 'Блок "Часто задаваемые вопросы"', 'default' => 'Часто задаваемые вопросы'],
                    ['key' => 'backToCatalog', 'description' => 'Ссылка "Назад в каталог"', 'default' => 'Назад в каталог'],
                    [
                        'key' => 'daysLeft',
                        'description' => 'Фраза "Еще n день/дня/дней"',
                        'default' => 'Еще {count} дней|Еще {count} день|Еще {count} дня|Еще {count} дней',
                    ],
                    ['key' => 'seasonal', 'description' => 'Фраза "Сезонные предложения"', 'default' => 'Сезонные предложения'],
                    ['key' => 'discount', 'description' => 'Фраза "Скидки"', 'default' => 'Скидки'],
                    ['key' => 'gift', 'description' => 'Фраза "Подарки"', 'default' => 'Подарки'],
                ],
            ],

        ];
        // массив английских переводов (переводы с нейронки)
        $enTranslations = [
            'header' => [
                'enter' => 'Login',
                'deliveryAndPayment' => 'Delivery and Payment',
                'recipes' => 'Recipes',
                'promo' => 'Promotions',
                'catalog' => 'Catalog',
                'search' => 'Search',
                'chooseCity' => 'Choose a city',
                'cart' => 'Cart',
                'roundTheClock' => 'Round the clock',
            ],
            'main_page' => [
                'all' => 'All',
                'allCombo' => 'All Combos',
                'special_offers' => 'Special Offers',
                'recommended_products' => 'Recommended Products',
                'product_categories' => 'Product Categories',
                'novelty' => 'New Arrivals',
                'recipes_for_cooking' => 'Cooking Recipes',
                'combo_packs' => 'Combo Packs',
                'user_reviews' => 'Customer Reviews',
            ],
            'info_banners' => [
                'reg_title' => 'Register and get access to the Privilege Program and exclusive offers for members',
                'reg_description' => 'Join our Privilege Program and get access to exclusive offers, discounts, and events available only to members. Stay up to date with all new products and special deals!',
                'loyalty_title' => 'Flexible Bonus Program',
                'loyalty_description_step_1' => 'CASHBACK for purchases',
                'loyalty_description_step_2' => 'from 2% to 5% for each order',
                'loyalty_addition_description' => 'You can check your bonus balance in your account',
            ],
            'recipe_card' => [
                'portion' => '{n} portion | {n} portions',
                'difficult' => 'Difficulty',
                'minute' => '{n} minute | {n} minutes',
                'hour' => '{n} hour | {n} hours',
            ],
            'product_card' => [
                'average_weight' => 'Average Weight',
                'in_cart' => 'Add to Cart',
                'buy_now' => 'Buy Now',
            ],
            'product_detail_card' => [
                'reviews' => '{n} Review | {n} Reviews',
                'article' => 'SKU',
                'delivery_info' => 'Delivery Information',
                'delivery_address' => 'Delivery Address',
                'cashback' => '+{n}% cashback',
                'city' => 'City',
                'pickup' => 'Pickup',
                'all_delivery_terms' => 'All delivery terms',
                'about_product' => 'About the Product',
                'delivery' => 'Delivery',
                'cutting' => 'Cutting',
                'manufacturer' => 'Manufacturer',
                'chef_recipes' => 'Chef’s Recipes',
                'often_bought_with' => 'Frequently Bought Together',
            ],
            'cart' => [
                'cart' => 'Cart',
                'goods' => '{n} item | {n} items',
                'clear_cart' => 'Clear cart',
                'currency_rub' => 'RUB',
                'total' => 'Total',
                'on' => 'for',
                'cost_without_delivery' => 'Cost excluding delivery',
                'go_to_cart' => 'Go to cart',
                'checkout' => 'Checkout',
            ],

            'cart_page' => [
                'continue_shopping' => 'Continue shopping',
                'delivery' => 'Delivery',
                'secondItemSale' => '-{n}% on the second product',
                'firstGift' => 'A gift for the first order',
                'daysLeft' => 'Only {count} day left|Only {count} days left',
                'courier' => 'Courier',
                'pickup' => 'Pickup',
                'transport_company' => 'Transport Company',
                'delivery_address' => 'Delivery Address',
                'city' => 'City',
                'street' => 'Street',
                'house' => 'House',
                'promo_code' => 'Promo code',
                'apply' => 'Apply',
                'order_details' => 'Order Details',
                'order_sum' => 'Order Total',
                'order_weight' => 'Order Weight',
                'total' => 'Total',
                'cost_without_delivery' => 'Cost excluding delivery',
                'checkout' => 'Checkout',
                'order_one_click' => 'One-Click Order',
                'return_exchange_terms' => 'Return and Exchange Policy',
            ],
            'home_delivery_info' => [
                'title' => 'Delivery of Prime Foods Marbled Beef across Russia',
                'after_title' => 'Marbled beef is now even closer',
                'arrow_text_first' => 'Note: If the average weight is specified on the product card, it may vary slightly after the order is assembled.',
                'arrow_text_second' => 'If ordered after 20:15, your order will be processed the next day.',
                'arrow_text_third' => 'After the order is assembled, you will receive an invoice and a payment link via email. We’ll also send the link via SMS from Prime Foods.',
                'intervals_title' => 'Delivery Intervals',
                'intervals_subtitle' => 'any 4-hour window',
                'regions_title' => 'Delivery Regions',
                'order_title' => 'Order Processing',
                'order_weekends' => 'Open daily',
                'order_online' => 'Via website — 24/7',
                'button_more' => 'More',
                'pickup_point' => 'Pickup Point',
                'open_for' => 'Open until',
                'pickup_point_moscow_address' => 'Moscow, Ryabinovaya St., 45, bldg. 4',
                'meat_store' => 'Meat Store',
                'pickup_point_spb_address' => 'St. Petersburg, Arsenálnaya St., 78C',
            ],
            'footer' => [
                'payment_systems' => 'Payment Systems',
                'privacy_policy' => 'Privacy Policy',
                'privacy_policy_checkbox_step_1' => 'By clicking the "Subscribe" button, I confirm that I have read ',
                'privacy_policy_checkbox_link' => 'privacy policy ',
                'privacy_policy_checkbox_step_2' => 'and I agree to the processing of personal data',
                'subscribe' => 'Subscribe',
                'cookie_usage_policy' => 'Cookie Usage Policy',
                'newsletter' => 'Newsletter',
                'input_email' => 'Enter your email',
                'contacts' => 'Contacts',
                'make_order' => 'Order Placement',
                'order_time' => 'Order Schedule',
                'corporate_mail' => 'Corporate Email',
            ],
            'catalog' => [
                'catalogTitle' => 'Product catalog',
                'sorting' => 'Sorting',
                'showFirst' => 'Which products to show first',
                'secondItemSale' => '-{n}% on the second product',
                'bonusPromotions' => 'Bonus promotions',
                'found' => 'Found | Found | Found | Found ',
                'products' => '{n} product |{n} product |{n} products |{n} products',
            ],
            'filters' => [
                'filters' => 'Filters',
                'price' => 'Price',
                'weight' => 'Product weight (kg)',
                'brand' => 'Brand',
                'packageType' => 'Package type',
                'apply' => 'Apply',
                'reset' => 'Reset',
            ],
            'sorting' => [
                'popular' => 'Popular',
                'highRating' => 'Highest customer rating',
                'cheaper' => 'Cheaper',
                'expensive' => 'More expensive',
                'new' => 'New arrivals',
            ],
            'faq' => [
                'title' => 'Frequently Asked Questions',
                'mightBeInteresting' => 'Might be interesting',
                'delivery' => 'Delivery',
                'payment' => 'Payment',
                'returnPolicy' => 'Return policy',
                'contactInfo' => 'Contact information',
                'contactMessengers' => 'Contact via messengers',
                'feedback' => 'Feedback',
                'noAnswer' => 'Didn’t find the answer? Ask a question',
                'weWillContact' => 'We will contact you and provide a consultation. Our experts will always help you make a choice',
                'yourName' => 'Your name',
                'contactExpert' => 'Contact an expert',
                'qualityGuarantee' => 'Information on product quality guarantee',
                'fullCycle' => 'Full production cycle',
                'fullCycleDescription' => 'from raising bulls to their fattening and butchering',
                'feeding200Days' => '200-day fattening',
                'feeding200DaysDescription' => 'only on the basis of a five-component corn mix, which ensures even fat distribution in the muscle tissue and gives the meat juiciness, tenderness, and a slightly sweet taste',
                'perfectGenetics' => 'Perfect genetics',
                'perfectGeneticsDescription' => 'only young bulls of beef breeds, mainly Aberdeen Angus, are used for production',
                'ecoFarms' => 'Animals are raised',
                'ecoFarmsDescription' => 'in ecologically clean areas',
                'wideChoice' => 'Wide choice',
                'wideChoiceDescription' => 'steaks, entrecotes, fillets, pulp, tenderloin, brisket, shoulder, ribs, etc.',
                'highMarbling' => 'Highest marbling — marbling gives the meat incredible tenderness and a rich, delicate flavor',
                'wholesaleRetail' => 'Wholesale and retail sales',
                'fastDelivery' => 'Fast delivery',
                'backToCatalog' => 'Back to catalog',
            ],
            'alerts' => [
                'cartReminderTitle' => 'Complete your purchase',
                'cartReminderMessage' => 'Some items in your cart will soon no longer be on sale. Hurry to buy at a bargain price.',
                'purchaseBonusTitle' => 'For your purchase you have received ',
                'purchaseBonusTitleTwo' => '{n} point|{n} points',
                'purchaseBonusMessage' => 'Points will be valid until {n}',
                'pointsExpireTitle' => 'Soon ',
                'pointsExpireBonusMessage' => '{n} point will expire|{n} points will expire',
                'pointsExpireMessage' => 'Points are valid for 90 days. Spend them before they expire!',
                'reviewReplyTitle' => 'You have received a reply to your review',
                'reviewReplyMessage' => 'Other users found your review helpful',
                'buttonDetails' => 'Details',
                'buttonGoToCart' => 'Go to cart',


                'birthdayBonusTitle' => 'Happy birthday!',
                'birthdayBonusCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'birthdayBonusMessage' => 'You have a gift available. Hurry to place an order before {n}.',
                'reviewBonusTitle' => 'Your review has been approved, you have received',
                'reviewBonusCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'reviewBonusMessage' => 'Bonus points will be active until {n}.',
                'subscribeBonusTitle' => 'For subscribing to the newsletter you have received',
                'subscribeBonusCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'subscribeBonusMessage' => 'Bonus points are active until {n}.',

                'referralFriendTitle' => 'For inviting a friend you have received',
                'referralFriendCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'referralFriendMessage' => 'Bonus points will be active until {n}.',

                'referralRegistrationTitle' => 'For registering via invitation you have received',
                'referralRegistrationCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'referralRegistrationMessage' => 'Bonus points will be active until {n}.',

                'manualBonusTitle' => 'Administrator credited you ',
                'manualBonusCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'manualBonusMessage' => 'Bonus points will be active until {n}.',
                'manualBonusWithdrawalTitle' => 'Administrator debited you ',
                'manualBonusWithdrawalCount' => '{n} bonus points|{n} bonus point|{n} bonus points|{n} bonus points',
                'manualBonusWithdrawalMessage' => 'Bonus points have been debited.',
            ],
            'profile' => [
                // меню слева
                'personal_account' => 'Personal Account',
                'orders_history' => 'Order History',
                'favorites' => 'Favorites',
                'my_reviews' => 'My Reviews',
                'settings' => 'Settings',
                'logout' => 'Log Out',
                'feedback' => 'Feedback',

                // бонусы
                'points' => 'points',
                'soon_expire' => 'Expiring soon',
                'bonus_balance' => 'Bonus Balance',
                'bonus_card' => 'Bonus Card',
                'bonus_card_number' => 'Bonus Card Number',
                'card_level' => 'Card Level',
                'bonus_details' => 'More about the bonus program',

                // блоки на главной
                'promo_and_discounts' => 'Promo Codes & Discounts',
                'promo_and_discounts_little' => 'More benefits from purchases',
                'fav_products' => 'Favorites',
                'fav_products_little' => 'Favorite Products',
                'fav_recipes' => 'Favorite Recipes',
                'fav_recipes_little' => 'From the chef',
                'waiting_reviews' => 'Awaiting Reviews',
                'waiting_reviews_little' => '{n} product|{n} products|{n} products',

                // доставка
                'delivery' => 'Delivery',
                'delivery_status' => 'Delivery status:',
                'delivery_total' => 'Total:',
                'delivery_description' => '{n} product for |{n} products for|{n} products for',


                'delivery_not_expected' => 'No delivery expected',
                'nearest_delivery' => 'Upcoming deliveries will be shown here',

                // заказы
                'recent_orders' => 'You recently ordered',
                'orders_not_expected' => 'Here we will show your recent orders',
                'to_catalog' => 'To Catalog',
                'all_orders' => 'All Orders',
                'repeat_order' => 'Repeat Purchase',

                // подарочная карта
                'gift_card' => 'Gift Card',

                // поддержка
                'support' => 'Technical Support',
                'write_support' => 'Contact Support',
                'return_conditions' => 'Return & Exchange Policy',
                'faq' => 'FAQ',
            ],
            'ordersHistory' => [
                'orderHistory' => 'Order History',
                'sorting' => 'Sorting:',
                'sortByDate' => 'by date',
                'sortByStatus' => 'by status',
                'sortByOrderNumber' => 'by order number',
                'order' => 'Order',
                'from' => 'from',
                'orderAmount' => 'Order Amount:',
                'orderWeight' => 'Order Weight:',
                'productQuantity' => 'Number of Products:',
                'total' => 'Total:',
                'deliveryDate' => 'Delivery Date:',
                'deliveryCost' => 'Delivery Cost:',
                'discount' => 'Discount:',
                'trackDelivery' => 'Track Delivery',
                'repeatOrder' => 'Repeat Order',
                'details' => 'Details',
                'orderStatus' => 'Order Status:',
                'backToCatalog' => 'Back to Catalog',
            ],
            'profile_settings' => [
                'settings' => 'Settings',

                // Notifications
                'notifications' => 'Notifications',
                'notifyLikesComments' => 'Notify about new likes and comment replies',
                'notifyDiscounts' => 'Notify about new discounts and promotions',
                'notifyFavoriteCategories' => 'Notify about new products from favorite categories',
                'receiveEmailNews' => 'Receive newsletters by email',
                'receiveSmsNews' => 'Receive SMS newsletters',
                'receiveMessengerNews' => 'Receive newsletters via messengers',

                // Hints
                'bindInPersonalCabinet' => 'Can be linked in "Personal account" section',

                // Notification frequency
                'notificationFrequency' => 'Notification frequency',
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'onlyWithPromos' => 'Only when promotions are available',

                // Buttons
                'saveChanges' => 'Save changes',

                // Change password
                'changePassword' => 'Change password',
                'enterOldPassword' => 'Enter old password',
                'forgotPassword' => 'Forgot password?',
                'oldPassword' => 'Old password',
                'newPassword' => 'New password',
                'confirmPassword' => 'Confirm password',
                'buttonChangePassword' => 'Change password',

                // Delete account
                'deleteAccount' => 'Delete account',
                'deleteAccountWarning' => 'The loyalty card will be canceled, and data will be permanently deleted',
                'buttonDeleteAccount' => 'Delete account',
            ],
            'my_reviews' => [
                'myReviews' => 'My Reviews',
                'products' => 'Products',
                'recipes' => 'Recipes',
                'backToCatalog' => 'Back to Catalog',
                'toCatalog' => 'To Catalog',
                'awaitingReview' => 'Awaiting Review',
                'deliveryDate' => 'Delivery Date:',
                'alreadyReviewed' => 'You have already reviewed',
                'weAreWaitingForReviews' => 'We are waiting for your ratings and reviews',
                'rateProduct' => 'Rate the product',
                'sorting' => 'Sorting:',
                'sortByDate' => 'by date',
                'editReview' => 'Edit Review',
                'editReviewTitle' => 'Editing Review',
                'deleteReview' => 'Delete Review',
                'addPhoto' => 'Add photo',
                'photo' => 'Photo',
                'photoLimit' => 'up to n pcs',
                'shareImpressions' => 'Share your impressions',
                'comment' => 'Comment',
                'save' => 'Save',
                'leaveReview' => 'Leave a review',
                'reviewCheckInfo' => 'We check the review for compliance with the platform rules and legislation. This will take no more than 24 hours.',
                'reviewRejectedInfo' => 'Unfortunately, your review was rejected. Please check the correctness of the language in which the review is written.',
                'reviewPending' => 'Your review is pending moderation',
                'reviewRejected' => 'Your review has been rejected',
                'helpful' => 'Helpful',
                'share' => 'Share',
                'storeReply' => 'Store Reply',
            ],
            'favorites' => [
                'favorites' => 'Favorites',
                'products' => 'Products',
                'recipes' => 'Recipes',
                'toCatalog' => 'To Catalog',
                'favoritesProductsInfo' => 'Here will be collections of products you liked',
                'favoritesRecipesInfo' => 'Here will be collections of recipes you liked',
            ],
            'checkout' => [
                // Steps
                'title' => 'Checkout',
                'steps_cart' => 'Cart',
                'steps_delivery' => 'Delivery information',
                'steps_payment' => 'Payment',
                'steps_confirm' => 'Confirmation',
                'secondItemSale' => '-{n}% on the second product',
                'takePoints' => 'Deduct {n} points|Deduct {n} point',
                'cashback' => 'Cashback',
                'pointsDate' => 'The points are valid until {n}',
                'firstGift' => 'A gift for the first order',
                'pickUpBonus' => '+ {n} point for pickup|+ {n} points for pickup',
                'daysLeft' => 'Only {count} day left|Only {count} days left',

                // Info notes
                'fill_data_note' => 'Fill in the details, this will determine the delivery cost and the final order amount',
                'payment_note' => 'Pay for the order online or in cash to the courier',
                'confirmation_note' => 'Confirmation will be sent to your email or phone number. You can also view order details on the website in your personal account',
                'await_call' => 'Wait for a manager’s call',
                'payment_after_assembly' => 'Payment is made only after the order is assembled',

                // Recipient
                'recipient' => 'Recipient',
                'recipient_data' => 'Recipient information',
                'lastname' => 'Your last name',
                'firstname' => 'Your first name',
                'middlename' => 'Your middle name',
                'contact_details' => 'Contact details',
                'email' => 'Your e-mail',
                'phone' => 'Your phone number',

                // Delivery
                'delivery' => 'Delivery',
                'delivery_method' => 'Delivery method',
                'courier' => 'Courier',
                'pickup' => 'Pickup',
                'pickup_points' => 'Pickup points',
                'pickup_point' => 'Pickup point',

                'delivery_address' => 'Delivery address',
                'from_address_note' => 'Assortment, prices and terms depend on the delivery address',
                'select_city' => 'Select city',
                'index' => 'Postal code',
                'street' => 'Street, house number',
                'entrance' => 'Entrance',
                'floor' => 'Floor',
                'apartment' => 'Apartment',

                'delivery_date' => 'Delivery date',
                'change_date' => 'Change date',
                'select_time_slot' => 'Select a delivery time slot',
                'confirm_choice' => 'Confirm selection',

                // Promo
                'apply' => 'Apply',
                'applied' => 'Applied',

                // Delivery details
                'delivery_details_note' => 'Delivery details may change depending on the chosen delivery method and address',
                'delivery_term' => 'Delivery time:',
                'delivery_zone' => 'Delivery zone:',
                'delivery_cost' => 'Delivery cost:',

                // Order details
                'order_summary' => 'Order summary',
                'order_details' => 'Order details',
                'order_amount' => 'Order amount:',
                'order_weight' => 'Order weight:',
                'delivery_label' => 'Delivery:',
                'my_discount' => 'My discount:',
                'promo_code_label' => 'Promo code:',
                'final_order_sum_note' => 'Final order amount is calculated after the order is assembled in the warehouse',

                // Payment
                'payment' => 'Payment method',
                'payment_cash' => 'Cash on delivery',
                'payment_card' => 'Payment by bank card',
                'awaiting_payment' => 'Awaiting payment',
                'payment_link_sent' => 'A payment link has been sent to you via SMS',
                'no_sms_note' => 'If you do not receive the SMS, please contact technical support',

                // Confirmation
                'your_order' => 'Your order',
                'success_confirmed' => 'successfully confirmed',
                'confirmation_email_sent' => 'A confirmation email has been sent to your email address',
                'go_to_account' => 'Go to personal account',

                // Buttons
                'place_order' => 'Place order',

                // Support
                'tech_support' => 'Technical support',
                'contact_us_if' => 'Contact us if you have any questions or problems with placing an order',
                'or_write_us' => 'Or write to us at',
                'support_email' => 'support@example.com',
                'chat_with_support' => 'Chat with technical support',
                'return_policy' => 'Return and exchange policy',
                'faq' => 'Frequently asked questions',
            ],

            'promo_page' => [
                'promotionsAndSpecialOffers' => 'Promotions and Special Offers',
                'allPromotionTypes' => 'All Promotion Types',
                'promotionValidUntil' => 'Promotion valid until',
                'buyWithDiscount' => 'Buy with Discount',
                'recommendedProducts' => 'Recommended Products',
                'daysLeft' => 'Only {count} day left|Only {count} days left',
                'all' => 'All',
                'addToCart' => 'Add to Cart',
                'buyNow' => 'Buy Now',
                'newsletterQuestion' => 'Want to stay updated on new promotions and special offers?',
                'subscribeToNewsletter' => 'Subscribe to our newsletter',
                'enterEmail' => 'Enter your e-mail',
                'subscribe' => 'Subscribe',
                'customerReviews' => 'Customer Reviews',
                'faq' => 'Frequently Asked Questions',
                'backToCatalog' => 'Back to Catalog',
                "seasonal" => "Сезонные предложения",
                "discount" => "Скидки",
                "gift" => "Подарки"
            ],


        ];
        foreach ($langStaticKeyArray as $groupArray) {
            $group = LangStaticKeyGroup::firstOrCreate(['title' => $groupArray['title']], [
                'description' => $groupArray['description'],
                'is_default' => true
            ]);

            foreach ($groupArray['keys'] as $key) {
                $fullKey = $groupArray['title'] . '.' . $key['key'];

                $defaultEn = $enTranslations[$groupArray['title']][$key['key']] ?? null;

                LangStaticKey::firstOrCreate(['key' => $fullKey], [
                    'description' => $key['description'] ?? null,
                    'default' => $key['default'] ?? null,
                    'default_en' => $defaultEn,
                    'is_default' => true,
                    'lang_static_key_group_id' => $group->id
                ]);
            }

        }


        // сохранение переводов в файл при запуске сидера
        $langTitle = 'ru';
        $dir = __DIR__ . '/../../../../../resources/js/src/shared/i18n/messages';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }


        foreach ($langStaticKeyArray as $groupArray) {
            $groupKey = $groupArray['title'];
            $valuesArray = [];

            foreach ($groupArray['keys'] as $key) {
                $valuesArray[$key['key']] = $key['default'];
            }

            $langFileArray[$groupKey] = $valuesArray;
        }

        $jsonString = json_encode($langFileArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filePath = $dir . "/$langTitle.json";

        file_put_contents($filePath, $jsonString);

        $enLangTitle = 'en';
        $enFilePath = $dir . "/$enLangTitle.json";
        $enJsonString = json_encode($enTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($enFilePath, $enJsonString);


    }
}

