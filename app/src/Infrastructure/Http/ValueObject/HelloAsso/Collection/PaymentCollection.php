<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Collection;

use App\Infrastructure\Http\ValueObject\HelloAsso\Entity\Order;
use App\Infrastructure\Http\ValueObject\HelloAsso\Entity\Payment;

readonly class PaymentCollection
{
    /**
     * @param Order[] $payments
     */
    public function __construct(
        private array      $payments,
    ) {
    }

    /**
     * @return Order[]
     */
    public function getOrders(int $page, int $length): array
    {
        $offset = ($page - 1) * $length;

        return array_slice($this->payments, $offset, $length);
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

        $payments = [];
        foreach ($data as $orderData) {
            $payments[] = Payment::fromHelloAssoResponse($orderData);
        }

        return new self($payments);
    }

    public function getTotalCount():int
    {
        return count($this->payments);
    }

    public function getFilteredCount(): int
    {
        return $this->getTotalCount();
    }
}
