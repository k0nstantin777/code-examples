<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class PerfectMoneyConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $account = $this->merchant->getAttributeByCode(MerchantAttributeCode::ACCOUNT);
        $usdWallet = $this->merchant->getAttributeByCode(MerchantAttributeCode::USD_WALLET_NUMBER);
        $eurWallet = $this->merchant->getAttributeByCode(MerchantAttributeCode::EUR_WALLET_NUMBER);
        $btcWallet = $this->merchant->getAttributeByCode(MerchantAttributeCode::BTC_WALLET_NUMBER);
        $password = $this->merchant->getAttributeByCode(MerchantAttributeCode::PASSWORD);

        config([
            'services.perfect_money.account' => $account->pivot->value,
            'services.perfect_money.password' => $password->pivot->value,
            'services.perfect_money.usd_wallet' => $usdWallet->pivot->value,
            'services.perfect_money.eur_wallet' => $eurWallet->pivot->value,
            'services.perfect_money.btc_wallet' => $btcWallet->pivot->value,
        ]);
    }
}
