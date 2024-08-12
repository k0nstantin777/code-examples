<?php

namespace App\Services\FFC\Enums;

enum UserType : string
{
    case ADMIN = 'admin';
    case WHOLESALER = 'wholesaler';
    case CONSUMER = 'consumer';
}
