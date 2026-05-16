<?php

function preg_phone($phone): array|string|null
{
    $phone = preg_replace("/^([78])|\D/", '', $phone);
    if (strlen($phone) == 11) $phone = preg_replace("/^[78]/", '',$phone);
    return $phone;
}

