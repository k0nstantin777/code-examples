<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class PayeerConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $apiId = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_ID);
        $account = $this->merchant->getAttributeByCode(MerchantAttributeCode::ACCOUNT);
        $secretKey = $this->merchant->getAttributeByCode(MerchantAttributeCode::SECRET_KEY);

        config([
            'services.payeer.api_id' => $apiId->pivot->value,
            'services.payeer.account' => $account->pivot->value,
            'services.payeer.secret_key' => $secretKey->pivot->value,
        ]);
    }
}
