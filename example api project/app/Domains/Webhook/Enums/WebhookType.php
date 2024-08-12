<?php

namespace App\Domains\Webhook\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string ORDER_SHIPPED
 */
enum WebhookType: string
{
    use Values;
    use InvokableCases;

    case ORDER_SHIPPED = 'order_shipped';
}