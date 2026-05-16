<?php

namespace Authorization\Services;

use Authorization\Exceptions\CodeService\CodeServiceException;
use Authorization\Interfaces\Services\VerifyCodeServiceInterface;
use Authorization\Models\VerifyCode;
use Carbon\Carbon;

class VerifyCodeService implements VerifyCodeServiceInterface
{
    public function check(string $code, string $subject, string $ip) : VerifyCode
    {
        $true_code = VerifyCode::query()->where([
            ['code', '=', $code],
            ['confirmation_subject', '=', $subject],
            ['ip_address', '=', $ip],
        ])->latest()->first();

        if (!$true_code) {
            throw new CodeServiceException('Код не найден', 400);
        }
        if (!($true_code->created_at->timestamp > Carbon::now()->subMinutes(config('code_verification.code_lifetime'))->timestamp)) {
            throw new CodeServiceException('Код устарел', 400);
        }

        $checkActualCode = VerifyCode::query()->where([
            ['confirmation_subject', '=', $subject],
            ['ip_address', '=', $ip],
        ])->latest()->first();

        if ($checkActualCode->id != $true_code->id) {
            throw new CodeServiceException('Код неактуален', 400);
        }

        return $true_code;
    }

    public function make(array $data): VerifyCode
    {
        $data['code'] = random_int(100000, 999999);

        $verifyCode = VerifyCode::query()->create($data);

        return $verifyCode;
    }
}
