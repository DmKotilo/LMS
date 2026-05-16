<?php

namespace Pages\Database\Seeders;

use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use Banner\Models\Banner;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Pages\Models\Page;

class PageSeeder extends Seeder
{
    public function run()
    {
        $pages = [
            [
                'seo_title' => 'Часто задаваемые вопросы',
                'seo_description' => 'Часто задаваемые вопросы',
                'slug' => 'faq',
                'content' => '<div>
                                      <div>
                                        <div>
                                          <nav class="text-complimentary-reg my-2 flex w-fit items-center gap-1 rounded-full px-3 py-2 md:hidden">
                                            <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g>
                                                <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                            </svg>
                                            <a class="text-additional" href="/page/faq">Часто задаваемые вопросы</a>
                                          </nav>
                                        </div>
                                        <div
                                          class="rounded-20 mb-4 h-50 bg-[url(/images/bg-faq.webp)] mask-[url(../../images/masks/for_catalog_mobile.svg)] bg-cover bg-center bg-no-repeat mask-size-[100%_auto] mask-bottom-left mask-no-repeat px-4 pt-4 pb-4 min-[425px]:mask-[url(../../images/masks/for_catalog.svg)] min-[425px]:mask-size-[auto_100%] lg:h-67 lg:px-8 lg:pt-8"
                                        >
                                          <div class="flex h-full flex-col max-md:justify-end gap-8 pb-8 lg:h-47">
                                            <nav class="bg-input text-complimentary-reg flex w-fit items-center gap-1 rounded-full px-3 py-2 max-md:hidden">
                                              <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                  <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                              </svg>
                                              <a class="text-additional" href="/page/faq">Часто задаваемые вопросы</a>
                                            </nav>
                                            <h1 class="font-vast lg:text-vast-title-bold text-vast-mob-title-bold max-w-180 font-bold text-white">Часто задаваемые вопросы</h1>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="mt-4 flex flex-col md:flex-row gap-8 md:mt-17">
                                        <div class="flex flex-col gap-6">
                                          <div class="border-filling rounded-20 border p-3 md:p-6">
                                            <h3 class="text-default-bold md:text-heavy-bold mb-4">Доставка</h3>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Как оформить заказ на сайте?
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Безопасность онлайн платежей
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Правила доставки товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div>
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Возврат товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="border-filling bg-light-gray rounded-20 border p-3 md:p-6">
                                            <h3 class="text-default-bold md:text-heavy-bold mb-4">Оплата</h3>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Как оформить заказ на сайте?
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Безопасность онлайн платежей
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Правила доставки товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div>
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Возврат товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="border-filling rounded-20 border p-3 md:p-6">
                                            <h3 class="text-default-bold md:text-heavy-bold mb-4">Качество мяса</h3>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Как оформить заказ на сайте?
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Безопасность онлайн платежей
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Правила доставки товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div>
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Возврат товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="border-filling bg-light-gray rounded-20 border p-3 md:p-6">
                                            <h3 class="text-default-bold md:text-heavy-bold mb-4">Хранение</h3>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Как оформить заказ на сайте?
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Безопасность онлайн платежей
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Правила доставки товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div>
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Возврат товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="border-filling rounded-20 border p-3 md:p-6">
                                            <h3 class="text-default-bold md:text-heavy-bold mb-4">Возврат</h3>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Как оформить заказ на сайте?
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Безопасность онлайн платежей
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Правила доставки товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div>
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Возврат товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <aside class="md:min-w-[352px]">
                                          <div class="bg-light-gray rounded-20 p-3 md:p-4">
                                            <h5 class="text-default-bold md:text-heavy-bold">Может быть интересно</h5>
                                            <div class="mt-6 flex flex-col gap-6 md:mt-8">
                                              <div class="bg-text relative flex items-center justify-between rounded-2xl text-white">
                                                <p class="text-small-bold px-3 py-4.5">Доставка</p>

                                                <img src="/images/test-images/image_package.webp" class="absolute right-4 -bottom-0 max-h-20" alt="package" />
                                              </div>
                                              <div class="bg-text relative flex items-center justify-between rounded-2xl text-white">
                                                <p class="text-small-bold px-3 py-4.5">Оплата</p>

                                                <img src="/images/test-images/image_phone_credit.webp" class="absolute right-4 -bottom-0 max-h-20" alt="package" />
                                              </div>
                                              <div class="bg-text relative flex items-center justify-between rounded-2xl text-white">
                                                <p class="text-small-bold px-3 py-4.5">Условия возврата</p>

                                                <img src="/images/test-images/image_exclamation.webp" class="absolute right-4 bottom-1 max-h-18" alt="package" />
                                              </div>
                                            </div>
                                          </div>
                                          <div class="rounded-20 border-filling mt-6 border p-3 md:p-4">
                                            <h5 class="text-small-medium md:text-default-medium">Контактная информация</h5>
                                            <div class="border-b-filling mt-4 flex flex-col gap-2 border-b pb-6 md:mt-6">
                                              <p class="flex items-center gap-2">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_4539_185156)">
                                                    <path
                                                      d="M14.25 4.5C15.5114 4.83218 16.6621 5.4932 17.5844 6.41557C18.5068 7.33793 19.1678 8.48858 19.5 9.75"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M13.5 7.5C15.0488 7.91438 16.0856 8.95125 16.5 10.5"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M14.6616 14.3757C14.7654 14.3066 14.8849 14.2645 15.0091 14.2532C15.1334 14.2419 15.2585 14.2618 15.3731 14.311L19.7944 16.292C19.9434 16.3557 20.0677 16.4659 20.1489 16.6062C20.23 16.7464 20.2635 16.9092 20.2444 17.0701C20.0987 18.1586 19.5627 19.1571 18.736 19.88C17.9093 20.6029 16.8482 21.0009 15.75 21.0001C12.3685 21.0001 9.12548 19.6568 6.73439 17.2657C4.3433 14.8746 3 11.6316 3 8.2501C2.99916 7.15192 3.3972 6.0908 4.12009 5.2641C4.84298 4.4374 5.84152 3.90138 6.93 3.75573C7.09091 3.73661 7.25368 3.77012 7.39395 3.85124C7.53422 3.93236 7.64444 4.05673 7.70813 4.20573L9.68906 8.63073C9.73774 8.74438 9.75756 8.8683 9.74676 8.99146C9.73596 9.11463 9.69489 9.23321 9.62719 9.33666L7.62375 11.7189C7.55269 11.8261 7.51066 11.9499 7.50179 12.0783C7.49291 12.2066 7.51749 12.335 7.57313 12.451C8.34844 14.0382 9.98906 15.6592 11.5809 16.427C11.6975 16.4824 11.8266 16.5064 11.9553 16.4967C12.084 16.487 12.208 16.4439 12.315 16.3717L14.6616 14.3757Z"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4539_185156">
                                                      <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                                <a href="tel:8 (800) 550-46-22" class="text-mob-small-bold">8 (800) 550-46-22</a>
                                              </p>
                                              <p class="flex items-center gap-2">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_4539_185163)">
                                                    <path
                                                      d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                      stroke="#02283F"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4539_185163">
                                                      <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>

                                                <span class="text-subsidiary-reg sm:text-mob-small-reg">Открыто до 20:00</span>
                                              </p>
                                              <p class="flex items-center gap-2">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3606_683)">
                                                    <path
                                                      d="M12 12.75C13.6569 12.75 15 11.4069 15 9.75C15 8.09315 13.6569 6.75 12 6.75C10.3431 6.75 9 8.09315 9 9.75C9 11.4069 10.3431 12.75 12 12.75Z"
                                                      stroke="#02283F"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M19.5 9.75C19.5 16.5 12 21.75 12 21.75C12 21.75 4.5 16.5 4.5 9.75C4.5 7.76088 5.29018 5.85322 6.6967 4.4467C8.10322 3.04018 10.0109 2.25 12 2.25C13.9891 2.25 15.8968 3.04018 17.3033 4.4467C18.7098 5.85322 19.5 7.76088 19.5 9.75Z"
                                                      stroke="#02283F"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3606_683">
                                                      <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>

                                                <span class="text-subsidiary-reg sm:text-mob-small-reg">Москва, Рябиновая улица, 45, стр. 4</span>
                                              </p>
                                              <p class="flex items-center gap-2">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_4539_185175)">
                                                    <path d="M3 12H21" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path
                                                      d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                      stroke="#02283F"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M15.75 12C15.75 18 12 21 12 21C12 21 8.25 18 8.25 12C8.25 6 12 3 12 3C12 3 15.75 6 15.75 12Z"
                                                      stroke="#02283F"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4539_185175">
                                                      <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                                <a href="https://dostavka.primebeef.ru" target="_blank" class="text-subsidiary-reg sm:text-mob-small-reg">dostavka.primebeef.ru</a>
                                              </p>
                                            </div>
                                            <div class="mt-6">
                                              <h5 class="text-mob-small-reg text-additional">Связаться через мессенджеры</h5>
                                              <div class="text-additional mt-2 flex items-center gap-3">
                                                <a href="https://t.me/primefoods_ru" target="_blank">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentColor" />
                                                    <path
                                                      d="M24.8295 6.34715C24.2312 5.81179 21.8378 4.04824 16.4527 4.01675C16.4527 4.01675 10.1228 3.63884 7.03656 6.47312C5.3045 8.20518 4.70615 10.6931 4.64316 13.8108C4.58018 16.9285 4.48571 22.7545 10.1228 24.3606V26.7854C10.1228 26.7854 10.0913 27.7617 10.7211 27.9507C11.5084 28.2026 11.9493 27.4468 12.7051 26.6595C13.1145 26.2186 13.6814 25.5888 14.0908 25.0849C17.9328 25.3998 20.893 24.6755 21.2394 24.5495C22.0267 24.2976 26.4041 23.7307 27.1284 17.9047C27.8843 11.8583 26.8135 8.04772 24.8295 6.34715ZM25.4909 17.4323C24.8925 22.3136 21.3339 22.6285 20.6726 22.8175C20.3892 22.9119 17.8068 23.5418 14.5317 23.3528C14.5317 23.3528 12.1068 26.2816 11.3195 27.0689C11.1935 27.1948 11.0675 27.2263 10.9731 27.2263C10.8471 27.1948 10.8156 27.0374 10.8156 26.8169C10.8156 26.502 10.8471 22.786 10.8471 22.786C6.0918 21.4633 6.37522 16.4876 6.40672 13.8737C6.4697 11.2599 6.94209 9.14994 8.39072 7.70131C11.0046 5.33941 16.3897 5.68582 16.3897 5.68582C20.9245 5.71731 23.0975 7.07147 23.6013 7.54385C25.2389 8.99248 26.0892 12.4251 25.4909 17.4323Z"
                                                      fill="currentColor"
                                                    />
                                                    <path
                                                      d="M18.6567 13.6865C18.5937 12.4897 17.9953 11.8599 16.8301 11.7969"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M20.2325 14.1874C20.264 13.0851 19.9176 12.1403 19.2563 11.416C18.5634 10.6601 17.6186 10.2507 16.4219 10.1562"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M21.806 14.8213C21.806 12.9003 21.2076 11.3886 20.0738 10.2863C18.9401 9.18406 17.5229 8.61719 15.8223 8.61719"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M16.8933 17.7496C16.8933 17.7496 17.3342 17.7811 17.5546 17.4976L17.9955 16.9308C18.216 16.6474 18.7198 16.4584 19.2552 16.7418C19.5386 16.8993 20.0425 17.2142 20.3574 17.4662C20.7038 17.7181 21.3967 18.2849 21.3967 18.2849C21.7431 18.5684 21.8061 18.9778 21.5856 19.4187C21.3652 19.8281 21.0503 20.206 20.6409 20.5524C20.3259 20.8358 20.011 20.9618 19.6961 21.0248H19.5701C19.4442 21.0248 19.2867 20.9933 19.1607 20.9618C18.6883 20.8358 17.9011 20.4894 16.5469 19.7651C15.6966 19.2927 14.9723 18.7888 14.374 18.3164C14.059 18.0645 13.7126 17.7811 13.3977 17.4347L13.2717 17.3087C12.9253 16.9623 12.6419 16.6474 12.39 16.3325C11.9176 15.7341 11.4137 15.0098 10.9413 14.1595C10.217 12.8368 9.8706 12.0495 9.74463 11.5457C9.71314 11.4197 9.68164 11.2937 9.68164 11.1363V11.0103C9.71313 10.6954 9.87059 10.3805 10.154 10.0655C10.5004 9.68763 10.8783 9.37271 11.2877 9.12078C11.7286 8.90034 12.138 8.96332 12.4214 9.30973C12.4214 9.30973 12.9883 10.0026 13.2402 10.349C13.4607 10.6639 13.7756 11.1678 13.9646 11.4512C14.248 11.9551 14.059 12.4904 13.8071 12.7109L13.2402 13.1518C12.9568 13.3722 12.9883 13.8131 12.9883 13.8131C12.9883 13.8131 13.7756 16.9308 16.8933 17.7496Z"
                                                      fill="currentColor"
                                                    />
                                                  </svg>
                                                </a>

                                                <a href="https://t.me/primefoods_ru" target="_blank">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentcolor" />
                                                    <g clip-path="url(#clip0_4211_122742)">
                                                      <path
                                                        d="M25.4575 6.45514C25.3413 6.3548 25.1999 6.28806 25.0486 6.26213C24.8973 6.23619 24.7417 6.25203 24.5987 6.30795L5.59937 13.7433C5.3301 13.848 5.10214 14.0373 4.9498 14.2828C4.79745 14.5283 4.72898 14.8166 4.75468 15.1043C4.78037 15.3921 4.89886 15.6637 5.09229 15.8783C5.28572 16.0929 5.54362 16.2389 5.82718 16.2942L10.75 17.2608V22.7498C10.749 23.0488 10.8379 23.3412 11.005 23.5891C11.1722 23.837 11.4099 24.029 11.6875 24.1401C11.9646 24.2533 12.2694 24.2805 12.5621 24.2181C12.8549 24.1557 13.1221 24.0067 13.3291 23.7905L15.7028 21.3286L19.4687 24.6248C19.7404 24.8658 20.0909 24.9992 20.4541 24.9998C20.6132 24.9997 20.7714 24.9747 20.9228 24.9258C21.1703 24.8472 21.3928 24.7053 21.5683 24.514C21.7438 24.3227 21.8662 24.0887 21.9231 23.8355L25.7284 7.28108C25.7625 7.13178 25.7552 6.97603 25.7075 6.83053C25.6598 6.68503 25.5734 6.55527 25.4575 6.45514ZM20.4559 23.4998L12.7047 16.703L23.8609 8.70702L20.4559 23.4998Z"
                                                        fill="currentcolor"
                                                      />
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_4211_122742">
                                                        <rect width="24" height="24" fill="white" transform="translate(4 4)" />
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                </a>
                                                <a href="https://t.me/primefoods_ru" target="_blank">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="30.891" height="30.9978" rx="7.5" stroke="currentColor" />
                                                    <path
                                                      d="M10.5981 24.1914L10.9593 24.4055C12.477 25.3062 14.2171 25.7826 15.9915 25.7835H15.9954C21.4448 25.7835 25.88 21.3493 25.8821 15.8995C25.8829 13.2583 24.8556 10.7751 22.9889 8.90675C21.1221 7.03872 18.6401 6.00924 15.9989 6.00814C10.5453 6.00814 6.11011 10.4416 6.10805 15.8914C6.10733 17.759 6.62975 19.5778 7.61959 21.1513L7.85486 21.5253L6.85583 25.1726L10.5981 24.1914ZM4 27.9978L5.68754 21.8358C4.64665 20.0324 4.09906 17.9866 4.09992 15.8909C4.10245 9.33425 9.43828 4 15.9956 4C19.1775 4.00137 22.1641 5.23973 24.41 7.48748C26.6556 9.73519 27.892 12.723 27.891 15.9005C27.8882 22.4568 22.5512 27.7921 15.9956 27.7921C15.9951 27.7921 15.9958 27.7921 15.9956 27.7921H15.9905C13.9997 27.7914 12.0436 27.292 10.3062 26.3444L4 27.9978Z"
                                                      fill="currentColor"
                                                    />
                                                    <path
                                                      d="M4.51034 15.8935C4.50962 17.9164 5.03822 19.8915 6.04331 21.6327L4.41406 27.5814L10.5016 25.9851C12.1789 26.8991 14.0673 27.3816 15.9892 27.3822H15.9941C22.3231 27.3822 27.4746 22.2321 27.4773 15.9025C27.4785 12.835 26.2851 9.95103 24.1169 7.78091C21.949 5.61111 19.0658 4.4155 15.9936 4.41406C9.6643 4.41406 4.51309 9.56347 4.51034 15.8935ZM15.9941 27.3822C15.9939 27.3822 15.994 27.3822 15.9941 27.3822V27.3822Z"
                                                      fill="white"
                                                    />
                                                    <path
                                                      d="M4.09992 15.8913C4.0992 17.9869 4.64678 20.033 5.68768 21.8362L4 27.9978L10.306 26.3444C12.0435 27.2919 13.9995 27.7912 15.9903 27.7921H15.9954C22.551 27.7921 27.888 22.4568 27.8908 15.9005C27.8918 12.7228 26.6555 9.73509 24.4098 7.48748C22.1638 5.23987 19.1774 4.00137 15.9954 4C9.43843 4 4.10246 9.33425 4.09972 15.8909M7.85499 21.5255L7.61959 21.1516C6.62976 19.5779 6.10734 17.7591 6.10806 15.8918C6.11022 10.4423 10.5453 6.00848 15.9989 6.00848C18.6399 6.00951 21.1219 7.03909 22.9889 8.90709C24.8557 10.7752 25.883 13.2586 25.8822 15.8995C25.8798 21.3493 21.4446 25.7835 15.9952 25.7835H15.9913C14.2169 25.7825 12.4767 25.3062 10.9591 24.4056L10.5979 24.1915L6.85568 25.1727L7.85499 21.5255ZM15.9954 27.7921C15.9952 27.7921 15.9953 27.7921 15.9954 27.7921V27.7921Z"
                                                      fill="currentColor"
                                                    />
                                                    <path
                                                      fill-rule="evenodd"
                                                      clip-rule="evenodd"
                                                      d="M13.0225 10.9268C12.7998 10.4318 12.5655 10.4219 12.3537 10.4131C12.1804 10.4058 11.9821 10.4063 11.7841 10.4063C11.5858 10.4063 11.2638 10.4806 10.9916 10.7781C10.719 11.0755 9.95117 11.7945 9.95117 13.2568C9.95117 14.7193 11.0164 16.1323 11.1648 16.3308C11.3134 16.529 13.221 19.6258 16.2419 20.8171C18.7527 21.8072 19.2636 21.6103 19.8086 21.5607C20.3535 21.5112 21.5671 20.8419 21.8147 20.1478C22.0624 19.4539 22.0624 18.859 21.9882 18.7348C21.9139 18.6109 21.7156 18.5365 21.4184 18.388C21.1211 18.2393 19.6599 17.5202 19.3875 17.421C19.115 17.322 18.9168 17.2725 18.7185 17.57C18.5204 17.8672 17.9512 18.5365 17.7777 18.7348C17.6044 18.9334 17.4309 18.9582 17.1337 18.8095C16.8364 18.6604 15.8791 18.3468 14.7435 17.3344C13.8599 16.5466 13.2634 15.5737 13.0899 15.2761C12.9166 14.9789 13.0714 14.8179 13.2204 14.6697C13.354 14.5365 13.5178 14.3227 13.6664 14.1492C13.8147 13.9756 13.8642 13.8518 13.9633 13.6535C14.0625 13.4551 14.0129 13.2816 13.9386 13.1329C13.8642 12.9842 13.2868 11.5145 13.0225 10.9268Z"
                                                      fill="currentColor"
                                                    />
                                                  </svg>
                                                </a>
                                                <a href="https://vk.com/primefoods_ru" target="_blank">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentcolor" />
                                                    <path
                                                      fill-rule="evenodd"
                                                      clip-rule="evenodd"
                                                      d="M15.7425 22.9413H17.177C17.177 22.9413 17.6102 22.8925 17.8317 22.6488C18.0353 22.4248 18.0288 22.0045 18.0288 22.0045C18.0288 22.0045 18.0008 20.0363 18.894 19.7465C19.7748 19.4608 20.9057 21.6486 22.1042 22.4899C23.0106 23.1264 23.6994 22.9871 23.6994 22.9871L26.9045 22.9413C26.9045 22.9413 28.5811 22.8355 27.7861 21.4876C27.721 21.3775 27.3229 20.4906 25.4029 18.6683C23.393 16.761 23.6625 17.0696 26.0833 13.7704C27.5576 11.7612 28.1469 10.5346 27.9628 10.0093C27.7873 9.50879 26.7029 9.641 26.7029 9.641L23.0942 9.66382C23.0942 9.66382 22.8265 9.62657 22.6282 9.7479C22.4342 9.86654 22.3098 10.1438 22.3098 10.1438C22.3098 10.1438 21.7384 11.6984 20.9769 13.0208C19.37 15.8108 18.7274 15.9585 18.4647 15.7849C17.8536 15.3811 18.0063 14.163 18.0063 13.2974C18.0063 10.5935 18.4074 9.46619 17.2253 9.17437C16.8331 9.07759 16.5441 9.01355 15.5409 9.0031C14.2532 8.9897 13.1635 9.00716 12.5464 9.31626C12.1359 9.52184 11.8191 9.97983 12.0122 10.0062C12.2507 10.0387 12.7908 10.1553 13.0771 10.5536C13.447 11.0682 13.4341 12.2234 13.4341 12.2234C13.4341 12.2234 13.6466 15.4063 12.9378 15.8015C12.4514 16.0727 11.7841 15.5191 10.3515 12.9879C9.6176 11.6913 9.06328 10.258 9.06328 10.258C9.06328 10.258 8.95654 9.9902 8.76589 9.84684C8.53466 9.67315 8.21157 9.6181 8.21157 9.6181L4.78225 9.641C4.78225 9.641 4.26757 9.65569 4.07843 9.8846C3.91017 10.0884 4.065 10.5093 4.065 10.5093C4.065 10.5093 6.7496 16.9317 9.78966 20.1683C12.5775 23.1361 15.7425 22.9413 15.7425 22.9413Z"
                                                      fill="currentcolor"
                                                    />
                                                  </svg>
                                                </a>
                                              </div>
                                            </div>
                                            <button
                                              class="text-mob-small-reg bg-filling mt-6 w-full cursor-pointer rounded-full py-4 transition-colors duration-200 ease-in hover:bg-gray-300"
                                            >
                                              Обратная связь
                                            </button>
                                          </div>
                                        </aside>
                                      </div>
                                      <div class="bg-text rounded-20 mt-10 p-4 md:p-6 text-white md:mt-20">
                                        <h4 class="text-default-bold md:text-heavy-bold">Не нашли ответ? Задайте вопрос</h4>
                                        <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                          Мы свяжемся с вами и предоставим консультацию. Наши эксперты всегда помогут определиться с выбором
                                        </p>
                                        <form action="#" method="post" class="mt-4 md:mt-8">
                                          <div class="flex flex-col md:items-center md:flex-row gap-2">
                                            <div class="flex flex-col md:flex-grow md:flex-row items-center gap-2">
                                              <input
                                                class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                required
                                                placeholder="Ваше имя"
                                                type="text"
                                              />
                                              <input
                                                class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                required
                                                placeholder="+7 (000) 000-00-00"
                                                type="text"
                                              />
                                            </div>
                                            <button type="submit" class="v-button v-button_light ">
                                              <span class="v-button__label">Связаться с экспертом</span>
                                              <span class="v-button__arrow">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3200_28568)">
                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3200_28568">
                                                      <rect width="20" height="20" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </span>
                                            </button>
                                          </div>
                                          <div class="mt-2 flex items-center gap-2">
                                            <label class="flex cursor-pointer md:items-center gap-2">
                                              <input id="check" type="checkbox" class="peer hidden" />
                                              <span
                                                id="checkbox-box"
                                                class="border-default peer-checked:bg-default flex h-5 w-5 flex-grow md:items-center justify-center rounded border transition-all duration-200 hover:border-gray-500"
                                              >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="checkbox-checkmark">
                                                  <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                              </span>
                                              <span class="text-complimentary-reg">
                                                Нажимая на кнопку «Подписаться» я подтверждаю, что ознакомился с
                                                <a href="/page/privacy-policy" class="underline">политикой конфиденциальности</a> и даю согласие на обработку персональных данных
                                              </span>
                                            </label>
                                          </div>
                                        </form>
                                      </div>
                                      <div class="mt-10 md:mt-20">
                                        <h4 class="text-default-bold md:text-heavy-bold">Информация о гарантии качества продукции</h4>
                                        <div class="mt-6 grid gap-8 md:mt-8 md:grid-cols-4">
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_5027_221397)">
                                                  <path
                                                    d="M26.5 27C28.433 27 30 25.433 30 23.5C30 21.567 28.433 20 26.5 20C24.567 20 23 21.567 23 23.5C23 25.433 24.567 27 26.5 27Z"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M8.5 27C11.5376 27 14 24.5376 14 21.5C14 18.4624 11.5376 16 8.5 16C5.46243 16 3 18.4624 3 21.5C3 24.5376 5.46243 27 8.5 27Z"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M8.5 23C9.32843 23 10 22.3284 10 21.5C10 20.6716 9.32843 20 8.5 20C7.67157 20 7 20.6716 7 21.5C7 22.3284 7.67157 23 8.5 23Z"
                                                    fill="#02283F"
                                                  />
                                                  <path
                                                    d="M29 21V16.75C28.9999 16.535 28.9306 16.3258 28.8023 16.1533C28.6739 15.9808 28.4934 15.8543 28.2875 15.7925L18 13V6H7V12"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path d="M5 6H7" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M18 6H20" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path
                                                    d="M5 12H8.5C11.0196 12 13.4359 13.0009 15.2175 14.7825C16.9991 16.5641 18 18.9804 18 21.5V23H23.035"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path d="M18 13V23" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M23 14.3538V9" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221397">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">Полный цикл производства</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">от разведения бычков до их откорма и разделки</p>
                                            </div>
                                          </div>
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_5027_221412)">
                                                  <path
                                                    d="M7 3C7 4.5913 7.63214 6.11742 8.75736 7.24264C9.88258 8.36786 11.4087 9 13 9H19C20.5913 9 22.1174 8.36786 23.2426 7.24264C24.3679 6.11742 25 4.5913 25 3"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M22 20H10C7.79086 20 6 21.7909 6 24C6 26.2091 7.79086 28 10 28H22C24.2091 28 26 26.2091 26 24C26 21.7909 24.2091 20 22 20Z"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path d="M10 24H12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M20 24H22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path
                                                    d="M12.5 17C13.3284 17 14 16.3284 14 15.5C14 14.6716 13.3284 14 12.5 14C11.6716 14 11 14.6716 11 15.5C11 16.3284 11.6716 17 12.5 17Z"
                                                    fill="currentColor"
                                                  />
                                                  <path
                                                    d="M19.5 17C20.3284 17 21 16.3284 21 15.5C21 14.6716 20.3284 14 19.5 14C18.6716 14 18 14.6716 18 15.5C18 16.3284 18.6716 17 19.5 17Z"
                                                    fill="currentColor"
                                                  />
                                                  <path
                                                    d="M20 9H24.0975C25.4836 8.99961 26.8271 9.47915 27.8997 10.3571C28.9723 11.2351 29.7078 12.4574 29.9813 13.8162C30.0082 13.9605 30.0031 14.1089 29.9663 14.251C29.9294 14.393 29.8618 14.5252 29.7682 14.6382C29.6745 14.7512 29.5571 14.8421 29.4244 14.9047C29.2916 14.9672 29.1467 14.9997 29 15H24"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M12.0002 9H7.90273C6.51662 8.99961 5.17313 9.47915 4.10055 10.3571C3.02796 11.2351 2.29245 12.4574 2.01898 13.8162C1.99202 13.9605 1.99713 14.1089 2.03395 14.251C2.07078 14.393 2.13842 14.5252 2.23207 14.6382C2.32572 14.7512 2.44308 14.8421 2.57584 14.9047C2.7086 14.9672 2.85349 14.9997 3.00023 15H8.00023"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M8 20.535V13C8 11.9391 8.42143 10.9217 9.17157 10.1716C9.92172 9.42143 10.9391 9 12 9H20C21.0609 9 22.0783 9.42143 22.8284 10.1716C23.5786 10.9217 24 11.9391 24 13V20.535"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221412">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">200 дневный откорм</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">
                                                только на основе пятикомпонентной кукурузной смеси, благодаря чему жир в мышечной ткани распределяется равномерно, а мясо обретает
                                                сочность и нежность и сладковатый вкус
                                              </p>
                                            </div>
                                          </div>
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_5027_221427)">
                                                  <path
                                                    d="M10.5334 21.4659L3.64718 18.9284C3.4573 18.8583 3.29347 18.7317 3.17775 18.5657C3.06204 18.3996 3 18.2021 3 17.9997C3 17.7973 3.06204 17.5997 3.17775 17.4337C3.29347 17.2676 3.4573 17.141 3.64718 17.0709L10.5334 14.5334L13.0709 7.64718C13.141 7.4573 13.2676 7.29347 13.4337 7.17775C13.5997 7.06204 13.7973 7 13.9997 7C14.2021 7 14.3996 7.06204 14.5657 7.17775C14.7317 7.29347 14.8583 7.4573 14.9284 7.64718L17.4659 14.5334L24.3522 17.0709C24.5421 17.141 24.7059 17.2676 24.8216 17.4337C24.9373 17.5997 24.9994 17.7973 24.9994 17.9997C24.9994 18.2021 24.9373 18.3996 24.8216 18.5657C24.7059 18.7317 24.5421 18.8583 24.3522 18.9284L17.4659 21.4659L14.9284 28.3522C14.8583 28.5421 14.7317 28.7059 14.5657 28.8216C14.3996 28.9373 14.2021 28.9994 13.9997 28.9994C13.7973 28.9994 13.5997 28.9373 13.4337 28.8216C13.2676 28.7059 13.141 28.5421 13.0709 28.3522L10.5334 21.4659Z"
                                                    stroke="#02283F"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path d="M22 2V8" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M28 9V13" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M19 5H25" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M26 11H30" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221427">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">Идеальная генетика</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">
                                                на производство идут только молодые бычки мясных пород, преимущественно абердин-ангус
                                              </p>
                                            </div>
                                          </div>
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_5027_221438)">
                                                  <path d="M16 29V11" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M16 19L10 16" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M16 16L22 13" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path
                                                    d="M16 22.245C17.4734 23.4275 19.3191 24.0477 21.2075 23.995C25.4488 23.8875 29.0113 20.2175 29 15.975C28.9955 14.3824 28.5157 12.8273 27.622 11.509C26.7284 10.1906 25.4615 9.16906 23.9838 8.57504C23.3857 6.93832 22.2991 5.52495 20.8711 4.52635C19.443 3.52775 17.7426 2.99219 16 2.99219C14.2575 2.99219 12.557 3.52775 11.129 4.52635C9.70093 5.52495 8.61431 6.93832 8.01628 8.57504C6.53773 9.16938 5.27036 10.1917 4.37664 11.511C3.48292 12.8304 3.00356 14.3865 3.00003 15.98C2.98878 20.2225 6.55253 23.8925 10.7938 24C12.6824 24.0511 14.5277 23.4291 16 22.245Z"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221438">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">Животных выращивают</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">в экологически чистых районах</p>
                                            </div>
                                          </div>
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_5027_221448)">
                                                  <path d="M10 5V11" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M10 16V28" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M26 21H19C19 21 19 8 26 5V28" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path
                                                    d="M6 5L5 11C5 12.3261 5.52678 13.5979 6.46447 14.5355C7.40215 15.4732 8.67392 16 10 16C11.3261 16 12.5979 15.4732 13.5355 14.5355C14.4732 13.5979 15 12.3261 15 11L14 5"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221448">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">Широчайший выбор</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">
                                                стейки, антрекоты, филе, мякоти, вырезка, грудинка, лопатка, ребрышки и т.д.
                                              </p>
                                            </div>
                                          </div>
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                                <g clip-path="url(#clip0_5027_221458)">
                                                  <path d="M12 20L11 16" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M20 20L21 16" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M16 20V16" stroke="#02283F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path
                                                    d="M21.7069 8.14C22.5296 7.95909 23.3813 7.95345 24.2063 8.12345C25.0314 8.29345 25.8114 8.63527 26.4956 9.12668C27.1798 9.61808 27.7528 10.2481 28.1775 10.9756C28.6021 11.7031 28.8687 12.5119 28.9601 13.3493C29.0514 14.1868 28.9654 15.034 28.7077 15.836C28.4499 16.638 28.0261 17.3767 27.464 18.0041C26.9018 18.6314 26.2138 19.1334 25.4448 19.4773C24.6758 19.8212 23.8431 19.9993 23.0007 20H9.00066C8.15828 19.9993 7.32549 19.8212 6.55649 19.4773C5.78749 19.1334 5.09953 18.6314 4.53737 18.0041C3.9752 17.3767 3.55145 16.638 3.29368 15.836C3.0359 15.034 2.94989 14.1868 3.04124 13.3493C3.13259 12.5119 3.39926 11.7031 3.82387 10.9756C4.24849 10.2481 4.82155 9.61808 5.50576 9.12668C6.18996 8.63527 6.96997 8.29345 7.79503 8.12345C8.62008 7.95345 9.47168 7.95909 10.2944 8.14"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M10 10C10 8.4087 10.6321 6.88258 11.7574 5.75736C12.8826 4.63214 14.4087 4 16 4C17.5913 4 19.1174 4.63214 20.2426 5.75736C21.3679 6.88258 22 8.4087 22 10"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M25 19.6562V25.9975C25 26.2627 24.8946 26.5171 24.7071 26.7046C24.5196 26.8921 24.2652 26.9975 24 26.9975H8C7.73478 26.9975 7.48043 26.8921 7.29289 26.7046C7.10536 26.5171 7 26.2627 7 25.9975V19.6562"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221458">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">Высшая степень мраморности</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">
                                                Мраморность придает мясу невероятную мягкость и нежный, насыщенный вкус
                                              </p>
                                            </div>
                                          </div>
                                          <div class="flex max-md:items-start gap-2 md:flex-col md:gap-4">
                                            <div class="bg-light-gray w-fit rounded-full p-3">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                                <g clip-path="url(#clip0_5027_221470)">
                                                  <path
                                                    d="M23.5 23H11.3962C10.9279 22.9999 10.4744 22.8355 10.1148 22.5353C9.75527 22.2352 9.51247 21.8183 9.42875 21.3575L6.09125 3H3"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M11.5 28C12.8807 28 14 26.8807 14 25.5C14 24.1193 12.8807 23 11.5 23C10.1193 23 9 24.1193 9 25.5C9 26.8807 10.1193 28 11.5 28Z"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M23.5 28C24.8807 28 26 26.8807 26 25.5C26 24.1193 24.8807 23 23.5 23C22.1193 23 21 24.1193 21 25.5C21 26.8807 22.1193 28 23.5 28Z"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M8.81875 18H24.5125C24.9809 17.9999 25.4344 17.8355 25.7939 17.5353C26.1535 17.2352 26.3963 16.8183 26.48 16.3575L28 8H7"
                                                    stroke="#02283F"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_5027_221470">
                                                    <rect width="32" height="32" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div>
                                              <h5 class="text-mob-small-medium md:text-default-medium">Продажа оптом и в розницу</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2 md:mt-4">Быстрая доставка</p>
                                            </div>
                                          </div>
                                          <div><img src="/images/test-images/image_faq.png" alt="изображение стейков" /></div>
                                        </div>
                                      </div>
                                    </div>
                                    <a href="/catalog">
                                      <button type="submit" class="v-button v-button_dark mx-auto mt-8 w-fit">
                                        <span class="v-button__label">Назад в каталог</span>
                                        <span class="v-button__arrow">
                                          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3200_28568)">
                                              <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </g>
                                            <defs>
                                              <clipPath id="clip0_3200_28568">
                                                <rect width="20" height="20" fill="white"></rect>
                                              </clipPath>
                                            </defs>
                                          </svg>
                                        </span>
                                      </button>
                                    </a>',
                'is_active' => true,
            ],
            [
                'seo_title' => 'Политика конфиденциальности',
                'seo_description' => 'Политика конфиденциальности',
                'slug' => 'privacy-policy',
                'content' => '
                <div>
                        <div>
                          <nav class="text-complimentary-reg my-2 flex w-fit items-center gap-1 rounded-full px-3 py-2 md:hidden">
                            <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g>
                                <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                              </g>
                            </svg>
                            <a class="text-additional" href="/page/privacy-policy">Политика конфиденциальности</a>
                          </nav>
                        </div>
                        <div
                          class="rounded-20 catalog-banner bg-filling mb-4 h-44 mask-[url(../../images/masks/for_catalog_mobile.svg)] mask-size-[100%_auto] mask-bottom-left mask-no-repeat px-4 pt-4 min-[425px]:mask-[url(../../images/masks/for_catalog.svg)] min-[425px]:mask-size-[auto_100%] lg:h-65 lg:px-8 lg:pt-8"
                        >
                          <div class="flex h-36 flex-col justify-end gap-3 pb-8 md:justify-between lg:h-47">
                            <nav class="bg-input text-complimentary-reg flex w-fit items-center gap-1 rounded-full px-3 py-2 max-md:hidden">
                              <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                  <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                              </svg>
                              <a class="text-additional" href="/page/privacy-policy">Политика конфиденциальности</a>
                            </nav>
                            <div class="flex flex-col gap-3">
                              <h1 class="font-vast lg:text-vast-title-bold text-[16px]/[20px] font-bold">Политика конфиденциальности</h1>
                              <p class="text-subsidiary-reg">Редакция от 15.12.2025</p>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="mt-4 flex flex-col gap-8 md:mt-17">
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">1. Термины и определения</h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>
                              Компания — общество с ограниченной ответственностью «ТД ПРАЙМ ФУДС» (ООО «ТД ПРАЙМ ФУДС»), ИНН 9714045758 ОГРН 1247700280244,
                              расположенное по адресу: 125252, город Москва, 3-я Песчаная ул., д. 2а.
                            </p>
                            <p>Пользователь — любое физическое лицо, осуществляющее доступ к Сервису посредством сети Интернет.</p>
                            <p>
                              Сервис — официальный сайт Интернет-магазина <a href="https://primefoods.ru" class="font-bold">https://primefoods.ru</a>
                              и принадлежащие ему поддомены, а также иные интерфейсы, посредством которых Пользователь может получить доступ к Интернет-магазину.
                              Сервис предоставляет Пользователю Компания. Вопросы продажи товаров с использованием Сервиса регулируются Условиями продажи товаров
                              Компанией.
                            </p>
                            <p>
                              Интернет-магазин— раздел Сервиса, где представлены товары, предлагаемые Компанией для приобретения, а также условия оплаты и доставки
                              товаров Пользователям.
                            </p>
                            <p>
                              Персональные данные— любая информация, относящаяся к прямо или косвенно определенному или определяемому физическому лицу (Пользователю).
                            </p>
                            <p>
                              Обработка персональных данных— любое действие или совокупность действий, совершаемых с персональными данными, включая сбор, запись,
                              систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (предоставление, доступ),
                              блокирование, удаление, уничтожение.
                            </p>
                            <p>
                              Партнеры Компании— юридические лица, с которыми Компания проводит совместные аналитические исследования для улучшения Сервиса и
                              предоставления Пользователю дополнительных возможностей, а также оказывает услуги по доставке товаров Пользователю. Такие лица могут
                              оказывать Компании (аффилированным с Компанией лицам) информационные и аналитические услуги для этих целей.
                            </p>
                          </div>
                        </div>
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">2. Общие положения</h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>
                              Настоящая Политика действует в отношении любой информации, в том числе, Персональных данных, передаваемых Пользователями Компании в
                              процессе продажи товаров с использованием Сервиса, и определяет порядок и условия Обработки такой информации.
                            </p>
                            <p>Информация Пользователя, обрабатываемая при использовании Сервиса.</p>
                            <p>
                              В процессе использования Сервиса Пользователем Компания может обрабатывать Персональные данные Пользователя в целях и в сроки, указанные
                              в Приложении 1 к настоящей Политике. В Приложении 1 также описаны конкретные категории Персональных данных, подлежащие обработке,
                              способы обработки и уничтожения Персональных данных по истечении сроков обработки.
                            </p>
                            <p>
                              Компания вправе в указанных целях вносить Персональные данные в информационные системы, хранить и обрабатывать любыми не противоречащими
                              законодательству Российской Федерации способами в течение срока правоотношений с Пользователем по исполнению Условий продажи товаров,
                              представленных в Сервисе, а также сроков, установленных действующим законодательством. По достижению целей обработки или в случае утраты
                              необходимости в достижении этих целей, если иное не предусмотрено законодательством, либо иное отдельно не согласовано сторонами,
                              обрабатываемые Персональные данные подлежат уничтожению.
                            </p>
                            <p>
                              Обязательная для предоставления информация отмечена в Сервисе специальными маркировками, остальная информация предоставляется на
                              усмотрение Пользователей.
                            </p>
                            <p>
                              В Компании не обрабатываются биометрические данные, а также сведения, касающиеся расовой, национальной принадлежности, политических
                              взглядов, религиозных или философских убеждений, интимной жизни.
                            </p>
                          </div>
                        </div>
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">3. Принципы обработки Персональных данных</h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>Компания обрабатывает Персональные данные на основании следующих принципов:</p>
                            <p>обработка Персональных данных осуществляется на законной и справедливой основе;</p>
                            <p>обработка Персональных данных ограничивается достижением конкретных, заранее определенных и законных целей;</p>
                            <p>не допускается обработка Персональных данных, несовместимая с целями сбора Персональных данных;</p>
                            <p>
                              не допускается объединение баз данных, содержащих Персональные данные, обработка которых осуществляется в целях, несовместимых между
                              собой;
                            </p>
                            <p>обработке подлежат только Персональные данные, которые отвечают целям их обработки;</p>
                            <p>
                              содержание и объем обрабатываемых Персональных данных соответствуют заявленным целям обработки. Не допускается избыточность
                              обрабатываемых Персональных данных по отношению к заявленным целям их обработки;
                            </p>
                            <p>
                              при обработке Персональных данных обеспечивается точность Персональных данных, их достаточность, а в необходимых случаях и актуальность
                              по отношению к целям обработки Персональных данных, принимаются необходимые меры по удалению или уточнению неполных или неточных
                              Персональных данных;
                            </p>
                            <p>
                              хранение Персональных данных осуществляется в форме, позволяющей определить Пользователя или иного субъекта Персональных данных, не
                              дольше, чем того требуют цели обработки Персональных данных, если срок хранения Персональных данных не установлен федеральным законом,
                              согласием на обработку, договором, стороной которого, выгодоприобретателем или поручителем, по которому является субъект Персональных
                              данных;
                            </p>
                            <p>
                              обрабатываемые Персональные данные уничтожаются по достижении целей обработки или в случае утраты необходимости в достижении этих целей,
                              если иное не предусмотрено федеральным законом;
                            </p>
                            <p>
                              обработка Персональных данных не используется в целях причинения имущественного и/или морального вреда субъектам Персональных данных,
                              затруднения реализации их прав и свобод.
                            </p>
                          </div>
                        </div>
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">
                            4. Условия Обработки информации Пользователя и её передачи третьим лицам
                          </h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>
                              Обработка информации осуществляется Компанией в соответствии с настоящей Политикой, Условиями продажи товаров, представленных в Сервисе,
                              и внутренними актами Компании, а также законодательством Российском Федерации.
                            </p>
                            <p>
                              Обработка информации Пользователя, в том числе, Персональных данных, осуществляется на основании необходимости исполнения Компанией
                              Условий продажи товаров, представленных в Сервисе, в течение всего периода их действия.
                            </p>
                            <p>
                              Кроме этого, Компания вправе обрабатывать Персональные данные в случаях, когда такая обработка необходима для осуществления прав и
                              законных интересов Компании или третьих лиц, если при этом не нарушаются права Пользователя, а также когда обработка необходима для
                              выполнения функций, полномочий и обязанностей, возложенных законодательством Российской Федерации.
                            </p>
                            <p>
                              Осуществление прямых контактов с Пользователем для продвижения товаров, работ и услуг в Интернет-магазине, или с целью сбора отзывов и
                              получения обратной связи о работе Сервиса, допускается только с согласия Пользователя, данного в процессе регистрации и/или
                              использования Сервиса. Пользователь принимает решение о предоставлении информации, в том числе, Персональных данных, и дает согласие на
                              их Обработку свободно, своей волей и в своем интересе. Согласие на Обработку информации даётся Пользователем посредством проставления
                              соответствующей отметки в процессе регистрации и/или использования Сервиса. Компания может запрашивать согласие Пользователя
                              неоднократно при каждом обращении Пользователя. В случае, если при последующих запросах Компанией согласия (например, при заполнении
                              веб-форм с соответствующим полем для проставления галочки о согласии) таковое не будет дано, ранее данное согласие не будет
                              автоматически признаваться отозванным и продолжит действовать в течение указанного в согласии срока.
                            </p>
                            <p>
                              При отсутствии согласия Пользователя на обработку персональных данных в целях продвижения товаров, работ и услуг, Компания осуществляет
                              прямые контакты с Пользователем только для исполнения предусмотренных законом обязанностей (например, в случае направления Пользователю
                              электронного чека о покупке товаров в Сервисе).
                            </p>
                            <p>
                              Обработка информации осуществляется Компанией, а также иными третьими лицами, которые привлекаются Компанией к обработке, или которым
                              передаются Персональные данные в указанных целях на основании договора и (или) в соответствии с требованиями законодательством
                              Российской Федерации. К числу подобных третьих лиц, в частности, относятся:
                            </p>
                            <p>курьеры-партнеры и компании, оказывающие услуги по доставке товаров, заказываемых через Интернет-магазин;</p>
                            <p>контрагенты, оказывающие Компании услуги для целей противодействия мошенничеству;</p>
                            <p>государственные/муниципальные органы власти в случаях, установленных законодательством.</p>
                            <p>
                              Компания имеет право привлекать третьих лиц к обработке полученных Персональных данных и/или передавать им полученные данные, а также
                              получать от них данные в указанных целях без дополнительного согласия Пользователя при условии обеспечения указанными третьими лицами
                              конфиденциальности и безопасности персональных данных при обработке. Допускается обработка Персональных данных указанными третьими
                              лицами с использованием и без использования средств автоматизации, а также совершение ими любых действий по обработке Персональных
                              данных, не противоречащих законодательству Российской Федерации.
                            </p>
                            <p>
                              Компания обязуется принимать необходимые правовые, организационные и технические меры для защиты получаемых персональных данных от
                              неправомерного или случайного доступа к ним, уничтожения, изменения, блокирования, копирования, представления, распространения
                              персональных данных, иных неправомерных действий в отношении персональных данных, и соблюдать принципы и правила обработки персональных
                              данных, предусмотренные Федеральным законом от 27.07.2006 № 152-ФЗ «О персональных данных» и иными соответствующими нормативными актами.
                            </p>
                            <p>
                              В Компании запрещено принятие на основании исключительно автоматизированной обработки Персональных данных решений, порождающих
                              юридические последствия в отношении Пользователя или иным образом затрагивающих его права и законные интересы.
                            </p>
                            <p>
                              Компания не размещает Персональные данные в общедоступных источниках без письменного согласия Пользователя или иного субъекта
                              Персональных данных.
                            </p>
                          </div>
                        </div>
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">5. Изменение Пользователем информации</h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>
                              Пользователь может в любой момент изменить предоставленную им информацию или её часть, воспользовавшись функцией редактирования
                              информации в своём Профиле, или воспользовавшись обращением в Компанию через доступные каналы связи.
                            </p>
                            <p>
                              Пользователь также может удалить предоставленную им информацию, воспользовавшись функцией удаления информации в своём Профиле. При этом
                              удаление Учетной записи означает отзыв согласия Пользователя на Обработку его информации, включая Персональные данные, и приведет к
                              невозможности использования всех или некоторых услуг Интернет-магазина.
                            </p>
                          </div>
                        </div>
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">
                            6. Взаимодействие с Пользователем по вопросам обработки информации
                          </h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>
                              Пользователь вправе получить информацию о том, как Компания обрабатывает информацию и Персональные данные, включая информацию о перечне
                              Персональных данных, основаниях, целях и сроках их обработки, способах обработки и уничтожения Персональных данных, третьих лицах,
                              которым они передаются, а также иную информацию. Помимо этого, Пользователь может направить запрос на уточнение или внесение изменений в
                              Персональные данные. Пользователь также вправе отозвать предоставленные Компании согласия на обработку Персональных данных.
                            </p>
                            <p>
                              В случае возникновения любых вопросов и обращений касательно обработки Персональных данных с использованием Интернет-магазина
                              Пользователь может обратиться по адресу электронной почты ecom@primefoods.ru либо с помощью письменного обращения в адрес Компании.
                            </p>
                            <p>
                              При наличии подписок на получение информационных и рекламных коммуникаций, дополнительно к указанным выше способам Пользователь может
                              обратиться к Компании с просьбой об отписке от таких коммуникаций:
                            </p>
                            <p>
                              путем активации автоматической функции «Отписаться» по ссылке, присутствующей в электронном письме, содержащем коммуникацию. В этом
                              случае Компания прекращает направление коммуникаций на адрес электронной почты либо номер телефона, с которого была активирована
                              функция;
                            </p>
                            <p>
                              путем активации соответствующей функции в личном кабинете Пользователя в Сервисе (если функция доступна и поддерживается
                              пользовательским устройством).
                            </p>
                          </div>
                        </div>
                        <div class="border-b-filling border-b pb-8">
                          <h2 class="text-default-bold md:text-heavy-bold bg-filling w-fit rounded-lg px-2 py-0.5">7. Заключительные положения</h2>
                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col gap-3 md:mt-8 md:gap-4">
                            <p>
                              Политика может время от времени обновляться или иным образом изменяться Компанией, при этом любые изменения подлежат опубликованию
                              Компанией и вступают в силу с момента их публикации на сайте.
                            </p>
                          </div>
                        </div>
                        <a
                          href="/documents/Политика_конфиденциальности_ООО_ТД_ПРАЙМ_ФУДС.pdf"
                          target="_blank"
                          rel="noopener"
                          download="Политика_конфиденциальности_ООО_ТД_ПРАЙМ_ФУДС.pdf"
                        >
                          <button class="v-button v-button_dark mx-auto">
                            <span class="v-button__label">Скачать в PDF</span>
                            <span class="v-button__arrow">
                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_3200_28568)">
                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                                <defs>
                                  <clipPath id="clip0_3200_28568">
                                    <rect width="20" height="20" fill="white"></rect>
                                  </clipPath>
                                </defs>
                              </svg>
                            </span>
                          </button>
                        </a>
                      </div>
                ',
                'is_active' => true,
            ],
            [
                'seo_title' => 'Программа привилегий',
                'seo_description' => 'Условия программы привилегий',
                'slug' => 'privilege-program',
                'content' => '  <div>
                                      <div><nav class="text-complimentary-reg my-2 flex w-fit items-center gap-1 rounded-full px-3 py-2 md:hidden"><a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g>
                                          <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                      </svg> <a class="text-additional" href="/page/privacy-policy">Программа привилегий</a></nav></div>
                                      <div class="rounded-20 mb-4 h-67 bg-[url(/images/privilege_banner.webp)] pb-4 mask-[url(/images/masks/for-previlege-moblie.svg)] bg-cover bg-center bg-no-repeat mask-size-[auto_100%] mask-bottom-left mask-no-repeat px-4 pt-4 min-[425px]:mask-[url(../../images/masks/for_catalog.svg)] min-[425px]:mask-size-[auto_100%] lg:h-100 lg:px-8 lg:pt-8">
                                        <div class="flex h-full flex-col justify-end gap-3 pb-8 md:justify-between lg:h-47"><nav class="bg-input text-complimentary-reg flex w-fit items-center gap-1 rounded-full px-3 py-2 max-md:hidden"><a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <g>
                                            <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </g>
                                        </svg> <a class="text-additional" href="/page/privacy-policy">Программа привилегий</a></nav>
                                          <div class="flex flex-col gap-3 mt-5 text-white">
                                            <h1 class="font-vast lg:text-vast-title-bold text-vast-mob-title-bold font-bold">Программа привилегий</h1>
                                            <p class="text-subsidiary-medium md:text-default-medium flex items-center gap-2"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <path d="M4 7.33594V11.3359H8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M4.06607 17.3319L4.05273 17.3186C4.78607 23.8919 10.7327 28.6119 17.3061 27.8653C23.8794 27.1186 28.5994 21.1719 27.8527 14.5986C27.1061 8.01192 21.1594 3.29195 14.5861 4.03861C10.2661 4.51861 6.5594 7.29195 4.8794 11.2919" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M13.332 18.6693L18.6654 13.3359" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M18.9993 18.8331C18.9993 18.913 18.9193 18.9995 18.8327 18.9995C18.7393 18.9995 18.666 18.9197 18.666 18.8331C18.666 18.74 18.7327 18.6668 18.8327 18.6668V18.6655C18.9127 18.6522 18.9927 18.732 18.9927 18.8252H18.9913" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M12.9997 13.1588C12.9864 13.0657 13.0664 12.9858 13.1597 12.9858C13.2397 12.9725 13.3197 13.0523 13.3197 13.1455C13.3197 13.2254 13.2397 13.3052 13.1464 13.3052V13.3038C13.0531 13.3038 12.9731 13.224 12.9731 13.1309C12.9597 13.1175 12.9597 13.1175 12.9717 13.1175" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg> CASHBACK до 5%</p>
                                            <p class="text-subsidiary-medium md:text-default-medium flex items-center gap-2"><svg width="31" height="32" viewBox="0 0 31 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <path d="M3.875 9.64062H27.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M12.3607 14.8438H9.04102" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M11.625 22.6489H7.75C5.6099 22.6489 3.875 20.9017 3.875 18.7464V8.33997C3.875 6.18469 5.6099 4.4375 7.75 4.4375H23.25C25.3901 4.4375 27.125 6.18469 27.125 8.33997V13.5432" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M20.3418 24.2676L23.571 21.0156" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M28.4167 22.6447C28.4167 26.2368 25.5252 29.1487 21.9583 29.1487C18.3915 29.1487 15.5 26.2368 15.5 22.6447C15.5 19.0526 18.3915 16.1406 21.9583 16.1406C25.5194 16.1349 28.411 19.0375 28.4167 22.6238C28.4167 22.6308 28.4167 22.6377 28.4167 22.6447Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              <path d="M20.0208 21.3477C20.3775 21.3477 20.6667 21.0565 20.6667 20.6973C20.6667 20.3381 20.3775 20.0469 20.0208 20.0469C19.6641 20.0469 19.375 20.3381 19.375 20.6973C19.375 21.0565 19.6641 21.3477 20.0208 21.3477Z" fill="currentColor" stroke="currentColor"></path>
                                              <path d="M23.8958 25.2461C24.2525 25.2461 24.5417 24.9549 24.5417 24.5957C24.5417 24.2365 24.2525 23.9453 23.8958 23.9453C23.5391 23.9453 23.25 24.2365 23.25 24.5957C23.25 24.9549 23.5391 25.2461 23.8958 25.2461Z" fill="currentColor" stroke="currentColor"></path>
                                            </svg> Оплата до 30% от стоимости покупки</p>
                                           <a href="/user/profile/privilege-program">
                                                <button class="v-button v-button_light md:mt-7 w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                                                 <g clip-path="url(#clip0_3200_28568)">
                                                                                                                                   <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                   <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                 </g>
                                                                                                                                 <defs>
                                                                                                                                   <clipPath id="clip0_3200_28568">
                                                                                                                                     <rect width="20" height="20" fill="white"></rect>
                                                                                                                                   </clipPath>
                                                                                                                                 </defs>
                                                                                                                               </svg> </span> </button>
                                                                                                                               </a></div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="mt-4 flex flex-col gap-8 md:mt-17">
                                      <div>
                                        <h2 class="text-default-bold sm:text-heavy-bold">Уровни программы привилегий</h2>
                                        <div class="mt-6 grid gap-2 sm:mt-8 md:grid-cols-3 md:gap-6">
                                          <div class="bg-light-gray rounded-20 p-3 md:p-6">
                                            <div class="flex items-center justify-between">
                                              <div class="bg-additional flex items-center gap-2 rounded-full px-4 py-3 text-white"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white"></rect>
                                                  </clipPath>
                                                </defs>
                                              </svg> <span class="text-default-bold md:text-medium-bold">Rare</span></div>
                                              <div class="text-additional text-default-bold">CASHBACK 2%</div>
                                            </div>
                                            <div class="text-subsidiary-reg md:text-mob-small-reg mt-4 md:mt-6">При сумме заказов за последние 3 месяца до 39 999 руб</div>
                                          </div>
                                          <div class="bg-light-gray rounded-20 p-3 md:p-6">
                                            <div class="flex items-center justify-between">
                                              <div class="bg-text flex items-center gap-2 rounded-full px-4 py-3 text-white"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white"></rect>
                                                  </clipPath>
                                                </defs>
                                              </svg> <span class="text-default-bold md:text-medium-bold">Medium</span></div>
                                              <div class="text-text text-default-bold">CASHBACK 3%</div>
                                            </div>
                                            <div class="text-subsidiary-reg md:text-mob-small-reg mt-4 md:mt-6">При сумме заказов за последние 3 месяца от 40 000 до 69 999 руб</div>
                                          </div>
                                          <div class="bg-light-gray rounded-20 p-3 md:p-6">
                                            <div class="flex items-center justify-between">
                                              <div class="bg-default flex items-center gap-2 rounded-full px-4 py-3 text-white"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white"></rect>
                                                  </clipPath>
                                                </defs>
                                              </svg> <span class="text-default-bold md:text-medium-bold">Well Done</span></div>
                                              <div class="text-default text-default-bold">CASHBACK 5%</div>
                                            </div>
                                            <div class="text-subsidiary-reg md:text-mob-small-reg mt-4 md:mt-6">При сумме заказов за последние 3 месяца от 70 000 руб</div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="mt-10 flex flex-col gap-8 sm:flex-row sm:items-start md:mt-20">
                                        <div class="border-filling rounded-20 border p-3 md:p-6">
                                          <h3 class="text-default-bold md:text-heavy-bold mb-4">О программе привилегий</h3>
                                          <div class="border-b-filling border-b">
                                            <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">Как стать участником? <svg class="accordion-toggle text-additional" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g clip-path="url(#clip0_4286_2582)">
                                                <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                              <defs>
                                                <clipPath id="clip0_4286_2582">
                                                  <rect width="18" height="18" fill="white"></rect>
                                                </clipPath>
                                              </defs>
                                            </svg></div>
                                            <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                              <p>Участником системы лояльности может стать любой зарегистрированный пользователь интернет-магазина.</p>
                                              <p>При регистрации нового пользователя автоматически создается личный бонусный счет. В личном кабинете будет отображаться информация о текущем статусе клиента в системе лояльности, количестве накопленных баллов и доступных скидках.</p>
                                              <a href="/user/profile/privilege-program">
                                                <button class="v-button v-button_outline w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                  <g clip-path="url(#clip0_3200_28568)">
                                                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                  </g>
                                                                                  <defs>
                                                                                    <clipPath id="clip0_3200_28568">
                                                                                      <rect width="20" height="20" fill="white"></rect>
                                                                                    </clipPath>
                                                                                  </defs>
                                                                                </svg> </span> </button> </a></div>
                                          </div>
                                          <div class="border-b-filling border-b">
                                            <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">Начисление бонусных баллов <svg class="accordion-toggle text-additional" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g clip-path="url(#clip0_4286_2582)">
                                                <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                              <defs>
                                                <clipPath id="clip0_4286_2582">
                                                  <rect width="18" height="18" fill="white"></rect>
                                                </clipPath>
                                              </defs>
                                            </svg></div>
                                            <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                              <p>Участником системы лояльности может стать любой зарегистрированный пользователь интернет-магазина.</p>
                                              <p>При регистрации нового пользователя автоматически создается личный бонусный счет. В личном кабинете будет отображаться информация о текущем статусе клиента в системе лояльности, количестве накопленных баллов и доступных скидках.</p>
                                               <a href="/user/profile/privilege-program">
                                                                                              <button class="v-button v-button_outline w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                                                <g clip-path="url(#clip0_3200_28568)">
                                                                                                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                </g>
                                                                                                                                <defs>
                                                                                                                                  <clipPath id="clip0_3200_28568">
                                                                                                                                    <rect width="20" height="20" fill="white"></rect>
                                                                                                                                  </clipPath>
                                                                                                                                </defs>
                                                                                                                              </svg> </span> </button> </a></div>
                                          </div>
                                          <div class="border-b-filling border-b">
                                            <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">Использование бонусных баллов <svg class="accordion-toggle text-additional" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g clip-path="url(#clip0_4286_2582)">
                                                <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                              <defs>
                                                <clipPath id="clip0_4286_2582">
                                                  <rect width="18" height="18" fill="white"></rect>
                                                </clipPath>
                                              </defs>
                                            </svg></div>
                                            <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                              <p>Участником системы лояльности может стать любой зарегистрированный пользователь интернет-магазина.</p>
                                              <p>При регистрации нового пользователя автоматически создается личный бонусный счет. В личном кабинете будет отображаться информация о текущем статусе клиента в системе лояльности, количестве накопленных баллов и доступных скидках.</p>
                                               <a href="/user/profile/privilege-program">
                                                                                              <button class="v-button v-button_outline w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                                                <g clip-path="url(#clip0_3200_28568)">
                                                                                                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                </g>
                                                                                                                                <defs>
                                                                                                                                  <clipPath id="clip0_3200_28568">
                                                                                                                                    <rect width="20" height="20" fill="white"></rect>
                                                                                                                                  </clipPath>
                                                                                                                                </defs>
                                                                                                                              </svg> </span> </button> </a></div>
                                          </div>
                                          <div class="border-b-filling border-b">
                                            <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">Срок действия бонусных баллов <svg class="accordion-toggle text-additional" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g clip-path="url(#clip0_4286_2582)">
                                                <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                              <defs>
                                                <clipPath id="clip0_4286_2582">
                                                  <rect width="18" height="18" fill="white"></rect>
                                                </clipPath>
                                              </defs>
                                            </svg></div>
                                            <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                              <p>Участником системы лояльности может стать любой зарегистрированный пользователь интернет-магазина.</p>
                                              <p>При регистрации нового пользователя автоматически создается личный бонусный счет. В личном кабинете будет отображаться информация о текущем статусе клиента в системе лояльности, количестве накопленных баллов и доступных скидках.</p>
                                               <a href="/user/profile/privilege-program">
                                                                                              <button class="v-button v-button_outline w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                                                <g clip-path="url(#clip0_3200_28568)">
                                                                                                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                </g>
                                                                                                                                <defs>
                                                                                                                                  <clipPath id="clip0_3200_28568">
                                                                                                                                    <rect width="20" height="20" fill="white"></rect>
                                                                                                                                  </clipPath>
                                                                                                                                </defs>
                                                                                                                              </svg> </span> </button> </a></div>
                                          </div>
                                          <div>
                                            <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">Акции и специальные предложения <svg class="accordion-toggle text-additional" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g clip-path="url(#clip0_4286_2582)">
                                                <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                              <defs>
                                                <clipPath id="clip0_4286_2582">
                                                  <rect width="18" height="18" fill="white"></rect>
                                                </clipPath>
                                              </defs>
                                            </svg></div>
                                            <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                              <p>Участником системы лояльности может стать любой зарегистрированный пользователь интернет-магазина.</p>
                                              <p>При регистрации нового пользователя автоматически создается личный бонусный счет. В личном кабинете будет отображаться информация о текущем статусе клиента в системе лояльности, количестве накопленных баллов и доступных скидках.</p>
                                              <a href="/user/profile/privilege-program">
                                                                                             <button class="v-button v-button_outline w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                                               <g clip-path="url(#clip0_3200_28568)">
                                                                                                                                 <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                                 <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                                                               </g>
                                                                                                                               <defs>
                                                                                                                                 <clipPath id="clip0_3200_28568">
                                                                                                                                   <rect width="20" height="20" fill="white"></rect>
                                                                                                                                 </clipPath>
                                                                                                                               </defs>
                                                                                                                             </svg> </span> </button> </a></div>
                                          </div>
                                        </div>
                                        <div class="relative rounded-[40px] bg-[url(/images/privilegues-bg-image.webp)] bg-cover bg-center bg-no-repeat p-4 text-white sm:max-w-[480px] sm:p-8">
                                          <div class="pointer-events-none absolute inset-0 z-0 rounded-[40px] bg-black/40">&nbsp;</div>
                                          <div class="relative z-10 flex flex-col justify-between gap-8 sm:gap-56">
                                            <div>
                                              <h4 class="font-vast text-vast-mob-title-bold md:text-vast-title-bold font-bold max-sm:mt-10">Пригласи друга</h4>
                                              <p class="text-subsidiary-reg sm:text-mob-small-reg mt-3">Пригласи друга и получи 500 баллов</p>
                                            </div>
                                        <a href="/user/profile/privilege-program">
                                            <div> <button class="v-button v-button_light w-fit"> <span class="v-button__label">Стать участником</span> <span class="v-button__arrow"> <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                  <g clip-path="url(#clip0_3200_28568)">
                                                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                                  </g>
                                                                                  <defs>
                                                                                    <clipPath id="clip0_3200_28568">
                                                                                      <rect width="20" height="20" fill="white"></rect>
                                                                                    </clipPath>
                                                                                  </defs>
                                                                                </svg> </span> </button>
                                                                                </a>
                                              <p class="text-complimentary-reg mt-4">Бонусы будут начислены после того, как приглашенный пользователь совершит первую покупку. Приглашенный пользователь также получает приветственные бонусы.</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>',
                'is_active' => true,
            ],
            [
                'seo_title' => 'Подписка на рассылку',
                'seo_description' => 'Подписка на рассылку',
                'slug' => 'newsletter',
                'content' => '<div>
                                      <div>
                                        <nav class="text-complimentary-reg my-2 flex w-fit items-center gap-1 rounded-full px-3 py-2 md:hidden">
                                          <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g>
                                              <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </g>
                                          </svg>
                                          <a class="text-additional" href="/page/newsletter">Подписка на рассылку</a>
                                        </nav>
                                      </div>
                                      <div
                                        class="rounded-20 relative mb-4 h-[444px] bg-[url(/images/newsletter_bg.webp)] mask-[url(../../images/masks/for-newsletter-mobile.svg)] bg-cover bg-center bg-no-repeat mask-size-[100%_auto] mask-bottom-left mask-no-repeat px-4 pt-4 pb-4 md:h-90 md:mask-[url(../../images/masks/for-newsletter.svg)] md:mask-cover lg:px-8 lg:pt-8"
                                      >
                                        <div class="rounded-20 absolute inset-0 z-0 bg-black/45">&nbsp;</div>
                                        <div class="relative z-10 h-full pb-8">
                                          <nav class="bg-input text-complimentary-reg flex w-fit items-center gap-1 rounded-full px-3 py-2 max-md:hidden">
                                            <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g>
                                                <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                            </svg>
                                            <a class="text-additional" href="/page/newsletter">Подписка на рассылку</a>
                                          </nav>
                                          <div class="flex flex-col justify-between gap-2 md:mt-12 md:flex-row">
                                            <div>
                                              <h1 class="font-vast lg:text-vast-title-bold uppercase text-vast-mob-title-bold max-w-180 font-bold text-white">
                                                Подпишитесь на нашу рассылку
                                              </h1>
                                              <p class="text-mob-small-reg mt-3 text-white">Узнавайте первыми о вкусных новинках и выгодных акциях</p>
                                            </div>
                                            <div>
                                              <form id="subscribe-form" class="subscription-form max-w-[550px]">
                                                <div class="flex flex-col gap-2 md:flex-row md:items-center">
                                                  <div class="flex flex-col gap-2">
                                                    <input
                                                      class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                      required
                                                      type="text"
                                                      placeholder="Ваше имя"
                                                    />
                                                    <div class="flex w-full flex-col gap-0.5">
                                                      <input
                                                        type="email"
                                                        placeholder="Ваш e-mail"
                                                        min="11"
                                                        class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                        required
                                                      />
                                                    </div>
                                                    <div class="mt-2 flex items-center gap-2 text-white">
                                                      <label class="relative flex cursor-pointer gap-2 md:items-center">
                                                        <input id="check" name="check" required class="peer absolute top-2 -z-10" type="checkbox" />
                                                        <span
                                                          id="checkbox-box"
                                                          class="border-default peer-checked:bg-default flex h-5 w-5 shrink-0 flex-grow justify-center rounded border bg-white transition-all duration-200 hover:border-gray-500 md:items-center"
                                                        >
                                                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="checkbox-checkmark">
                                                            <path
                                                              d="M4 12.6111L8.92308 17.5L20 6.5"
                                                              stroke="#fff"
                                                              stroke-width="2"
                                                              stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                            ></path>
                                                          </svg>
                                                        </span>
                                                        <span class="text-complimentary-reg">
                                                          Нажимая на кнопку &laquo;Подписаться&raquo; я подтверждаю, что ознакомился с
                                                          <a class="underline" href="/page/privacy-policy">политикой конфиденциальности</a> и даю согласие на обработку персональных
                                                          данных
                                                        </span>
                                                      </label>
                                                    </div>
                                                    <button class="v-button v-button_light w-fit" type="submit">
                                                      <span class="v-button__label">Подписаться на рассылку</span>
                                                      <span class="v-button__arrow">
                                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                          <g clip-path="url(#clip0_3200_28568)">
                                                            <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                          </g>
                                                          <defs>
                                                            <clipPath id="clip0_3200_28568">
                                                              <rect width="20" height="20" fill="white"></rect>
                                                            </clipPath>
                                                          </defs>
                                                        </svg>
                                                      </span>
                                                    </button>
                                                  </div>
                                                </div>
                                              </form>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="mt-4 md:mt-17">
                                      <h2 class="text-default-bold md:text-heavy-bold">Информация о рассылках</h2>
                                      <div class="relative mt-6 flex flex-col justify-between gap-4 md:mt-8 md:gap-8 lg:flex-row">
                                        <div class="basis-1/2">
                                          <div class="text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                            <p>
                                              Подписка на рассылку от магазина премиального мяса &mdash; это отличный способ быть в курсе всех новинок, специальных предложений и
                                              эксклюзивных акций.
                                            </p>
                                            <p>
                                              Рассылка обычно приходит с частотой 1-2 раза в неделю, что позволяет оставаться информированным без излишней загруженности почты. Так
                                              же вы можете регулировать частоту рассылок через настройки личного кабинета.
                                            </p>
                                            <div>
                                              Преимущества подписки:
                                              <ul class="list-disc pl-6">
                                                <li>Экономия времени и денег благодаря персонализированным предложениям</li>
                                                <li>Возможность первыми узнавать о специальных акциях</li>
                                                <li>Получение полезной информации для любителей качественного мяса</li>
                                                <li>Участие в закрытых мероприятиях и дегустациях</li>
                                              </ul>
                                            </div>
                                          </div>
                                          <div class="rounded-20 bg-light-gray mt-6 flex flex-col items-center justify-between p-3 max-md:gap-6 md:mt-8 md:flex-row md:p-4">
                                            <div class="flex flex-col gap-2">
                                              <h4 class="text-mob-small-bold md:text-default-bold">Оформите подписку и наслаждайтесь преимуществами премиального сервиса!</h4>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg">Напишите нам в любой из мессенджеров</p>
                                              <div class="text-additional mt-2 flex items-center gap-3">
                                                <a href="https://t.me/primefoods_ru" target="_blank" rel="noopener">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentColor"></rect>
                                                    <path
                                                      d="M24.8295 6.34715C24.2312 5.81179 21.8378 4.04824 16.4527 4.01675C16.4527 4.01675 10.1228 3.63884 7.03656 6.47312C5.3045 8.20518 4.70615 10.6931 4.64316 13.8108C4.58018 16.9285 4.48571 22.7545 10.1228 24.3606V26.7854C10.1228 26.7854 10.0913 27.7617 10.7211 27.9507C11.5084 28.2026 11.9493 27.4468 12.7051 26.6595C13.1145 26.2186 13.6814 25.5888 14.0908 25.0849C17.9328 25.3998 20.893 24.6755 21.2394 24.5495C22.0267 24.2976 26.4041 23.7307 27.1284 17.9047C27.8843 11.8583 26.8135 8.04772 24.8295 6.34715ZM25.4909 17.4323C24.8925 22.3136 21.3339 22.6285 20.6726 22.8175C20.3892 22.9119 17.8068 23.5418 14.5317 23.3528C14.5317 23.3528 12.1068 26.2816 11.3195 27.0689C11.1935 27.1948 11.0675 27.2263 10.9731 27.2263C10.8471 27.1948 10.8156 27.0374 10.8156 26.8169C10.8156 26.502 10.8471 22.786 10.8471 22.786C6.0918 21.4633 6.37522 16.4876 6.40672 13.8737C6.4697 11.2599 6.94209 9.14994 8.39072 7.70131C11.0046 5.33941 16.3897 5.68582 16.3897 5.68582C20.9245 5.71731 23.0975 7.07147 23.6013 7.54385C25.2389 8.99248 26.0892 12.4251 25.4909 17.4323Z"
                                                      fill="currentColor"
                                                    ></path>
                                                    <path
                                                      d="M18.6567 13.6865C18.5937 12.4897 17.9953 11.8599 16.8301 11.7969"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    ></path>
                                                    <path
                                                      d="M20.2325 14.1874C20.264 13.0851 19.9176 12.1403 19.2563 11.416C18.5634 10.6601 17.6186 10.2507 16.4219 10.1562"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    ></path>
                                                    <path
                                                      d="M21.806 14.8213C21.806 12.9003 21.2076 11.3886 20.0738 10.2863C18.9401 9.18406 17.5229 8.61719 15.8223 8.61719"
                                                      stroke="currentColor"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    ></path>
                                                    <path
                                                      d="M16.8933 17.7496C16.8933 17.7496 17.3342 17.7811 17.5546 17.4976L17.9955 16.9308C18.216 16.6474 18.7198 16.4584 19.2552 16.7418C19.5386 16.8993 20.0425 17.2142 20.3574 17.4662C20.7038 17.7181 21.3967 18.2849 21.3967 18.2849C21.7431 18.5684 21.8061 18.9778 21.5856 19.4187C21.3652 19.8281 21.0503 20.206 20.6409 20.5524C20.3259 20.8358 20.011 20.9618 19.6961 21.0248H19.5701C19.4442 21.0248 19.2867 20.9933 19.1607 20.9618C18.6883 20.8358 17.9011 20.4894 16.5469 19.7651C15.6966 19.2927 14.9723 18.7888 14.374 18.3164C14.059 18.0645 13.7126 17.7811 13.3977 17.4347L13.2717 17.3087C12.9253 16.9623 12.6419 16.6474 12.39 16.3325C11.9176 15.7341 11.4137 15.0098 10.9413 14.1595C10.217 12.8368 9.8706 12.0495 9.74463 11.5457C9.71314 11.4197 9.68164 11.2937 9.68164 11.1363V11.0103C9.71313 10.6954 9.87059 10.3805 10.154 10.0655C10.5004 9.68763 10.8783 9.37271 11.2877 9.12078C11.7286 8.90034 12.138 8.96332 12.4214 9.30973C12.4214 9.30973 12.9883 10.0026 13.2402 10.349C13.4607 10.6639 13.7756 11.1678 13.9646 11.4512C14.248 11.9551 14.059 12.4904 13.8071 12.7109L13.2402 13.1518C12.9568 13.3722 12.9883 13.8131 12.9883 13.8131C12.9883 13.8131 13.7756 16.9308 16.8933 17.7496Z"
                                                      fill="currentColor"
                                                    ></path>
                                                  </svg>
                                                </a>
                                                <a href="https://t.me/primefoods_ru" target="_blank" rel="noopener">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentcolor"></rect>
                                                    <g clip-path="url(#clip0_4211_122742)">
                                                      <path
                                                        d="M25.4575 6.45514C25.3413 6.3548 25.1999 6.28806 25.0486 6.26213C24.8973 6.23619 24.7417 6.25203 24.5987 6.30795L5.59937 13.7433C5.3301 13.848 5.10214 14.0373 4.9498 14.2828C4.79745 14.5283 4.72898 14.8166 4.75468 15.1043C4.78037 15.3921 4.89886 15.6637 5.09229 15.8783C5.28572 16.0929 5.54362 16.2389 5.82718 16.2942L10.75 17.2608V22.7498C10.749 23.0488 10.8379 23.3412 11.005 23.5891C11.1722 23.837 11.4099 24.029 11.6875 24.1401C11.9646 24.2533 12.2694 24.2805 12.5621 24.2181C12.8549 24.1557 13.1221 24.0067 13.3291 23.7905L15.7028 21.3286L19.4687 24.6248C19.7404 24.8658 20.0909 24.9992 20.4541 24.9998C20.6132 24.9997 20.7714 24.9747 20.9228 24.9258C21.1703 24.8472 21.3928 24.7053 21.5683 24.514C21.7438 24.3227 21.8662 24.0887 21.9231 23.8355L25.7284 7.28108C25.7625 7.13178 25.7552 6.97603 25.7075 6.83053C25.6598 6.68503 25.5734 6.55527 25.4575 6.45514ZM20.4559 23.4998L12.7047 16.703L23.8609 8.70702L20.4559 23.4998Z"
                                                        fill="currentcolor"
                                                      ></path>
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_4211_122742">
                                                        <rect width="24" height="24" fill="white" transform="translate(4 4)"></rect>
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                </a>
                                                <a href="https://t.me/primefoods_ru" target="_blank" rel="noopener">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="30.891" height="30.9978" rx="7.5" stroke="currentColor"></rect>
                                                    <path
                                                      d="M10.5981 24.1914L10.9593 24.4055C12.477 25.3062 14.2171 25.7826 15.9915 25.7835H15.9954C21.4448 25.7835 25.88 21.3493 25.8821 15.8995C25.8829 13.2583 24.8556 10.7751 22.9889 8.90675C21.1221 7.03872 18.6401 6.00924 15.9989 6.00814C10.5453 6.00814 6.11011 10.4416 6.10805 15.8914C6.10733 17.759 6.62975 19.5778 7.61959 21.1513L7.85486 21.5253L6.85583 25.1726L10.5981 24.1914ZM4 27.9978L5.68754 21.8358C4.64665 20.0324 4.09906 17.9866 4.09992 15.8909C4.10245 9.33425 9.43828 4 15.9956 4C19.1775 4.00137 22.1641 5.23973 24.41 7.48748C26.6556 9.73519 27.892 12.723 27.891 15.9005C27.8882 22.4568 22.5512 27.7921 15.9956 27.7921C15.9951 27.7921 15.9958 27.7921 15.9956 27.7921H15.9905C13.9997 27.7914 12.0436 27.292 10.3062 26.3444L4 27.9978Z"
                                                      fill="currentColor"
                                                    ></path>
                                                    <path
                                                      d="M4.51034 15.8935C4.50962 17.9164 5.03822 19.8915 6.04331 21.6327L4.41406 27.5814L10.5016 25.9851C12.1789 26.8991 14.0673 27.3816 15.9892 27.3822H15.9941C22.3231 27.3822 27.4746 22.2321 27.4773 15.9025C27.4785 12.835 26.2851 9.95103 24.1169 7.78091C21.949 5.61111 19.0658 4.4155 15.9936 4.41406C9.6643 4.41406 4.51309 9.56347 4.51034 15.8935ZM15.9941 27.3822C15.9939 27.3822 15.994 27.3822 15.9941 27.3822V27.3822Z"
                                                      fill="white"
                                                    ></path>
                                                    <path
                                                      d="M4.09992 15.8913C4.0992 17.9869 4.64678 20.033 5.68768 21.8362L4 27.9978L10.306 26.3444C12.0435 27.2919 13.9995 27.7912 15.9903 27.7921H15.9954C22.551 27.7921 27.888 22.4568 27.8908 15.9005C27.8918 12.7228 26.6555 9.73509 24.4098 7.48748C22.1638 5.23987 19.1774 4.00137 15.9954 4C9.43843 4 4.10246 9.33425 4.09972 15.8909M7.85499 21.5255L7.61959 21.1516C6.62976 19.5779 6.10734 17.7591 6.10806 15.8918C6.11022 10.4423 10.5453 6.00848 15.9989 6.00848C18.6399 6.00951 21.1219 7.03909 22.9889 8.90709C24.8557 10.7752 25.883 13.2586 25.8822 15.8995C25.8798 21.3493 21.4446 25.7835 15.9952 25.7835H15.9913C14.2169 25.7825 12.4767 25.3062 10.9591 24.4056L10.5979 24.1915L6.85568 25.1727L7.85499 21.5255ZM15.9954 27.7921C15.9952 27.7921 15.9953 27.7921 15.9954 27.7921V27.7921Z"
                                                      fill="currentColor"
                                                    ></path>
                                                    <path
                                                      fill-rule="evenodd"
                                                      clip-rule="evenodd"
                                                      d="M13.0225 10.9268C12.7998 10.4318 12.5655 10.4219 12.3537 10.4131C12.1804 10.4058 11.9821 10.4063 11.7841 10.4063C11.5858 10.4063 11.2638 10.4806 10.9916 10.7781C10.719 11.0755 9.95117 11.7945 9.95117 13.2568C9.95117 14.7193 11.0164 16.1323 11.1648 16.3308C11.3134 16.529 13.221 19.6258 16.2419 20.8171C18.7527 21.8072 19.2636 21.6103 19.8086 21.5607C20.3535 21.5112 21.5671 20.8419 21.8147 20.1478C22.0624 19.4539 22.0624 18.859 21.9882 18.7348C21.9139 18.6109 21.7156 18.5365 21.4184 18.388C21.1211 18.2393 19.6599 17.5202 19.3875 17.421C19.115 17.322 18.9168 17.2725 18.7185 17.57C18.5204 17.8672 17.9512 18.5365 17.7777 18.7348C17.6044 18.9334 17.4309 18.9582 17.1337 18.8095C16.8364 18.6604 15.8791 18.3468 14.7435 17.3344C13.8599 16.5466 13.2634 15.5737 13.0899 15.2761C12.9166 14.9789 13.0714 14.8179 13.2204 14.6697C13.354 14.5365 13.5178 14.3227 13.6664 14.1492C13.8147 13.9756 13.8642 13.8518 13.9633 13.6535C14.0625 13.4551 14.0129 13.2816 13.9386 13.1329C13.8642 12.9842 13.2868 11.5145 13.0225 10.9268Z"
                                                      fill="currentColor"
                                                    ></path>
                                                  </svg>
                                                </a>
                                                <a href="https://vk.com/primefoods_ru" target="_blank" rel="noopener">
                                                  <svg
                                                    class="hover:text-default transition-colors duration-300"
                                                    width="32"
                                                    height="32"
                                                    viewBox="0 0 32 32"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                  >
                                                    <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentcolor"></rect>
                                                    <path
                                                      fill-rule="evenodd"
                                                      clip-rule="evenodd"
                                                      d="M15.7425 22.9413H17.177C17.177 22.9413 17.6102 22.8925 17.8317 22.6488C18.0353 22.4248 18.0288 22.0045 18.0288 22.0045C18.0288 22.0045 18.0008 20.0363 18.894 19.7465C19.7748 19.4608 20.9057 21.6486 22.1042 22.4899C23.0106 23.1264 23.6994 22.9871 23.6994 22.9871L26.9045 22.9413C26.9045 22.9413 28.5811 22.8355 27.7861 21.4876C27.721 21.3775 27.3229 20.4906 25.4029 18.6683C23.393 16.761 23.6625 17.0696 26.0833 13.7704C27.5576 11.7612 28.1469 10.5346 27.9628 10.0093C27.7873 9.50879 26.7029 9.641 26.7029 9.641L23.0942 9.66382C23.0942 9.66382 22.8265 9.62657 22.6282 9.7479C22.4342 9.86654 22.3098 10.1438 22.3098 10.1438C22.3098 10.1438 21.7384 11.6984 20.9769 13.0208C19.37 15.8108 18.7274 15.9585 18.4647 15.7849C17.8536 15.3811 18.0063 14.163 18.0063 13.2974C18.0063 10.5935 18.4074 9.46619 17.2253 9.17437C16.8331 9.07759 16.5441 9.01355 15.5409 9.0031C14.2532 8.9897 13.1635 9.00716 12.5464 9.31626C12.1359 9.52184 11.8191 9.97983 12.0122 10.0062C12.2507 10.0387 12.7908 10.1553 13.0771 10.5536C13.447 11.0682 13.4341 12.2234 13.4341 12.2234C13.4341 12.2234 13.6466 15.4063 12.9378 15.8015C12.4514 16.0727 11.7841 15.5191 10.3515 12.9879C9.6176 11.6913 9.06328 10.258 9.06328 10.258C9.06328 10.258 8.95654 9.9902 8.76589 9.84684C8.53466 9.67315 8.21157 9.6181 8.21157 9.6181L4.78225 9.641C4.78225 9.641 4.26757 9.65569 4.07843 9.8846C3.91017 10.0884 4.065 10.5093 4.065 10.5093C4.065 10.5093 6.7496 16.9317 9.78966 20.1683C12.5775 23.1361 15.7425 22.9413 15.7425 22.9413Z"
                                                      fill="currentcolor"
                                                    ></path>
                                                  </svg>
                                                </a>
                                              </div>
                                            </div>
                                            <a href="#newsletter-form">
                                              <button class="v-button v-button_dark">
                                                <span class="v-button__label text-mob-small-reg md:text-nowrap">Подписаться на рассылку</span>
                                                <span class="v-button__arrow">
                                                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_3200_28568)">
                                                      <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_3200_28568">
                                                        <rect width="20" height="20" fill="white"></rect>
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                </span>
                                              </button>
                                            </a>
                                          </div>
                                        </div>
                                        <div class="grid basis-1/2 gap-2 md:grid-cols-2 md:gap-8">
                                          <div class="bg-light-gray rounded-20 flex flex-col p-3 md:p-4">
                                            <div class="flex-auto">
                                              <div class="bg-text flex w-fit items-center justify-center rounded-full p-2 text-white md:p-4">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3200_28568)">
                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3200_28568">
                                                      <rect width="20" height="20" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                            </div>
                                            <div class="max-lg:mt-3">
                                              <h5 class="text-mob-small-bold md:text-default-bold">Эксклюзивные предложения и скидки</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                                получайте доступ к уникальным акциям, которые доступны только подписчикам.
                                              </p>
                                            </div>
                                          </div>
                                          <div class="bg-light-gray rounded-20 flex flex-col p-3 md:p-4">
                                            <div class="flex-auto">
                                              <div class="bg-text flex w-fit items-center justify-center rounded-full p-2 text-white md:p-4">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3200_28568)">
                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3200_28568">
                                                      <rect width="20" height="20" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                            </div>
                                            <div class="max-lg:mt-3">
                                              <h5 class="text-mob-small-bold md:text-default-bold">Обновления ассортимента</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">узнавайте о новых поступлениях и лимитированных продуктах первыми.</p>
                                            </div>
                                          </div>
                                          <div class="bg-light-gray rounded-20 flex flex-col p-3 md:p-4">
                                            <div class="flex-auto">
                                              <div class="bg-text flex w-fit items-center justify-center rounded-full p-2 text-white md:p-4">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3200_28568)">
                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3200_28568">
                                                      <rect width="20" height="20" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                            </div>
                                            <div class="max-lg:mt-3">
                                              <h5 class="text-mob-small-bold md:text-default-bold">Полезные советы и рецепты</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                                получайте рекомендации по приготовлению премиального мяса, чтобы ваши блюда всегда получались идеально.
                                              </p>
                                            </div>
                                          </div>
                                          <div class="bg-light-gray rounded-20 flex flex-col p-3 md:p-4">
                                            <div class="flex-auto">
                                              <div class="bg-text flex w-fit items-center justify-center rounded-full p-2 text-white md:p-4">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3200_28568)">
                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3200_28568">
                                                      <rect width="20" height="20" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                            </div>
                                            <div class="max-lg:mt-3">
                                              <h5 class="text-mob-small-bold md:text-default-bold">Информация о мероприятиях</h5>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">будьте в курсе событий, организуемых магазином</p>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div id="modal" class="bg-text/70 fixed inset-0 z-[3000] hidden items-center justify-center">
                                        <div class="w-full max-w-[328px] rounded-[30px] bg-white p-4 text-center md:max-w-md md:p-8">
                                          <div class="mb-2 flex justify-end">
                                            <button id="close-modal" class="cursor-pointer">
                                              <svg
                                                class="text-subsidiary h-6 w-6 rotate-45 md:h-8 md:w-8"
                                                width="18"
                                                height="18"
                                                viewBox="0 0 18 18"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                              >
                                                <g clip-path="url(#clip0_4286_2582)">
                                                  <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_4286_2582">
                                                    <rect width="18" height="18" fill="white"></rect>
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </button>
                                          </div>
                                          <h4 class="text-default-bold md:text-heavy-bold">Спасибо за подписку!</h4>
                                          <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                            Теперь вы будете первыми узнавать о наших новинках, специальных предложениях и эксклюзивных акциях. Следите за почтой &mdash; впереди
                                            много интересного!
                                          </p>
                                          <a href="/">
                                            <button class="v-button v-button_dark mx-auto mt-6">
                                              <span class="v-button__label text-mob-small-reg md:text-nowrap">Вернуться на главную</span>
                                              <span class="v-button__arrow">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <g clip-path="url(#clip0_3200_28568)">
                                                    <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_3200_28568">
                                                      <rect width="20" height="20" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </span>
                                            </button>
                                          </a>
                                        </div>
                                      </div>
                                    </div>',
                'is_active' => true,
            ],
            [
                'seo_title' => 'Контакты',
                'seo_description' => 'Контакты',
                'slug' => 'contacts',
                'content' => '  <div>
                                        <div>
                                          <nav class="text-complimentary-reg my-2 flex w-fit items-center gap-1 rounded-full px-3 py-2 md:hidden">
                                            <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g>
                                                <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                            </svg>
                                            <a class="text-additional" href="/page/contacts">Контакты</a>
                                          </nav>
                                        </div>
                                        <div
                                          class="rounded-20 relative mb-4 h-50 bg-[url(/images/contacts/contacts_bg.webp)] mask-[url(../../images/masks/for-newsletter-mobile.svg)] bg-cover bg-center bg-no-repeat mask-size-[100%_auto] mask-bottom-left mask-no-repeat px-4 pt-4 pb-4 min-[415px]:mask-[url(../../images/masks/for-newsletter.svg)] min-[415px]:mask-size-[auto_100%] md:h-65 lg:mask-size-[100%_auto] lg:px-8 lg:pt-8"
                                        >
                                          <div class="rounded-20 absolute inset-0 z-0 bg-black/45">&nbsp;</div>
                                          <div class="relative z-10 flex h-full flex-col justify-end md:justify-between md:pb-8">
                                            <nav class="bg-input text-complimentary-reg flex w-fit items-center gap-1 rounded-full px-3 py-2 max-md:hidden">
                                              <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                  <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                              </svg>
                                              <a class="text-additional" href="/page/contacts">Контакты</a>
                                            </nav>
                                            <h1 class="font-vast lg:text-vast-title-bold text-vast-mob-title-bold max-w-180 items-end pb-6 font-bold text-white max-md:flex">
                                              Контакты
                                            </h1>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="mt-6 md:mt-15">
                                        <div class="flex flex-col items-stretch justify-between gap-6 md:flex-row">
                                          <div class="md:flex-1">
                                            <h3 class="text-default-bold md:text-heavy-bold">Интернет-магазин</h3>
                                            <div class="mt-3 h-full">
                                              <div class="flex flex-col justify-between">
                                                <div class="flex flex-col gap-3 md:flex-row md:justify-between md:gap-6">
                                                  <div>
                                                    <!--                    <h5 class="text-mob-small-medium flex items-center gap-2">-->
                                                    <!--                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                        <g clip-path="url(#clip0_4539_185058)">-->
                                                    <!--                          <path d="M6 6L18 18" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                          <path d="M8.25 18H18V8.25" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                        </g>-->
                                                    <!--                        <defs>-->
                                                    <!--                          <clipPath id="clip0_4539_185058">-->
                                                    <!--                            <rect width="24" height="24" fill="white" />-->
                                                    <!--                          </clipPath>-->
                                                    <!--                        </defs>-->
                                                    <!--                      </svg>-->
                                                    <!--                      <span>Интернет магазин</span>-->
                                                    <!--                    </h5>-->
                                                    <div class="border-b-stroke mt-4 flex flex-1 items-center gap-2 border-b pb-2 md:gap-6">
                                                      <div class="flex w-full flex-col gap-2 md:flex-row md:items-center md:gap-8">
                                                        <div class="flex items-center gap-2">
                                                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <g clip-path="url(#clip0_4539_185156)">
                                                              <path
                                                                d="M14.25 4.5C15.5114 4.83218 16.6621 5.4932 17.5844 6.41557C18.5068 7.33793 19.1678 8.48858 19.5 9.75"
                                                                stroke="#02283F"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                              />
                                                              <path
                                                                d="M13.5 7.5C15.0488 7.91438 16.0856 8.95125 16.5 10.5"
                                                                stroke="#02283F"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                              />
                                                              <path
                                                                d="M14.6616 14.3757C14.7654 14.3066 14.8849 14.2645 15.0091 14.2532C15.1334 14.2419 15.2585 14.2618 15.3731 14.311L19.7944 16.292C19.9434 16.3557 20.0677 16.4659 20.1489 16.6062C20.23 16.7464 20.2635 16.9092 20.2444 17.0701C20.0987 18.1586 19.5627 19.1571 18.736 19.88C17.9093 20.6029 16.8482 21.0009 15.75 21.0001C12.3685 21.0001 9.12548 19.6568 6.73439 17.2657C4.3433 14.8746 3 11.6316 3 8.2501C2.99916 7.15192 3.3972 6.0908 4.12009 5.2641C4.84298 4.4374 5.84152 3.90138 6.93 3.75573C7.09091 3.73661 7.25368 3.77012 7.39395 3.85124C7.53422 3.93236 7.64444 4.05673 7.70813 4.20573L9.68906 8.63073C9.73774 8.74438 9.75756 8.8683 9.74676 8.99146C9.73596 9.11463 9.69489 9.23321 9.62719 9.33666L7.62375 11.7189C7.55269 11.8261 7.51066 11.9499 7.50179 12.0783C7.49291 12.2066 7.51749 12.335 7.57313 12.451C8.34844 14.0382 9.98906 15.6592 11.5809 16.427C11.6975 16.4824 11.8266 16.5064 11.9553 16.4967C12.084 16.487 12.208 16.4439 12.315 16.3717L14.6616 14.3757Z"
                                                                stroke="#02283F"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                              />
                                                            </g>
                                                            <defs>
                                                              <clipPath id="clip0_4539_185156">
                                                                <rect width="24" height="24" fill="white" />
                                                              </clipPath>
                                                            </defs>
                                                          </svg>

                                                          <a href="tel:+7 800 101-47-28" class="text-mob-small-bold max-md:text-nowrap">+7 800 101-47-28 </a>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <g>
                                                              <path
                                                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"
                                                                stroke="#02283F"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                              />
                                                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                            </g>
                                                          </svg>

                                                          <a href="mailto:sales@primefoods.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primefoods.ru</a>
                                                        </div>
                                                        <!--                        <div class="text-subsidiary-reg md:text-mob-small-reg mt-2 items-center gap-2 max-md:flex md:hidden">-->
                                                        <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                        <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                        <!--                              <path-->
                                                        <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                        <!--                                stroke="#02283F"-->
                                                        <!--                                stroke-linecap="round"-->
                                                        <!--                                stroke-linejoin="round"-->
                                                        <!--                              />-->
                                                        <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                        <!--                            </g>-->
                                                        <!--                            <defs>-->
                                                        <!--                              <clipPath id="clip0_4539_185163">-->
                                                        <!--                                <rect width="24" height="24" fill="white" />-->
                                                        <!--                              </clipPath>-->
                                                        <!--                            </defs>-->
                                                        <!--                          </svg>-->
                                                        <!--                          <span>Открыто до 20:00</span>-->
                                                        <!--                        </div>-->
                                                      </div>
                                                      <!--                      <div>-->
                                                      <!--                        <div class="mt-2 flex items-center gap-2 md:hidden">-->
                                                      <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                      <!--                            <g>-->
                                                      <!--                              <path-->
                                                      <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                      <!--                                stroke="#02283F"-->
                                                      <!--                                stroke-linecap="round"-->
                                                      <!--                                stroke-linejoin="round"-->
                                                      <!--                              />-->
                                                      <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                      <!--                            </g>-->
                                                      <!--                          </svg>-->

                                                      <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                      <!--                        </div>-->
                                                      <!--                        <div class="text-mob-small-reg items-center gap-2 max-md:hidden md:flex">-->
                                                      <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                      <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                      <!--                              <path-->
                                                      <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                      <!--                                stroke="#02283F"-->
                                                      <!--                                stroke-linecap="round"-->
                                                      <!--                                stroke-linejoin="round"-->
                                                      <!--                              />-->
                                                      <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                      <!--                            </g>-->
                                                      <!--                            <defs>-->
                                                      <!--                              <clipPath id="clip0_4539_185163">-->
                                                      <!--                                <rect width="24" height="24" fill="white" />-->
                                                      <!--                              </clipPath>-->
                                                      <!--                            </defs>-->
                                                      <!--                          </svg>-->
                                                      <!--                          <span>Открыто до 20:00</span>-->
                                                      <!--                        </div>-->
                                                      <!--                        <div class="mt-2 flex items-center gap-2">-->
                                                      <!--                          <svg class="flex-shrink-0" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                      <!--                            <g clip-path="url(#clip0_3606_683)">-->
                                                      <!--                              <path-->
                                                      <!--                                d="M12 12.75C13.6569 12.75 15 11.4069 15 9.75C15 8.09315 13.6569 6.75 12 6.75C10.3431 6.75 9 8.09315 9 9.75C9 11.4069 10.3431 12.75 12 12.75Z"-->
                                                      <!--                                stroke="#02283F"-->
                                                      <!--                                stroke-linecap="round"-->
                                                      <!--                                stroke-linejoin="round"-->
                                                      <!--                              />-->
                                                      <!--                              <path-->
                                                      <!--                                d="M19.5 9.75C19.5 16.5 12 21.75 12 21.75C12 21.75 4.5 16.5 4.5 9.75C4.5 7.76088 5.29018 5.85322 6.6967 4.4467C8.10322 3.04018 10.0109 2.25 12 2.25C13.9891 2.25 15.8968 3.04018 17.3033 4.4467C18.7098 5.85322 19.5 7.76088 19.5 9.75Z"-->
                                                      <!--                                stroke="#02283F"-->
                                                      <!--                                stroke-linecap="round"-->
                                                      <!--                                stroke-linejoin="round"-->
                                                      <!--                              />-->
                                                      <!--                            </g>-->
                                                      <!--                            <defs>-->
                                                      <!--                              <clipPath id="clip0_3606_683">-->
                                                      <!--                                <rect width="24" height="24" fill="white" />-->
                                                      <!--                              </clipPath>-->
                                                      <!--                            </defs>-->
                                                      <!--                          </svg>-->

                                                      <!--                          <span class="text-subsidiary-reg md:">Москва, Рябиновая улица, 45, стр. 4</span>-->
                                                      <!--                        </div>-->
                                                      <!--                      </div>-->
                                                    </div>
                                                    <!--                    <h5 class="text-mob-small-medium mt-2 flex items-center gap-2">-->
                                                    <!--                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                        <g clip-path="url(#clip0_4539_185058)">-->
                                                    <!--                          <path d="M6 6L18 18" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                          <path d="M8.25 18H18V8.25" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                        </g>-->
                                                    <!--                        <defs>-->
                                                    <!--                          <clipPath id="clip0_4539_185058">-->
                                                    <!--                            <rect width="24" height="24" fill="white" />-->
                                                    <!--                          </clipPath>-->
                                                    <!--                        </defs>-->
                                                    <!--                      </svg>-->
                                                    <!--                      <span>Отдел доставки</span>-->
                                                    <!--                    </h5>-->
                                                    <!--                    <div class="max-md:border-b-stroke mt-4 flex items-center gap-2 pb-2 max-md:border-b md:gap-6">-->
                                                    <!--                      <div>-->
                                                    <!--                        <div class="flex items-center gap-2">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185156)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M14.25 4.5C15.5114 4.83218 16.6621 5.4932 17.5844 6.41557C18.5068 7.33793 19.1678 8.48858 19.5 9.75"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M13.5 7.5C15.0488 7.91438 16.0856 8.95125 16.5 10.5"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M14.6616 14.3757C14.7654 14.3066 14.8849 14.2645 15.0091 14.2532C15.1334 14.2419 15.2585 14.2618 15.3731 14.311L19.7944 16.292C19.9434 16.3557 20.0677 16.4659 20.1489 16.6062C20.23 16.7464 20.2635 16.9092 20.2444 17.0701C20.0987 18.1586 19.5627 19.1571 18.736 19.88C17.9093 20.6029 16.8482 21.0009 15.75 21.0001C12.3685 21.0001 9.12548 19.6568 6.73439 17.2657C4.3433 14.8746 3 11.6316 3 8.2501C2.99916 7.15192 3.3972 6.0908 4.12009 5.2641C4.84298 4.4374 5.84152 3.90138 6.93 3.75573C7.09091 3.73661 7.25368 3.77012 7.39395 3.85124C7.53422 3.93236 7.64444 4.05673 7.70813 4.20573L9.68906 8.63073C9.73774 8.74438 9.75756 8.8683 9.74676 8.99146C9.73596 9.11463 9.69489 9.23321 9.62719 9.33666L7.62375 11.7189C7.55269 11.8261 7.51066 11.9499 7.50179 12.0783C7.49291 12.2066 7.51749 12.335 7.57313 12.451C8.34844 14.0382 9.98906 15.6592 11.5809 16.427C11.6975 16.4824 11.8266 16.5064 11.9553 16.4967C12.084 16.487 12.208 16.4439 12.315 16.3717L14.6616 14.3757Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185156">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="tel:8 (800) 550-46-22" class="text-mob-small-bold max-md:text-nowrap"> 8 (800) 550-46-22</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="mt-2 hidden items-center gap-2 md:flex">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g>-->
                                                    <!--                              <path-->
                                                    <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="text-subsidiary-reg md:text-mob-small-reg mt-2 items-center gap-2 max-md:flex md:hidden">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185163">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->
                                                    <!--                          <span>Открыто до 20:00</span>-->
                                                    <!--                        </div>-->
                                                    <!--                      </div>-->
                                                    <!--                      <div>-->
                                                    <!--                        <div class="mt-2 flex items-center gap-2 md:hidden">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g>-->
                                                    <!--                              <path-->
                                                    <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="text-mob-small-reg items-center gap-2 max-md:hidden md:flex">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185163">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->
                                                    <!--                          <span>Открыто до 20:00</span>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="mt-2 flex items-center gap-2">-->
                                                    <!--                          <svg class="flex-shrink-0" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_3606_683)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 12.75C13.6569 12.75 15 11.4069 15 9.75C15 8.09315 13.6569 6.75 12 6.75C10.3431 6.75 9 8.09315 9 9.75C9 11.4069 10.3431 12.75 12 12.75Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M19.5 9.75C19.5 16.5 12 21.75 12 21.75C12 21.75 4.5 16.5 4.5 9.75C4.5 7.76088 5.29018 5.85322 6.6967 4.4467C8.10322 3.04018 10.0109 2.25 12 2.25C13.9891 2.25 15.8968 3.04018 17.3033 4.4467C18.7098 5.85322 19.5 7.76088 19.5 9.75Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_3606_683">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <span class="text-subsidiary-reg md:">Москва, Рябиновая улица, 45, стр. 4</span>-->
                                                    <!--                        </div>-->
                                                    <!--                      </div>-->
                                                    <!--                    </div>-->
                                                  </div>
                                                  <div>
                                                    <!--                    <h5 class="text-mob-small-medium flex items-center gap-2">-->
                                                    <!--                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                        <g clip-path="url(#clip0_4539_185058)">-->
                                                    <!--                          <path d="M6 6L18 18" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                          <path d="M8.25 18H18V8.25" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                        </g>-->
                                                    <!--                        <defs>-->
                                                    <!--                          <clipPath id="clip0_4539_185058">-->
                                                    <!--                            <rect width="24" height="24" fill="white" />-->
                                                    <!--                          </clipPath>-->
                                                    <!--                        </defs>-->
                                                    <!--                      </svg>-->
                                                    <!--                      <span>Клиентский отдел</span>-->
                                                    <!--                    </h5>-->
                                                    <!--                    <div class="border-b-stroke mt-4 flex items-center gap-2 border-b pb-2 md:gap-6">-->
                                                    <!--                      <div>-->
                                                    <!--                        <div class="flex items-center gap-2">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185156)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M14.25 4.5C15.5114 4.83218 16.6621 5.4932 17.5844 6.41557C18.5068 7.33793 19.1678 8.48858 19.5 9.75"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M13.5 7.5C15.0488 7.91438 16.0856 8.95125 16.5 10.5"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M14.6616 14.3757C14.7654 14.3066 14.8849 14.2645 15.0091 14.2532C15.1334 14.2419 15.2585 14.2618 15.3731 14.311L19.7944 16.292C19.9434 16.3557 20.0677 16.4659 20.1489 16.6062C20.23 16.7464 20.2635 16.9092 20.2444 17.0701C20.0987 18.1586 19.5627 19.1571 18.736 19.88C17.9093 20.6029 16.8482 21.0009 15.75 21.0001C12.3685 21.0001 9.12548 19.6568 6.73439 17.2657C4.3433 14.8746 3 11.6316 3 8.2501C2.99916 7.15192 3.3972 6.0908 4.12009 5.2641C4.84298 4.4374 5.84152 3.90138 6.93 3.75573C7.09091 3.73661 7.25368 3.77012 7.39395 3.85124C7.53422 3.93236 7.64444 4.05673 7.70813 4.20573L9.68906 8.63073C9.73774 8.74438 9.75756 8.8683 9.74676 8.99146C9.73596 9.11463 9.69489 9.23321 9.62719 9.33666L7.62375 11.7189C7.55269 11.8261 7.51066 11.9499 7.50179 12.0783C7.49291 12.2066 7.51749 12.335 7.57313 12.451C8.34844 14.0382 9.98906 15.6592 11.5809 16.427C11.6975 16.4824 11.8266 16.5064 11.9553 16.4967C12.084 16.487 12.208 16.4439 12.315 16.3717L14.6616 14.3757Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185156">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="tel:8 (800) 550-46-22" class="text-mob-small-bold max-md:text-nowrap"> 8 (800) 550-46-22</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="mt-2 hidden items-center gap-2 md:flex">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g>-->
                                                    <!--                              <path-->
                                                    <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="text-subsidiary-reg md:text-mob-small-reg mt-2 items-center gap-2 max-md:flex md:hidden">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185163">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->
                                                    <!--                          <span>Открыто до 20:00</span>-->
                                                    <!--                        </div>-->
                                                    <!--                      </div>-->
                                                    <!--                      <div>-->
                                                    <!--                        <div class="mt-2 flex items-center gap-2 md:hidden">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g>-->
                                                    <!--                              <path-->
                                                    <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="text-mob-small-reg items-center gap-2 max-md:hidden md:flex">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185163">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->
                                                    <!--                          <span>Открыто до 20:00</span>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="mt-2 flex items-center gap-2">-->
                                                    <!--                          <svg class="flex-shrink-0" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_3606_683)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 12.75C13.6569 12.75 15 11.4069 15 9.75C15 8.09315 13.6569 6.75 12 6.75C10.3431 6.75 9 8.09315 9 9.75C9 11.4069 10.3431 12.75 12 12.75Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M19.5 9.75C19.5 16.5 12 21.75 12 21.75C12 21.75 4.5 16.5 4.5 9.75C4.5 7.76088 5.29018 5.85322 6.6967 4.4467C8.10322 3.04018 10.0109 2.25 12 2.25C13.9891 2.25 15.8968 3.04018 17.3033 4.4467C18.7098 5.85322 19.5 7.76088 19.5 9.75Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_3606_683">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <span class="text-subsidiary-reg md:">Москва, Рябиновая улица, 45, стр. 4</span>-->
                                                    <!--                        </div>-->
                                                    <!--                      </div>-->
                                                    <!--                    </div>-->
                                                    <!--                    <h5 class="text-mob-small-medium mt-2 flex items-center gap-2">-->
                                                    <!--                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                        <g clip-path="url(#clip0_4539_185058)">-->
                                                    <!--                          <path d="M6 6L18 18" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                          <path d="M8.25 18H18V8.25" stroke="#A39874" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                        </g>-->
                                                    <!--                        <defs>-->
                                                    <!--                          <clipPath id="clip0_4539_185058">-->
                                                    <!--                            <rect width="24" height="24" fill="white" />-->
                                                    <!--                          </clipPath>-->
                                                    <!--                        </defs>-->
                                                    <!--                      </svg>-->
                                                    <!--                      <span>Юридический отдел</span>-->
                                                    <!--                    </h5>-->
                                                    <!--                    <div class="mt-4 flex items-center gap-2 pb-2 md:gap-6">-->
                                                    <!--                      <div>-->
                                                    <!--                        <div class="flex items-center gap-2">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185156)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M14.25 4.5C15.5114 4.83218 16.6621 5.4932 17.5844 6.41557C18.5068 7.33793 19.1678 8.48858 19.5 9.75"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M13.5 7.5C15.0488 7.91438 16.0856 8.95125 16.5 10.5"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M14.6616 14.3757C14.7654 14.3066 14.8849 14.2645 15.0091 14.2532C15.1334 14.2419 15.2585 14.2618 15.3731 14.311L19.7944 16.292C19.9434 16.3557 20.0677 16.4659 20.1489 16.6062C20.23 16.7464 20.2635 16.9092 20.2444 17.0701C20.0987 18.1586 19.5627 19.1571 18.736 19.88C17.9093 20.6029 16.8482 21.0009 15.75 21.0001C12.3685 21.0001 9.12548 19.6568 6.73439 17.2657C4.3433 14.8746 3 11.6316 3 8.2501C2.99916 7.15192 3.3972 6.0908 4.12009 5.2641C4.84298 4.4374 5.84152 3.90138 6.93 3.75573C7.09091 3.73661 7.25368 3.77012 7.39395 3.85124C7.53422 3.93236 7.64444 4.05673 7.70813 4.20573L9.68906 8.63073C9.73774 8.74438 9.75756 8.8683 9.74676 8.99146C9.73596 9.11463 9.69489 9.23321 9.62719 9.33666L7.62375 11.7189C7.55269 11.8261 7.51066 11.9499 7.50179 12.0783C7.49291 12.2066 7.51749 12.335 7.57313 12.451C8.34844 14.0382 9.98906 15.6592 11.5809 16.427C11.6975 16.4824 11.8266 16.5064 11.9553 16.4967C12.084 16.487 12.208 16.4439 12.315 16.3717L14.6616 14.3757Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185156">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="tel:8 (800) 550-46-22" class="text-mob-small-bold max-md:text-nowrap"> 8 (800) 550-46-22</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="mt-2 hidden items-center gap-2 md:flex">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g>-->
                                                    <!--                              <path-->
                                                    <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="text-subsidiary-reg md:text-mob-small-reg mt-2 items-center gap-2 max-md:flex md:hidden">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185163">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->
                                                    <!--                          <span>Открыто до 20:00</span>-->
                                                    <!--                        </div>-->
                                                    <!--                      </div>-->
                                                    <!--                      <div>-->
                                                    <!--                        <div class="mt-2 flex items-center gap-2 md:hidden">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g>-->
                                                    <!--                              <path-->
                                                    <!--                                d="M3 5.25H21V18C21 18.1989 20.921 18.3897 20.7803 18.5303C20.6397 18.671 20.4489 18.75 20.25 18.75H3.75C3.55109 18.75 3.36032 18.671 3.21967 18.5303C3.07902 18.3897 3 18.1989 3 18V5.25Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M21 5.25L12 13.5L3 5.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <a href="mailto:sales@primebeef.ru" class="text-subsidiary-reg md:text-mob-small-reg">sales@primebeef.ru</a>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="text-mob-small-reg items-center gap-2 max-md:hidden md:flex">-->
                                                    <!--                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_4539_185163)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path d="M12 6.75V12H17.25" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_4539_185163">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->
                                                    <!--                          <span>Открыто до 20:00</span>-->
                                                    <!--                        </div>-->
                                                    <!--                        <div class="mt-2 flex items-center gap-2">-->
                                                    <!--                          <svg class="flex-shrink-0" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                                                    <!--                            <g clip-path="url(#clip0_3606_683)">-->
                                                    <!--                              <path-->
                                                    <!--                                d="M12 12.75C13.6569 12.75 15 11.4069 15 9.75C15 8.09315 13.6569 6.75 12 6.75C10.3431 6.75 9 8.09315 9 9.75C9 11.4069 10.3431 12.75 12 12.75Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                              <path-->
                                                    <!--                                d="M19.5 9.75C19.5 16.5 12 21.75 12 21.75C12 21.75 4.5 16.5 4.5 9.75C4.5 7.76088 5.29018 5.85322 6.6967 4.4467C8.10322 3.04018 10.0109 2.25 12 2.25C13.9891 2.25 15.8968 3.04018 17.3033 4.4467C18.7098 5.85322 19.5 7.76088 19.5 9.75Z"-->
                                                    <!--                                stroke="#02283F"-->
                                                    <!--                                stroke-linecap="round"-->
                                                    <!--                                stroke-linejoin="round"-->
                                                    <!--                              />-->
                                                    <!--                            </g>-->
                                                    <!--                            <defs>-->
                                                    <!--                              <clipPath id="clip0_3606_683">-->
                                                    <!--                                <rect width="24" height="24" fill="white" />-->
                                                    <!--                              </clipPath>-->
                                                    <!--                            </defs>-->
                                                    <!--                          </svg>-->

                                                    <!--                          <span class="text-subsidiary-reg md:">Москва, Рябиновая улица, 45, стр. 4</span>-->
                                                    <!--                        </div>-->
                                                    <!--                      </div>-->
                                                    <!--                    </div>-->
                                                  </div>
                                                </div>
                                                <div class="rounded-20 bg-light-gray mt-6 flex flex-col p-3 max-md:gap-4 md:flex-row md:items-center md:justify-between md:p-4">
                                                  <div>
                                                    <h5 class="text-mob-small-bold md:text-default-bold">Связаться с нами через мессенджеры</h5>
                                                    <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">Напишите нам в любой из мессенджеров</p>
                                                  </div>
                                                  <div class="text-additional flex items-center gap-3">
                                                    <a href="https://t.me/primefoods_ru" target="_blank" rel="noopener">
                                                      <svg
                                                        class="hover:text-default transition-colors duration-300"
                                                        width="32"
                                                        height="32"
                                                        viewBox="0 0 32 32"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                      >
                                                        <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentcolor"></rect>
                                                        <g clip-path="url(#clip0_4211_122742)">
                                                          <path
                                                            d="M25.4575 6.45514C25.3413 6.3548 25.1999 6.28806 25.0486 6.26213C24.8973 6.23619 24.7417 6.25203 24.5987 6.30795L5.59937 13.7433C5.3301 13.848 5.10214 14.0373 4.9498 14.2828C4.79745 14.5283 4.72898 14.8166 4.75468 15.1043C4.78037 15.3921 4.89886 15.6637 5.09229 15.8783C5.28572 16.0929 5.54362 16.2389 5.82718 16.2942L10.75 17.2608V22.7498C10.749 23.0488 10.8379 23.3412 11.005 23.5891C11.1722 23.837 11.4099 24.029 11.6875 24.1401C11.9646 24.2533 12.2694 24.2805 12.5621 24.2181C12.8549 24.1557 13.1221 24.0067 13.3291 23.7905L15.7028 21.3286L19.4687 24.6248C19.7404 24.8658 20.0909 24.9992 20.4541 24.9998C20.6132 24.9997 20.7714 24.9747 20.9228 24.9258C21.1703 24.8472 21.3928 24.7053 21.5683 24.514C21.7438 24.3227 21.8662 24.0887 21.9231 23.8355L25.7284 7.28108C25.7625 7.13178 25.7552 6.97603 25.7075 6.83053C25.6598 6.68503 25.5734 6.55527 25.4575 6.45514ZM20.4559 23.4998L12.7047 16.703L23.8609 8.70702L20.4559 23.4998Z"
                                                            fill="currentcolor"
                                                          ></path>
                                                        </g>
                                                      </svg>
                                                    </a>
                                                    <a href="https://vk.com/primefoods_ru" target="_blank" rel="noopener">
                                                      <svg
                                                        class="hover:text-default transition-colors duration-300"
                                                        width="32"
                                                        height="32"
                                                        viewBox="0 0 32 32"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                      >
                                                        <rect x="0.5" y="0.5" width="31" height="31" rx="7.5" stroke="currentcolor"></rect>
                                                        <path
                                                          fill-rule="evenodd"
                                                          clip-rule="evenodd"
                                                          d="M15.7425 22.9413H17.177C17.177 22.9413 17.6102 22.8925 17.8317 22.6488C18.0353 22.4248 18.0288 22.0045 18.0288 22.0045C18.0288 22.0045 18.0008 20.0363 18.894 19.7465C19.7748 19.4608 20.9057 21.6486 22.1042 22.4899C23.0106 23.1264 23.6994 22.9871 23.6994 22.9871L26.9045 22.9413C26.9045 22.9413 28.5811 22.8355 27.7861 21.4876C27.721 21.3775 27.3229 20.4906 25.4029 18.6683C23.393 16.761 23.6625 17.0696 26.0833 13.7704C27.5576 11.7612 28.1469 10.5346 27.9628 10.0093C27.7873 9.50879 26.7029 9.641 26.7029 9.641L23.0942 9.66382C23.0942 9.66382 22.8265 9.62657 22.6282 9.7479C22.4342 9.86654 22.3098 10.1438 22.3098 10.1438C22.3098 10.1438 21.7384 11.6984 20.9769 13.0208C19.37 15.8108 18.7274 15.9585 18.4647 15.7849C17.8536 15.3811 18.0063 14.163 18.0063 13.2974C18.0063 10.5935 18.4074 9.46619 17.2253 9.17437C16.8331 9.07759 16.5441 9.01355 15.5409 9.0031C14.2532 8.9897 13.1635 9.00716 12.5464 9.31626C12.1359 9.52184 11.8191 9.97983 12.0122 10.0062C12.2507 10.0387 12.7908 10.1553 13.0771 10.5536C13.447 11.0682 13.4341 12.2234 13.4341 12.2234C13.4341 12.2234 13.6466 15.4063 12.9378 15.8015C12.4514 16.0727 11.7841 15.5191 10.3515 12.9879C9.6176 11.6913 9.06328 10.258 9.06328 10.258C9.06328 10.258 8.95654 9.9902 8.76589 9.84684C8.53466 9.67315 8.21157 9.6181 8.21157 9.6181L4.78225 9.641C4.78225 9.641 4.26757 9.65569 4.07843 9.8846C3.91017 10.0884 4.065 10.5093 4.065 10.5093C4.065 10.5093 6.7496 16.9317 9.78966 20.1683C12.5775 23.1361 15.7425 22.9413 15.7425 22.9413Z"
                                                          fill="currentcolor"
                                                        ></path>
                                                      </svg>
                                                    </a>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="rounded-20 overflow-hidden">
                                            <iframe
                                              src="https://yandex.ru/map-widget/v1/?um=constructor%3Afec798daa29d66d3de27fb67b54fc5a3db20c0f0d98dc109ee2ce31eb616b73b&amp;source=constructor"
                                              width="550"
                                              height="356"
                                              frameborder="0"
                                            ></iframe>
                                          </div>
                                        </div>
                                        <div class="mt-10 md:mt-20">
                                          <div class="flex flex-col justify-between gap-8 md:flex-row">
                                            <div class="max-w-80">
                                              <h4 class="text-default-bold md:text-heavy-bold">Обратная связь</h4>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg">Остались вопросы? Оставьте заявку и мы свяжемся с вами в ближайшее время</p>
                                            </div>
                                            <form id="newsletter-form" class="subscription-form">
                                              <div class="flex flex-col gap-2 md:flex-row md:justify-between md:gap-8">
                                                <div class="flex basis-1/2 flex-col gap-2">
                                                  <input
                                                    class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                    required
                                                    type="text"
                                                    placeholder="Ваше имя"
                                                  />
                                                  <div class="flex w-full flex-col gap-0.5">
                                                    <input
                                                      type="tel"
                                                      id="phone"
                                                      placeholder="+7 (000) 000-00-00"
                                                      min="11"
                                                      class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                      required
                                                    />
                                                  </div>
                                                  <input
                                                    class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                    required
                                                    type="email"
                                                    placeholder="Ваш e-mail"
                                                  />
                                                </div>
                                                <div class="basis-1/2 md:mt-2">
                                                  <span id="comm"></span>
                                                  <div class="flex flex-col items-start gap-4 md:flex-row">
                                                    <div class="mt-2 flex items-center gap-2">
                                                      <label class="relative flex cursor-pointer gap-2 md:items-center">
                                                        <input id="check" name="check" required class="peer absolute -z-10" type="checkbox" />
                                                        <span
                                                          id="checkbox-box"
                                                          class="border-default peer-checked:bg-default flex h-5 w-5 shrink-0 flex-grow justify-center rounded border bg-white transition-all duration-200 hover:border-gray-500 md:items-center"
                                                        >
                                                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="checkbox-checkmark">
                                                            <path
                                                              d="M4 12.6111L8.92308 17.5L20 6.5"
                                                              stroke="#fff"
                                                              stroke-width="2"
                                                              stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                            ></path>
                                                          </svg>
                                                        </span>
                                                        <span class="text-complimentary-reg">
                                                          Нажимая на кнопку &laquo;Отправить&raquo; я подтверждаю, что ознакомился с
                                                          <a class="underline" href="/page/privacy-policy">политикой конфиденциальности</a> и даю согласие на обработку персональных
                                                          данных
                                                        </span>
                                                      </label>
                                                    </div>
                                                    <button class="v-button v-button_dark w-full md:w-fit" type="submit">
                                                      <span class="v-button__label">Отправить</span>
                                                      <span class="v-button__arrow">
                                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                          <g clip-path="url(#clip0_3200_28568)">
                                                            <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                          </g>
                                                          <defs>
                                                            <clipPath id="clip0_3200_28568">
                                                              <rect width="20" height="20" fill="white"></rect>
                                                            </clipPath>
                                                          </defs>
                                                        </svg>
                                                      </span>
                                                    </button>
                                                  </div>
                                                </div>
                                              </div>
                                            </form>
                                          </div>
                                        </div>
                                        <div class="mt-10 flex flex-col-reverse gap-6 md:mt-20 md:flex-row md:gap-8">
                                          <div class="shrink-0">
                                            <img
                                              src="/images/contacts/contacts_faq.webp"
                                              alt="Изображение"
                                              class="rounded-20 h-[200px] w-[320px] object-cover max-md:mx-auto md:h-[420px] md:w-[481px]"
                                            />
                                          </div>
                                          <div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Как оформить заказ на сайте?
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Безопасность онлайн платежей
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div class="border-b-filling border-b">
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Правила доставки товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                            <div>
                                              <div class="accordion-header text-mob-small-medium md:text-default-medium flex cursor-pointer items-center justify-between py-3">
                                                Возврат товаров
                                                <svg
                                                  class="accordion-toggle text-additional"
                                                  width="18"
                                                  height="18"
                                                  viewBox="0 0 18 18"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <g clip-path="url(#clip0_4286_2582)">
                                                    <path d="M2.8125 9H15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.8125V15.1875" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_4286_2582">
                                                      <rect width="18" height="18" fill="white"></rect>
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </div>
                                              <div class="accordion-content text-subsidiary-reg md:text-mob-small-reg flex flex-col gap-3">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                                <p>
                                                  Все заказы обрабатываются менеджерами с 9:30 до 20:00, без выходных. В случае оформления после 20:15 Ваш заказ будет обработан на
                                                  следующий день После сборки заказа, Вам на почту придет накладная и удобная ссылка на оплату. Также мы дублируем ссылку в СМС,
                                                  отправителем указан PrimeFoods.
                                                </p>
                                                <button class="v-button v-button_outline w-fit">
                                                  <span class="v-button__label">Подробнее</span>
                                                  <span class="v-button__arrow">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                      <g clip-path="url(#clip0_3200_28568)">
                                                        <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                      </g>
                                                      <defs>
                                                        <clipPath id="clip0_3200_28568">
                                                          <rect width="20" height="20" fill="white"></rect>
                                                        </clipPath>
                                                      </defs>
                                                    </svg>
                                                  </span>
                                                </button>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="mt-10 pb-4 md:mt-20 md:pb-20">
                                          <h3 class="text-default-bold md:text-heavy-bold">Юридические реквизиты</h3>
                                          <div class="text-subsidiary-reg md:text-mob-small-reg mt-6 flex flex-col items-start max-md:gap-4 md:mt-8 md:flex-row md:justify-between">
                                            <div class="w-full flex-grow">
                                              <p>ООО "ТД Прайм Фудс"</p>
                                              <p>ОГРН: 1247700280244</p>
                                              <p>ИНН: 9714045758</p>
                                              <p>КПП: 771401001</p>
                                            </div>
                                            <div class="flex flex-col gap-4 md:flex-row md:gap-8">
                                              <div class="md:min-w-[520px]">
                                                <p>Юридический адрес: 125252, город Москва, 3-Я Песчаная ул, д. 2а </p>
                                                <p>ОКПО: 55885391</p>
                                                <p>ОКВЭД: 46.32.1</p>
                                              </div>
                                              <div class="flex-wrap">
                                                <p>
                                                  Вы можете самостоятельно оформить заказ на сайте, добавив выбранные продукты в корзину. Наш менеджер свяжется с вами и подтвердит
                                                  наличие товара, согласует с вами время и дату доставки. Обратите внимание, если в карточке товара указан средний вес, то после
                                                  сборки заказа вес может незначительно поменяться.
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>',
                'is_active' => true,
            ],
            [
                'seo_title' => 'Условия возврата и обмена',
                'seo_description' => 'Условия возврата и обмена',
                'slug' => 'return-exchange',
                'content' => '   <div>
                                      <div>
                                        <nav class="text-complimentary-reg my-2 flex w-fit items-center gap-1 rounded-full px-3 py-2 md:hidden">
                                          <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g>
                                              <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </g>
                                          </svg>
                                          <a class="text-additional" href="/page/return-exchange">Условия возврата и обмена</a>
                                        </nav>
                                      </div>
                                      <div
                                        class="rounded-20 relative mb-4 h-50 bg-[url(/images/return-exchange-bg.webp)] mask-[url(../../images/masks/for-newsletter-mobile.svg)] bg-cover bg-center bg-no-repeat mask-size-[100%_auto] mask-bottom-left mask-no-repeat px-4 pt-4 pb-4 min-[415px]:mask-[url(../../images/masks/for-newsletter.svg)] min-[415px]:mask-size-[auto_100%] md:h-65 lg:mask-size-[100%_auto] lg:px-8 lg:pt-8"
                                      >
                                        <div class="rounded-20 absolute inset-0 z-0 bg-black/45">&nbsp;</div>
                                        <div class="relative z-10 flex h-full flex-col justify-end md:justify-between md:pb-8">
                                          <nav class="bg-input text-complimentary-reg flex w-fit items-center gap-1 rounded-full px-3 py-2 max-md:hidden">
                                            <a class="hover:text-additional transition-colors duration-200 ease-in" href="/">Главная</a>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                              <g>
                                                <path d="M9 4.5L16.5 12L9 19.5" stroke="#867F7F" stroke-linecap="round" stroke-linejoin="round"></path>
                                              </g>
                                            </svg>
                                            <a class="text-additional" href="/page/return-exchange">Условия возврата и обмена</a>
                                          </nav>
                                          <h1 class="font-vast lg:text-vast-title-bold text-vast-mob-title-bold max-w-180 items-end pb-6 font-bold text-white max-md:flex">
                                            Условия возврата <br />
                                            и обмена
                                          </h1>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="mt-4 md:mt-17">
                                      <h2 class="text-default-bold md:text-heavy-bold">Общие положения возврата и обмена</h2>
                                      <div class="mt-4 flex flex-col gap-8 md:mt-8 md:flex-row">
                                        <div class="flex flex-col gap-2 md:basis-1/2">
                                          <div class="rounded-20 bg-light-gray flex flex-col p-4">
                                            <div class="bg-text w-fit rounded-full p-2 text-white md:p-4">
                                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div class="mt-4 flex-auto">
                                              <h4 class="text-mob-small-bold md:text-default-bold">Возврат или обмен возможен в присутствии курьера</h4>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                                От любого заказа Вы можете полностью отказаться при курьере до передачи вам товара, оплатив только стоимость доставки.
                                              </p>
                                            </div>
                                          </div>
                                          <div class="rounded-20 bg-light-gray flex flex-col p-4">
                                            <div class="bg-text w-fit rounded-full p-2 text-white md:p-4">
                                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div class="mt-4 flex-auto">
                                              <h4 class="text-mob-small-bold md:text-default-bold">Возврат товара ненадлежащего качества</h4>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                                возможен при условии, что товар не был в употреблении, оригинальная упаковка может быть вскрыта, но сохранена, а также сохранен
                                                документ, подтверждающий факт и условия покупки указанного товара у продавца.
                                              </p>
                                            </div>
                                          </div>
                                          <div class="rounded-20 bg-light-gray flex flex-col p-4">
                                            <div class="bg-text w-fit rounded-full p-2 text-white md:p-4">
                                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" />
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white" />
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </div>
                                            <div class="mt-4 flex-auto">
                                              <h4 class="text-mob-small-bold md:text-default-bold">Частичный возврат</h4>
                                              <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                                Для клиентов из Москвы и Московской области доступен также частичный возврат. Для клиентов из других регионов частичный возврат
                                                доступен не у всех служб доставки
                                              </p>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="md:basis-1/2">
                                          <img src="/images/return-exchange-bg.webp" alt="Стейк" class="rounded-20 h-full object-cover max-md:max-h-[240px]" />
                                        </div>
                                      </div>
                                      <div class="mt-10 md:mt-20">
                                        <h3 class="text-default-bold md:text-heavy-bold">Возврат в присутствии курьера</h3>
                                        <div class="mt-4 md:mt-8">
                                          <div class="w-full">
                                            <div class="relative hidden items-start justify-between md:flex">
                                              <div class="step relative z-20 flex w-1/4 flex-col">
                                                <div
                                                  class="circle bg-text border-filling text-default-medium z-10 flex h-14 w-14 items-center justify-center rounded-full border font-normal text-white transition-all duration-500 ease-in-out"
                                                >
                                                  01
                                                </div>
                                                <svg class="mt-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                                  <g clip-path="url(#clip0_5983_263492)">
                                                    <path d="M8 8L24 24" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M11 24H24V11" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_5983_263492">
                                                      <rect width="32" height="32" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>

                                                <div class="text-default-medium mt-4">Сообщите курьеру, что вы<br />отказываетесь от товара</div>
                                                <p class="text-mob-small-reg mt-3">Структура организации позволяет выполнять важные задачи</p>
                                              </div>

                                              <div class="step relative z-10 flex w-1/4 flex-col pl-8">
                                                <div
                                                  class="circle border-filling text-default-medium z-10 flex h-14 w-14 items-center justify-center rounded-full border bg-white transition-all duration-500 ease-in-out"
                                                >
                                                  02
                                                </div>
                                                <svg class="mt-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                                  <g clip-path="url(#clip0_5983_263492)">
                                                    <path d="M8 8L24 24" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M11 24H24V11" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_5983_263492">
                                                      <rect width="32" height="32" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                                <div class="text-default-medium mt-4">Заполните и распишитесь в<br />возвратной накладной</div>
                                                <p class="text-mob-small-reg mt-3">Структура организации позволяет выполнять важные задачи</p>
                                              </div>

                                              <div class="step relative z-10 flex w-1/4 flex-col pr-8 pl-8">
                                                <div
                                                  class="circle border-filling text-default-medium z-10 flex h-14 w-14 items-center justify-center rounded-full border bg-white transition-all duration-500 ease-in-out"
                                                >
                                                  03
                                                </div>
                                                <svg class="mt-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                                  <g clip-path="url(#clip0_5983_263492)">
                                                    <path d="M8 8L24 24" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M11 24H24V11" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_5983_263492">
                                                      <rect width="32" height="32" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                                <div class="text-default-medium mt-4">С вами свяжется наш менеджер для уточнения информации</div>
                                                <p class="text-mob-small-reg mt-3">
                                                  После получения документов на возврат с вами свяжется сотрудник компании для детализации заявки и согласования вывоза товара
                                                </p>
                                              </div>

                                              <div class="step relative z-10 flex w-1/4 flex-col">
                                                <div
                                                  class="circle border-filling text-default-medium z-10 flex h-14 w-14 items-center justify-center rounded-full border bg-white transition-all duration-500 ease-in-out"
                                                >
                                                  04
                                                </div>
                                                <svg class="mt-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                                  <g clip-path="url(#clip0_5983_263492)">
                                                    <path d="M8 8L24 24" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M11 24H24V11" stroke="#02283F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_5983_263492">
                                                      <rect width="32" height="32" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                                <div class="text-default-medium mt-4">Возврат денежных средств</div>
                                                <p class="text-subsidiary-reg text-additional mt-1">
                                                  Стоимость доставки при возврате оплачивается покупателем, за исключением случаев ошибки со стороны магазина
                                                </p>
                                                <p class="text-mob-small-reg mt-3">
                                                  После получения и проверки возвращённого товара мы вернём деньги на ваш банковский счёт или электронный кошелёк в течение 7
                                                  рабочих дней.
                                                </p>
                                              </div>
                                            </div>
                                            <div class="relative flex flex-col gap-6 md:hidden">
                                              <div class="step flex gap-4">
                                                <div
                                                  class="circle bg-text border-filling text-default-medium z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border text-white transition-all duration-500 ease-in-out"
                                                >
                                                  01
                                                </div>
                                                <div class="flex flex-col gap-2 pt-3">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                    <g clip-path="url(#clip0_6211_286814)">
                                                      <path d="M5 5L15 15" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                      <path d="M6.875 15H15V6.875" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_6211_286814">
                                                        <rect width="20" height="20" fill="white" />
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                  <h5 class="text-mob-small-medium">Сообщите курьеру, что вы отказываетесь от товара</h5>
                                                  <p class="text-subsidiary-reg">структура организации позволяет выполнять важные задания</p>
                                                </div>
                                              </div>
                                              <div class="step relative flex gap-4">
                                                <div
                                                  class="circle border-filling text-default-medium z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border bg-white transition-all duration-500 ease-in-out"
                                                >
                                                  02
                                                </div>
                                                <div class="flex flex-col gap-2 pt-3">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                    <g clip-path="url(#clip0_6211_286814)">
                                                      <path d="M5 5L15 15" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                      <path d="M6.875 15H15V6.875" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_6211_286814">
                                                        <rect width="20" height="20" fill="white" />
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                  <h5 class="text-mob-small-medium">Заполните и распишитесь в<br />возвратной накладной</h5>
                                                  <p class="text-subsidiary-reg">структура организации позволяет выполнять важные задания</p>
                                                </div>
                                              </div>
                                              <div class="step flex gap-4">
                                                <div
                                                  class="circle border-filling text-default-medium z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border bg-white transition-all duration-500 ease-in-out"
                                                >
                                                  03
                                                </div>
                                                <div class="flex flex-col gap-2 pt-3">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                    <g clip-path="url(#clip0_6211_286814)">
                                                      <path d="M5 5L15 15" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                      <path d="M6.875 15H15V6.875" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_6211_286814">
                                                        <rect width="20" height="20" fill="white" />
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                  <h5 class="text-mob-small-medium">С вами свяжется наш менеджер для уточнения информации</h5>
                                                  <p class="text-subsidiary-reg">
                                                    После получения документов на возврат с вами свяжется сотрудник компании для детализации заявки и согласования вывоза товара
                                                  </p>
                                                </div>
                                              </div>
                                              <div class="step flex gap-4">
                                                <div
                                                  class="circle border-filling text-default-medium z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border bg-white transition-all duration-500 ease-in-out"
                                                >
                                                  04
                                                </div>
                                                <div class="flex flex-col gap-2 pt-3">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                    <g clip-path="url(#clip0_6211_286814)">
                                                      <path d="M5 5L15 15" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                      <path d="M6.875 15H15V6.875" stroke="#02283F" stroke-linecap="round" stroke-linejoin="round" />
                                                    </g>
                                                    <defs>
                                                      <clipPath id="clip0_6211_286814">
                                                        <rect width="20" height="20" fill="white" />
                                                      </clipPath>
                                                    </defs>
                                                  </svg>
                                                  <h5 class="text-mob-small-medium">
                                                    Стоимость доставки при возврате оплачивается покупателем, за исключением случаев ошибки со стороны магазина
                                                  </h5>
                                                  <p class="text-subsidiary-reg">
                                                    После получения и проверки возвращённого товара мы вернём деньги на ваш банковский счёт или электронный кошелёк в течение 7
                                                    рабочих дней.
                                                  </p>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="bg-text rounded-20 mt-10 p-4 text-white md:mt-20 md:p-6">
                                      <h4 class="text-default-bold md:text-heavy-bold">Не нашли ответ? Задайте вопрос</h4>
                                      <p class="text-subsidiary-reg md:text-mob-small-reg mt-2">
                                        Мы свяжемся с вами и предоставим консультацию. Наши эксперты всегда помогут определиться с выбором
                                      </p>
                                      <form id="newsletter-form" class="mt-4 md:mt-8">
                                        <div class="flex flex-col gap-2 md:flex-row md:items-start">
                                          <div class="flex flex-col items-start gap-2 md:flex-grow md:flex-row">
                                            <input
                                              class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                              required
                                              type="text"
                                              placeholder="Ваше имя"
                                            />
                                            <div class="flex flex-col gap-0.5 w-full">
                                              <input
                                                id="phone"
                                                type="tel"
                                                placeholder="+7 (000) 000-00-00"
                                                min="11"
                                                class="rounded-20 bg-input placeholder:text-additional text-text w-full border-none p-4 outline-none"
                                                required
                                              />
                                            </div>
                                          </div>
                                          <button class="v-button v-button_light" type="submit">
                                            <span class="v-button__label">Связаться с экспертом</span>
                                            <span class="v-button__arrow">
                                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3200_28568)">
                                                  <path d="M5 15L15 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                  <path d="M6.875 5H15V13.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </g>
                                                <defs>
                                                  <clipPath id="clip0_3200_28568">
                                                    <rect width="20" height="20" fill="white"></rect>
                                                  </clipPath>
                                                </defs>
                                              </svg>
                                            </span>
                                          </button>
                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                          <label class="flex cursor-pointer gap-2 md:items-center relative">
                                            <input id="check" name="check" required class="peer absolute -z-10 top-2  " type="checkbox" />
                                            <span
                                              id="checkbox-box"
                                              class="border-default bg-white peer-checked:bg-default flex h-5 w-5 flex-grow justify-center rounded border transition-all duration-200 hover:border-gray-500 md:items-center"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="checkbox-checkmark">
                                                  <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                              </span>
                                            <span class="text-complimentary-reg">
                                                Нажимая на кнопку &laquo;Связаться с экспертом&raquo; я подтверждаю, что ознакомился с
                                                <a class="underline" href="/page/privacy-policy">политикой конфиденциальности</a> и даю согласие на обработку персональных данных
                                              </span>
                                          </label>
                                        </div>
                                      </form>
                                    </div>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
