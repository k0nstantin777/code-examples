<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class CoinPaymentsConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $publicKeyAttribute = $this->merchant->getAttributeByCode(MerchantAttributeCode::PUBLIC_API_KEY);
        $privateKeyAttribute = $this->merchant->getAttributeByCode(MerchantAttributeCode::PRIVATE_API_KEY);

        config([
            'services.coinpayments.public_key' => $publicKeyAttribute->pivot->value,
            'services.coinpayments.private_key' => $privateKeyAttribute->pivot->value,
        ]);
    }
}
