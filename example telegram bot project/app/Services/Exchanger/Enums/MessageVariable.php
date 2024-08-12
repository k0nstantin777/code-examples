<?php

namespace App\Services\Exchanger\Enums;

use ArchTech\Enums\InvokableCases;

/**
 *  @method static string CURRENCY_NAME()
 *  @method static string ATTRIBUTE_NAME()
 *  @method static string STATUS_NAME()
 *  @method static string COMMENT_TEXT()
 */
enum MessageVariable : string
{
    use InvokableCases;

    case CURRENCY_NAME = 'currency_name';
    case ATTRIBUTE_NAME = 'attribute_name';
    case STATUS_NAME = 'status_name';
    case COMMENT_TEXT = 'comment_text';
}