<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Collection;

use App\Infrastructure\Http\ValueObject\HelloAsso\Entity\Order;
use App\Infrastructure\Http\ValueObject\HelloAsso\Entity\Pagination;

class OrderCollection
{
    /**
     * @param Order[] $orders
     * @param Pagination $pagination
     */
    public function __construct(
        private readonly array $orders,
        private readonly Pagination $pagination
    ) {
    }

    /**
     * @return Order[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public static function fromHelloAssoResponse(array $response): self
    {
        $orders = [];
        foreach ($response['data'] as $orderData) {
            $orders[] = Order::fromHelloAssoResponse($orderData);
        }
        $pagination = Pagination::fromHelloAssoResponse($response['pagination']);

        return new self($orders, $pagination);
    }
}
