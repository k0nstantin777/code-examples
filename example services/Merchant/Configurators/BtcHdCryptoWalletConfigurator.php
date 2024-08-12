<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class BtcHdCryptoWalletConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $hostAttr = $this->merchant->getAttributeByCode(MerchantAttributeCode::HOST);
        $apiKeyAttr = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_KEY);
        $btcHdWalletPublicKeyAttr = $this->merchant->getAttributeByCode(MerchantAttributeCode::EXTENDED_PUBLIC_KEY);
        $btcHdWalletAddressesLimit = $this->merchant->getAttributeByCode(MerchantAttributeCode::ADDRESSES_LIMIT);

        config([
            'services.crypto_wallets.api_key' => $apiKeyAttr->pivot->value,
            'services.crypto_wallets.base_uri' => $hostAttr->pivot->value,
            'services.crypto_wallets.btc_hd_wallet.extended_public_key' => $btcHdWalletPublicKeyAttr->pivot->value,
            'services.crypto_wallets.btc_hd_wallet.addresses_limit' => $btcHdWalletAddressesLimit->pivot->value,
        ]);
    }
}
