<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Entity;

class Pagination
{
    public static function fromHelloAssoResponse(array $pagination): self
    {
        return new Pagination();
    }
}