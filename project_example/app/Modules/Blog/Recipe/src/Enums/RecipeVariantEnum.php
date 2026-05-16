<?php

namespace  App\Modules\Blog\Recipe\src\Enums;
enum RecipeVariantEnum: string
{
    case Boil = 'Варить';
    case Fry = 'Жарить';
    case Bake = 'Запекать';
    case languish = 'Томить';
    case SousVide = 'Су-вид';
    case Grill = 'Гриль';
    case Smoker = 'Смокер';
}
