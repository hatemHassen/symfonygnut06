<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Entity;

use DateTimeImmutable;
use JsonSerializable;

readonly class Payment implements JsonSerializable
{
    /**
     * @param array{
     *     id: string,
     *     firstName: string,
     *     lastName: string,
     *     email: string,
     *     address?: string,
     *     city?: string,
     *     zipCode?: string,
     *     country?: string,
     *     company?: string
     * } $payer
     * @param array{
     *     id: int,
     *     date: string,
     *     formSlug: string,
     *     formType: string,
     *     organizationName: string,
     *     organizationSlug: string,
     *     organizationType: string,
     *     organizationIsUnderColucheLaw: bool,
     *     formName: string,
     *     meta?: array{createdAt: string, updatedAt: string},
     *     isAnonymous: bool,
     *     isAmountHidden: bool
     * } $order
     * @param array<array{
     *     id: int,
     *     amount: int,
     *     type: string,
     *     state: string,
     *     shareAmount?: int,
     *     shareItemAmount?: int
     * }> $items
     * @param array{
     *     createdAt: string,
     *     updatedAt: string
     * } $meta
     */
    public function __construct(
        private string                $id,
        private string                $paymentId,
        private DateTimeImmutable     $date,
        private string                $formSlug,
        private string                $formName,
        private int                   $amount,
        private string                $paymentMeans,
        private string                $state,
        private string                $cashOutState,
        private array                 $payer,
        private array                 $order,
        private array                 $items,
        private array                 $meta,
        private ?string               $paymentReceiptUrl = null,
        private ?int                  $installmentNumber = null,
    ) {
    }

    /**
     * @param array{
     *     order: array{
     *         id: int,
     *         date: string,
     *         formSlug: string,
     *         formType: string,
     *         organizationName: string,
     *         organizationSlug: string,
     *         organizationType: string,
     *         organizationIsUnderColucheLaw: bool,
     *         formName: string,
     *         meta?: array{createdAt: string, updatedAt: string},
     *         isAnonymous: bool,
     *         isAmountHidden: bool
     *     },
     *     payer: array{
     *         email: string,
     *         address?: string,
     *         city?: string,
     *         zipCode?: string,
     *         country?: string,
     *         company?: string,
     *         firstName: string,
     *         lastName: string
     *     },
     *     items: array<array{
     *         shareAmount?: int,
     *         shareItemAmount?: int,
     *         id: int,
     *         amount: int,
     *         type: string,
     *         state: string
     *     }>,
     *     cashOutState: string,
     *     paymentReceiptUrl?: string,
     *     id: int,
     *     amount: int,
     *     date: string,
     *     paymentMeans: string,
     *     installmentNumber?: int,
     *     state: string,
     *     meta?: array{createdAt: string, updatedAt: string},
     *     refundOperations?: array
     * } $data
     */
    public static function fromHelloAssoResponse(array $data): self
    {
        return new self(
            id: (string)$data['order']['id'],
            paymentId: (string)$data['id'],
            date: new DateTimeImmutable($data['date']),
            formSlug: $data['order']['formSlug'],
            formName: $data['order']['formName'],
            amount: $data['amount'],
            paymentMeans: $data['paymentMeans'],
            state: $data['state'],
            cashOutState: $data['cashOutState'],
            payer: $data['payer'],
            order: $data['order'],
            items: $data['items'],
            meta: $data['meta'] ?? [],
            paymentReceiptUrl: $data['paymentReceiptUrl'] ?? null,
            installmentNumber: $data['installmentNumber'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'orderId' => $this->id,
            'name' => sprintf('%s %s',$this->payer['lastName'],$this->payer['firstName']),
            'orderName' => $this->formName,
            'date' => $this->date->format('Y-m-d H:i:s'),
            'price' => $this->formatPrice($this->amount),
        ];
    }
    private function formatPrice($number): string
    {
        // Convertir les centimes en euros avec une séparation pour les décimales
        $priceInEuros = $number / 100;

        // Formater le nombre pour séparer les milliers et retirer le séparateur décimal
        $formattedPrice = number_format($priceInEuros, 2, ',', ' ');

        // Remplacer la virgule par l'euro et ajuster pour le format voulu
        // Ici, on prend la partie entière, on ajoute '€' et on append les deux derniers chiffres
        return substr($formattedPrice, 0, -3) . '€' . substr($formattedPrice, -2);
    }
    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getFormSlug(): string
    {
        return $this->formSlug;
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPaymentMeans(): string
    {
        return $this->paymentMeans;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getCashOutState(): string
    {
        return $this->cashOutState;
    }

    public function getPayer(): array
    {
        return $this->payer;
    }

    public function getOrder(): array
    {
        return $this->order;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getPaymentReceiptUrl(): ?string
    {
        return $this->paymentReceiptUrl;
    }

    public function getInstallmentNumber(): ?int
    {
        return $this->installmentNumber;
    }
}