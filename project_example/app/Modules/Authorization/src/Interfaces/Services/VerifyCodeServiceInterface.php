<?php

namespace Authorization\Interfaces\Services;

use Authorization\Models\VerifyCode;

interface VerifyCodeServiceInterface
{
    public function check(string $code, string $subject, string $ip) : VerifyCode;

    public function make(array $data): VerifyCode;
}
