<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class BlockIoConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $bitcoinKey = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_KEY_BITCOIN_NET);
        $dogecoinKey = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_KEY_DOGECOIN_NET);
        $litecoinKey = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_KEY_LITECOIN_NET);
        $pin = $this->merchant->getAttributeByCode(MerchantAttributeCode::SECRET_PIN);

        config([
            'services.blockio.bitcoin_key' => $bitcoinKey->pivot->value,
            'services.blockio.dogecoin_key' => $dogecoinKey->pivot->value,
            'services.blockio.litecoin_key' => $litecoinKey->pivot->value,
            'services.blockio.secret_pin' => $pin->pivot->value,
        ]);
    }
}
