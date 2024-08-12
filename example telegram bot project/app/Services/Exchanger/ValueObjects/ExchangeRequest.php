<?php

namespace App\Services\Exchanger\ValueObjects;

use App\Services\Exchanger\Enums\ExchangeRequestStatus;
use Illuminate\Support\Carbon;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangeRequest extends DataTransferObject
{
    #[MapFrom('id')]
    public string $id;

    #[MapFrom('formatted_token')]
    public string $formattedToken;

    #[MapFrom('status_string')]
    public string $statusString;

    #[MapFrom('status')]
    public ExchangeRequestStatus $status;

    #[MapFrom('created_date_string')]
    public string $createdDateString;

    #[MapFrom('created_at')]
    public Carbon $createdAt;

    #[MapFrom('given_currency_rate')]
    public string $givenCurrencyRate;

    #[MapFrom('given_sum')]
    public string $givenSum;

    #[MapFrom('received_sum')]
    public string $receivedSum;

    #[MapFrom('received_currency_rate')]
    public string $receivedCurrencyRate;

    #[MapFrom('is_expired')]
    public bool $isExpired;

    #[MapFrom('expired_at')]
    public ?Carbon $expiredAt;

    #[MapFrom('show_link')]
    public string $showLink;

    #[MapFrom('payment_address')]
    public ?string $paymentAddress;

    #[MapFrom('given_currency')]
    public Currency $givenCurrency;

    #[MapFrom('received_currency')]
    public Currency $receivedCurrency;

    /**
     * @var ExchangeFormAttribute[]
     */
    #[MapFrom('attributes')]
    public array $attributes;
}