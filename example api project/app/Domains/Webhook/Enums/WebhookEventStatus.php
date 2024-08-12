<?php

namespace App\Domains\Webhook\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static int SUCCESS
 * @method static int FAILED
 * @method static int NEW
 */
enum WebhookEventStatus: int
{
    use Values;
    use InvokableCases;

    case SUCCESS = 1;
    case FAILED = -1;
    case NEW = 0;
}