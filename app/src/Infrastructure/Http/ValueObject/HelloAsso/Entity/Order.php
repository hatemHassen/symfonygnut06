<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Entity;

class Order
{
    public static function fromHelloAssoResponse(array $orderData): self
    {
        return new Order();
    }
}