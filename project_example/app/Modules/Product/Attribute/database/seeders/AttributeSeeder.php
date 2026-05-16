<?php

namespace Attribute\Database\Seeders;
use Attribute\Enums\InputTypeEnum;
use Attribute\Models\Attribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    public function run()
    {

        Attribute::firstOrCreate(['name' => "Мраморность"], [
            'slug' => Str::slug("Мраморность мяса"),
            'input_type' => InputTypeEnum::select,
            'options' => ['Choise', 'Top Choice', 'Prime'],
            'is_default' => true,
        ]);

        Attribute::firstOrCreate(['name' => "Тип упаковки"], [
            'slug' => Str::slug("Тип упаковки"),
            'input_type' => InputTypeEnum::select,
            'options' => ['Вакумная', 'В газовой среде', 'Лотки с плёнкой','Гибкая упаковка'],
            'is_default' => true,
        ]);

        Attribute::firstOrCreate(['name' => "Базовая единица"], [
            'slug' => Str::slug("Базовая единица"),
            'input_type' => InputTypeEnum::select,
            'options' => ['кг', 'г'],
            'is_default' => true,
        ]);
        Attribute::firstOrCreate(['name' => "Откорм"], [
            'slug' => Str::slug("Откорм"),
            'input_type' => InputTypeEnum::select,
            'options' => ['Кукурузный', 'Пшеничный','Вегетарианский'],
            'is_default' => true,
        ]);
        Attribute::firstOrCreate(['name' => "Вес упаковки"], [
            'slug' => Str::slug("Вес упаковки"),
            'input_type' => InputTypeEnum::number,
        ]);
        Attribute::firstOrCreate(['name' => "Средний вес короба"], [
            'slug' => Str::slug("Средний вес короба"),
            'input_type' => InputTypeEnum::number,
        ]);

//        Attribute::firstOrCreate(['name' => "Степень охлаждения"], [
//            'slug' => Str::slug("Степень охлаждение"),
//            'input_type' => InputTypeEnum::select,
//            'options' => ['охлажденное', 'замороженное'],
//            'is_default' => true,
//        ]);

//        Attribute::firstOrCreate(['name' => "Характеристика-чекбокс"], [
//            'slug' => Str::slug("Характеристика-чекбокс"),
//            'input_type' => InputTypeEnum::checkbox,
//        ]);
//

//
//        Attribute::firstOrCreate(['name' => "Характеристика-текст обязательная"], [
//            'slug' => Str::slug("Характеристика-текст обязательная"),
//            'input_type' => InputTypeEnum::text,
//            'is_required' => true,
//        ]);
//
//        Attribute::firstOrCreate(['name' => "Характеристика-текст неактивная"], [
//            'slug' => Str::slug("Характеристика-текст неактивная"),
//            'input_type' => InputTypeEnum::text,
//            'is_active' => false,
//        ]);
//
//        Attribute::firstOrCreate(['name' => "Характеристика-выбор_из_списка несколько"], [
//            'slug' => Str::slug("Характеристика-выбор_из_списка несколько"),
//            'input_type' => InputTypeEnum::select,
//            'options' => ['значение 1', 'значение 2', 'значение 3', 'значение 4'],
//            'is_many_checked_options' => true,
//
//        ]);

        //TODO choices.js на муншайне не позволяет вводить пользователям кастомные значения в селект
//        Attribute::firstOrCreate(['name' => "Характеристика-выбор_из_списка с вводом своих значений TODO библиотека не работет"], [
//            'slug' => Str::slug("Характеристика-выбор_из_списка с вводом своих значений"),
//            'input_type' => InputTypeEnum::select,
//            'options' => ['значение 1', 'значение 2', 'значение 3', 'значение 4'],
//            'is_many_checked_options' => true,
//            'is_select_writable' => true,
//        ]);

/*        for ($i = 1; $i <= 10; $i++) {
            Attribute::create([
                'name' => "Атрибут $i",
            ]);
        }*/
    }
}
