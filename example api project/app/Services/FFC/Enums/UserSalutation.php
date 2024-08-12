<?php

namespace App\Services\FFC\Enums;

use ArchTech\Enums\Values;

enum UserSalutation : string
{
    use Values;

    case MR = 'mr';
    case MISS = 'miss';
    case COMPANY = 'company';
    case MRS = 'mrs';
}
