<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Collection;

use App\Infrastructure\Http\ValueObject\HelloAsso\Entity\Order;

readonly class OrderCollection
{
    /**
     * @param Order[] $orders
     */
    public function __construct(
        private array      $orders,
    ) {
    }

    /**
     * @return Order[]
     */
    public function getOrders(int $page, int $length): array
    {
        $offset = ($page - 1) * $length;

        return array_slice($this->orders, $offset, $length);
    }

    /**
     * @param array< array{
     *            order: array{
     *                     id:string,
     *                     date:string,
     *                     formSlug:string,
     *                     formType:string,
     *                     organizationName:string,
     *                     organizationSlug:string,
     *                         organizationType:string,
     *                     organizationIsUnderColucheLaw:bool,
     *                     formName:string,
     *                     isAnonymous:bool,
     *                     isAmountHidden:bool,
     *                     meta:array{
     *                         createdAt: string,
     *                         updatedAt: string,
     *                     }
     *         },
     *        payer: array{
     *            firstName: string,
     *            lastName: string,
     *            email: string,
     *            country: string,
     *        },
     *        name: string,
     *        user: array{
     *            firstName: string,
     *            lastName: string,
     *        },
     *        priceCategory: string,
     *        ticketUrl: string,
     *        qrCode: string,
     *        tierDescription: string,
     *        tierId: string,
     *        id: string,
     *        amount: float,
     *        type: string,
     *        initialAmount: float,
     *        state: string,
     *        }> $data
     * @return self
     */

    public static function fromHelloAssoResponse(array $data): self
    {
        if(empty($data)) {
            return new self([]);
        }

        $orders = [];
        foreach ($data as $orderData) {
            $orders[] = Order::fromHelloAssoResponse($orderData);
        }

        return new self($orders);
    }

    public function getTotalCount():int
    {
        return count($this->orders);
    }

    public function getFilteredCount(): int
    {
        return $this->getTotalCount();
    }
}
