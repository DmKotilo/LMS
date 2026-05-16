<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Authorization\Services\SMSINTService;
use Authorization\Enums\CodeSendTarget;
use Authorization\Http\Requests\CodeSendPhoneFormRequest;
use Authorization\Exceptions\CodeService\CodeException;
use Authorization\Http\Requests\CodeConfirmationFormRequest;
use Authorization\Interfaces\Services\VerifyCodeServiceInterface;
use Authorization\Mail\CodeVerification;
use Authorization\Services\VerifyCodeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use User\Models\User;

class CodeVerificationController extends Controller
{
    public function __construct(
        public VerifyCodeService $codeService,
        private SMSINTService $sms,
    ) {
    }

    public function sendCodeToPhone(CodeSendPhoneFormRequest $request): \Illuminate\Http\RedirectResponse | JsonResponse
    {

        $data = $request->validated();
        $data['ip_address'] = $request->ip();
        $data['confirmation_subject'] = $data['phone'];

        $data['target'] = $request->target;
        if($data['target'] == null) $data['target'] = CodeSendTarget::register->name;

        try {
            $data['code'] = $this->codeService->make($data);
//            if(env('APP_ENV') == 'production')
//            $this->sms->send($data['confirmation_subject'], "Ваш код авторизации на сайте primefoods.ru: {$data['code']->code}");
//            Log::info('Sms was sended to: ' . $data['confirmation_subject']);

            if (config('app.env') == 'production'){
                $this->sms->send($data['confirmation_subject'], "Ваш код авторизации на сайте shop.primefoods.ru: {$data['code']->code}");
                Log::info('Sms was sended to: ' . $data['confirmation_subject']);
            }
        } catch (\Throwable $th) {
            Log::error('Code was not sent, error:' . $th->getMessage());
            return redirect()->back()->withErrors(['code' => "Ошибка при отправке кода"]);
        }

        return CodeSendTarget::get($data['target'])->redirectAfterSend(phone: $data['phone'], code: $data['code']->code);

    }

    public function confirmCode(CodeConfirmationFormRequest $request): \Illuminate\Http\RedirectResponse
    {

        $data = $request->validated();

        $data['ip'] = $request->ip();

        try {
            $true_code = $this->codeService->check(
                code: $data['code'],
                subject: $data['phone'],
                ip: $data['ip'],
            );
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'code' => $th->getMessage()
            ]);

    /*        return redirect()->back()->with(['error' => $th->getMessage()]);*/
        }

        $true_code->update(['status' => true]);
        $referralCode = $data['referral_code'] ?? null;

        return CodeSendTarget::get($true_code->target)->redirectAfterValid(phone: $data['phone'], code: $true_code->code, referralCode: $referralCode);

    }
}
