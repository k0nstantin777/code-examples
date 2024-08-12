<?php

namespace App\Services\Exchanger\ValueObjects;

use Illuminate\Support\Carbon;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExternalCustomerSession extends DataTransferObject
{
    #[MapFrom('type')]
    public string $type;

    #[MapFrom('params')]
    public array $params;

    #[MapFrom('customer_id')]
    public int $customerId;

    #[MapFrom('expired_at')]
    public Carbon $expiredAt;

    public function isExpired() : bool
    {
        return $this->expiredAt->lessThan(now());
    }
}