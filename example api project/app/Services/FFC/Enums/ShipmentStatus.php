<?php

namespace App\Services\FFC\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string AWAITING_SHIPMENT
 * @method static string SHIPPED
 * @method static string CANCELLED
 */
enum ShipmentStatus : string
{
    use Values;
    use InvokableCases;

    case AWAITING_SHIPMENT = 'awaiting_shipment';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';
}
