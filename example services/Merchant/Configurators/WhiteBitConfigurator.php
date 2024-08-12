<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class WhiteBitConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $publicKeyAttr = $this->merchant->getAttributeByCode(MerchantAttributeCode::PUBLIC_API_KEY);
        $privateKeyAttr = $this->merchant->getAttributeByCode(MerchantAttributeCode::PRIVATE_API_KEY);

        config([
            'services.whitebit.public_key' => $publicKeyAttr->pivot->value,
            'services.whitebit.private_key' => $privateKeyAttr->pivot->value,
        ]);
    }
}
