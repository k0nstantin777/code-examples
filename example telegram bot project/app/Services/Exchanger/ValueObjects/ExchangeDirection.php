<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangeDirection extends DataTransferObject
{
    #[MapFrom('id')]
    public int $id;

    #[MapFrom('given_currency')]
    public Currency $givenCurrency;

    #[MapFrom('received_currency')]
    public Currency $receivedCurrency;

    #[MapFrom('given_currency_rate')]
    public string $givenCurrencyRate;

    #[MapFrom('received_currency_rate')]
    public string $receivedCurrencyRate;

    #[MapFrom('given_min_sum')]
    public string $givenMinSum;

    #[MapFrom('received_min_sum')]
    public string $receivedMinSum;

    #[MapFrom('given_max_sum')]
    public string $givenMaxSum;

    #[MapFrom('received_max_sum')]
    public string $receivedMaxSum;

    #[MapFrom('commission_value')]
    public string $commissionValue;

    #[MapFrom('access')]
    public ExchangeDirectionAccessDetails $access;

    /**
     * @var ExchangeFormAttribute[]
     */
    #[MapFrom('form_attributes')]
    public array $formAttributes;
}