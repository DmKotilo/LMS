<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Enums\CodeSendTarget;
use Authorization\Services\SMSINTService;
use Authorization\Services\VerifyCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Inertia\Inertia;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Смена телефона')]
class ChangePhoneController extends Controller
{
    public function __construct(
        public VerifyCodeService $codeService,
        private SMSINTService $sms,
    ) {
    }
    #[Endpoint('Отправка кода подтверждения для смены телефона')]
    #[BodyParam('phone', description: 'Новый номер телефона', required: true, example: '+79001234567')]
    #[Response(description: 'Код подтверждения отправлен', content: '{"message": "Код подтверждения отправлен", "code": 1234}')]
    #[Response(description: 'Неверный пароль или телефон уже используется')]
    public function send(Request $request)
    {
        $user = $request->user();

        $formattedPhone = \App\Helpers\PhoneHelper::formatPhone($request->phone);
        $request->merge(['phone' => $formattedPhone]);

        $data = $request->validate([
//            'password' => $user && !empty($user->password)
//                ? ['required', 'string',]
//                : ['nullable', 'string',],
            'phone' => [
                'required',
                'string',
                Rule::unique('users', 'phone'),
                Rule::unique('users', 'new_phone'),
            ],
        ]);

//        if (!empty($user->password)) {
//            if (!Hash::check($request->password, $request->user()->password)) {
//                return redirect()->back()->with(['error' => 'Неверный пароль'], 422);
//            }
//        }


        $user = $request->user();
        $user->new_phone = $formattedPhone;
        try {
            $user->save();
        } catch (QueryException $exception) {
            return redirect()->back()->withErrors([
                'phone' => 'Такой номер уже существует',
            ]);
        }

        $data['ip_address'] = $request->ip();
        $data['confirmation_subject'] = $formattedPhone;

        $data['target'] = $request->target;
        if($data['target'] == null) $data['target'] = CodeSendTarget::new_phone_verify->name;

        try {
            $data['code'] = $this->codeService->make($data);
            if(config('app.env') == 'production') {
                $this->sms->send($data['confirmation_subject'], "Ваш код авторизации на сайте primefoods.ru: {$data['code']->code}");
                Log::info('Sms was sended to: ' . $data['confirmation_subject']);
            }
        } catch (\Throwable $th) {
            Log::error('Code was not sent, error:' . $th->getMessage());
            return redirect()->back()->withErrors(['error' => "Ошибка при отправке кода"]);
        }

        return CodeSendTarget::get($data['target'])->redirectAfterSend(phone: $data['phone'], code: $data['code']->code);
    }

    #[Endpoint('Подтверждение кода для смены телефона')]
    #[BodyParam('code', description: 'Код подтверждения из SMS', required: true, example: 123456)]
    #[Response(description: 'Телефон успешно изменён')]
    #[Response(description: 'Неверный код или отсутствует номер для подтверждения')]
    public function confirm(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'numeric', 'regex:/^([0-9]*)$/', 'digits:'.config('code_verification.code_length')],
        ]);
        $user = $request->user();

        if (!$user->new_phone) {
            return back()->with(['error' => 'Нет номера для подтверждения']);
        }
        $data['ip'] = $request->ip();
        $data['phone'] = $user->new_phone;

        try {
            $true_code = $this->codeService->check(
                code: $data['code'],
                subject: $data['phone'],
                ip: $data['ip'],
            );
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'error' => $th->getMessage()
            ]);

            /*        return redirect()->back()->with(['error' => $th->getMessage()]);*/
        }

        $true_code->update(['status' => true]);
        $user->phone = \App\Helpers\PhoneHelper::formatPhone($user->new_phone);
        $user->new_phone = null;
        $user->save();


        return CodeSendTarget::get($true_code->target)->redirectAfterValid(phone: $data['phone'], code: $true_code->code);

    }
}

