<?php

namespace App\Interfaces\Interfaces;

interface CartItemPriceable
{
    public function getCartPrice(): float;
}
