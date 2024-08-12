<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class CardanoWeb3PaymentsConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $addressesPool = $this->merchant->getAttributeByCode(MerchantAttributeCode::ADDRESSES_POOL);

        config([
            'services.cardano_web3_payments.addresses_pool' => array_map(
                fn($item) => trim($item),
                explode(',', $addressesPool->pivot->value)
            ),
        ]);
    }
}