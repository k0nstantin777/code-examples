<?php

namespace App\Domains\Exchanger\DataTransferObjects;

use Illuminate\Support\Carbon;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class ExchangerSessionCreateDto extends DataTransferObject
{
    #[MapFrom('user_id')]
    public int $userId;

    #[MapFrom('exchanger_user_id')]
    public int $exchangerUserId;

    #[MapFrom('session_updated_at')]
    public Carbon $sessionUpdatedAt;
}