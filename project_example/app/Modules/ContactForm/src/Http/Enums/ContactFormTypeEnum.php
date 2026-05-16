<?php

namespace App\Modules\ContactForm\src\Http\Enums;

enum ContactFormTypeEnum: string
{
    case CallForm = 'call_form';
    case QuestionForm = 'question_form';
    case Interview = 'interview';

    public function toString() : string {
        return match($this) {
            self::CallForm => 'Заявка на звонок',
            self::QuestionForm => 'Вопрос',
            self::Interview => 'Собеседование',

        };
    }

    public static function toArray(): array
    {
        return [
            self::QuestionForm->value => 'Заявка на звонок',
            self::QuestionForm->value => 'Вопрос',
            self::Interview->value => 'Собеседование',

        ];
    }
}
