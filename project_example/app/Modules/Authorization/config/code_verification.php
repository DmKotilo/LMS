<?php

return [

    'status' => env('CODE_VERIFICATION_STATUS', true),
    'code_length' => env('CODE_VERIFICATION_LENGTH', 6),
    'code_lifetime' => env('CODE_VERIFICATION_LIFETIME_MINUTES', 10)

];
