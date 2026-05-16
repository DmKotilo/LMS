<?php

namespace ContactForm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContactForm\src\Http\Enums\ContactFormStatusEnum;
use App\Modules\ContactForm\src\Http\Enums\ContactFormTypeEnum;
use App\Notifications\NewCallContactFormNotification;
use App\Services\Bitrix24\ContactForms\ContactLeadSyncService;
use App\Services\Bitrix24\ContactForms\TeamApplicationSyncService;
use App\Support\UtmHelper;
use ContactForm\Models\CallContactForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Формы обратной связи')]
class CallContactFormController extends Controller
{
    #[BodyParam('name', description: 'имя пользователя', required: true, example: 'Иван Иванов')]
    #[BodyParam('phone', description: 'телефон пользователя', required: true, example: '+79991234567')]
    #[BodyParam('email', description: 'Почта', required: false, example: '123@mail.com')]
    #[BodyParam('comment', description: 'необязательный комментарий max:255', required: false, example: 'Здравствуйте, интересует вопрос>.....')]
    #[BodyParam('time_interval', description: 'интервал времени для звонка', required: false, example: '18:00-20:00')]
    #[BodyParam('type', description: 'тип формы', required: true, example: 'call_form(для заказа звонка)/question_form(для обычной формы)/interview(Запись на собеседование)',)]
    #[Response( description: 'успешный ответ', content: '{"success": true, "message": "Форма успешно отправлена!"}')]
    public function store(
        Request $request,
        ContactLeadSyncService $contactLeadSyncService,
        TeamApplicationSyncService $teamApplicationSyncService
    )
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'comment' => 'nullable|string|max:255',
            'time_interval' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'post' => 'nullable|string|max:255',
            'type' => 'required|in:call_form,question_form,interview',
        ]);
        $data['phone'] = \App\Helpers\PhoneHelper::formatPhone($data['phone']);
        $data['status'] = ContactFormStatusEnum::New->value;
        $form = CallContactForm::create($data);
        $utm = UtmHelper::extract($request);
        foreach ($utm as $key => $value) {
            $form->setAttribute($key, $value);
        }

        if ($form->type === ContactFormTypeEnum::Interview) {
            $teamApplicationSyncService->sync($form);
        } else {
            $contactLeadSyncService->sync($form);
        }
       // Notification::route('mail', 'dmkotilo@gmail.com')->notify(new NewCallContactFormNotification($form));//todo сменить почту на почту праймфудса

        return redirect()->back()->with('success', 'Форма успешно отправлена!');
    }
}
