<?php

namespace App\Infrastructure\Http\ValueObject\HelloAsso\Entity;

use DateTimeImmutable;
use JsonSerializable;

class Order implements JsonSerializable
{
    /**
     * @var array{
     *     id: int,
     *     date: string,
     *     formSlug: string,
     *     formType: string,
     *     organizationName: string,
     *     organizationSlug: string,
     *     organizationType: string,
     *     organizationIsUnderColucheLaw: bool,
     *     formName: string,
     *     meta: array{createdAt: string, updatedAt: string},
     *     isAnonymous: bool,
     *     isAmountHidden: bool
     * }
     */
    public readonly array $orderData;

    public readonly int $id;
    public readonly DateTimeImmutable $date;
    public readonly string $formSlug;
    public readonly string $formType;
    public readonly string $organizationName;
    public readonly string $organizationSlug;
    public readonly string $organizationType;
    public readonly bool $organizationIsUnderColucheLaw;
    public readonly string $formName;
    public readonly array $meta;
    public readonly bool $isAnonymous;
    public readonly bool $isAmountHidden;

    /**
     * @var array{email: string, country: string, firstName: string, lastName: string}
     */
    public readonly array $payer;

    public readonly string $payerEmail;
    public readonly string $payerCountry;
    public readonly string $payerFirstName;
    public readonly string $payerLastName;

    public readonly string $name;

    /**
     * @var array{firstName: string, lastName: string}
     */
    public readonly array $user;

    public readonly string $userFirstName;
    public readonly string $userLastName;

    public readonly string $priceCategory;
    public readonly string $ticketUrl;
    public readonly string $qrCode;
    public readonly string $tierDescription;
    public readonly int $tierId;
    public readonly int $amount;
    public readonly string $type;
    public readonly int $initialAmount;
    public readonly string $state;

    private function __construct(
        int $id,
        DateTimeImmutable $date,
        string $formSlug,
        string $formType,
        string $organizationName,
        string $organizationSlug,
        string $organizationType,
        bool $organizationIsUnderColucheLaw,
        string $formName,
        array $meta,
        bool $isAnonymous,
        bool $isAmountHidden,
        array $payer,
        string $name,
        array $user,
        string $priceCategory,
        string $qrCode,
        string $tierDescription,
        int $tierId,
        int $amount,
        string $type,
        int $initialAmount,
        string $state,
        array $orderData,
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->formSlug = $formSlug;
        $this->formType = $formType;
        $this->organizationName = $organizationName;
        $this->organizationSlug = $organizationSlug;
        $this->organizationType = $organizationType;
        $this->organizationIsUnderColucheLaw = $organizationIsUnderColucheLaw;
        $this->formName = $formName;
        $this->meta = $meta;
        $this->isAnonymous = $isAnonymous;
        $this->isAmountHidden = $isAmountHidden;
        $this->payer = $payer;
        $this->payerEmail = $payer['email'];
        $this->payerCountry = $payer['country'];
        $this->payerFirstName = $payer['firstName'];
        $this->payerLastName = $payer['lastName'];
        $this->name = $name;
        $this->user = $user;
        $this->userFirstName = $user['firstName'];
        $this->userLastName = $user['lastName'];
        $this->priceCategory = $priceCategory;
        $this->qrCode = $qrCode;
        $this->tierDescription = $tierDescription;
        $this->tierId = $tierId;
        $this->amount = $amount;
        $this->type = $type;
        $this->initialAmount = $initialAmount;
        $this->state = $state;
        $this->orderData = $orderData;
    }

    public static function fromHelloAssoResponse(array $orderData): self
    {
        return new self(
            id: (int)$orderData['order']['id'],
            date: new DateTimeImmutable($orderData['order']['date']),
            formSlug: (string)$orderData['order']['formSlug'],
            formType: (string)$orderData['order']['formType'],
            organizationName: (string)$orderData['order']['organizationName'],
            organizationSlug: (string)$orderData['order']['organizationSlug'],
            organizationType: (string)$orderData['order']['organizationType'],
            organizationIsUnderColucheLaw: (bool)$orderData['order']['organizationIsUnderColucheLaw'],
            formName: (string)$orderData['order']['formName'],
            meta: $orderData['order']['meta'] ?? [],
            isAnonymous: (bool)$orderData['order']['isAnonymous'],
            isAmountHidden: (bool)$orderData['order']['isAmountHidden'],
            payer: $orderData['payer'] ?? [],
            name: (string)$orderData['name'],
            user: $orderData['user'] ?? [],
            priceCategory: (string)$orderData['priceCategory'] ?? '',
            qrCode: (string)$orderData['qrCode'],
            tierDescription: (string)$orderData['tierDescription'],
            tierId: (int)$orderData['tierId'],
            amount: (int)$orderData['amount'],
            type: (string)$orderData['type'],
            initialAmount: (int)$orderData['initialAmount'],
            state: (string)$orderData['state'],
            orderData: $orderData,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'orderId' => $this->id,
            'orderName' => $this->formSlug,
            'name' => $this->name,
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
}