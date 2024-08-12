<?php

namespace App\Domains\Account\Services;

use App\Domains\Account\Enums\Ability;
use App\Services\FFC\Enums\UserType;
use App\Services\FFC\ValueObjects\User;

class PersonalTokenAbilitiesService
{
    public function getByFFCUser(User $user) : array
    {
        $abilities = [];
        if ($user->getType() === UserType::ADMIN) {
            $abilities[] = Ability::ACCESS_PRIVATE_API->value;
        }

        return $abilities;
    }
}