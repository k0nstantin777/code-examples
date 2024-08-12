<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class AdvancedCashConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $apiName = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_NAME);
        $accountEmail = $this->merchant->getAttributeByCode(MerchantAttributeCode::EMAIL);
        $secretKey = $this->merchant->getAttributeByCode(MerchantAttributeCode::SECRET_KEY);

        config([
            'services.advanced_cash.api_name' => $apiName->pivot->value,
            'services.advanced_cash.email' => $accountEmail->pivot->value,
            'services.advanced_cash.secret_key' => $secretKey->pivot->value,
        ]);
    }
}
