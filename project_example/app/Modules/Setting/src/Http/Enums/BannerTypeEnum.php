<?php

namespace App\Modules\Banner\src\Http\Enums;

enum BannerTypeEnum: string
{
    case MainSlider = 'main_slider';
    case InfoBlock = 'info_block';
    case Novelty = 'novelty';
    case ComboPacks = 'combo_packs';
    case ProductDetail = 'product_detail';
    case ProductWhiteList = 'product_white_list';
    case RecipeWhiteList = 'recipe_white_list';
    case ArticleDetail = 'article_detail';
    case PromotionPage = 'promotion_page';
    case ComboDetail = 'combo_detail';

    public function toString() : string {
        return match($this) {
            self::MainSlider => 'Главный слайдер',
            self::InfoBlock => 'Инфо блок',
            self::Novelty => 'Новинки',
            self::ComboPacks => 'Комбо наборы',
            self::ProductDetail => 'Страница продукта',
            self::ProductWhiteList => 'Избранные продукты',
            self::RecipeWhiteList => 'Избранные рецепты',
            self::ArticleDetail => 'Страница статьи',
            self::PromotionPage => 'Страница промоакций',
            self::ComboDetail => 'Страница комбо-набора',
        };
    }

    public static function toArray(): array
    {
        return [
            self::MainSlider->value => 'Главный слайдер',
            self::InfoBlock->value => 'Инфо блок',
            self::Novelty->value => 'Новинки',
            self::ComboPacks->value => 'Комбо наборы',
            self::ProductDetail->value => 'Страница продукта',
            self::ProductWhiteList->value => 'Избранные продукты',
            self::RecipeWhiteList->value => 'Избранные рецепты',
            self::ArticleDetail->value => 'Страница статьи',
            self::PromotionPage->value => 'Страница промоакций',
            self::ComboDetail->value => 'Страница комбо-набора',
        ];
    }
}
