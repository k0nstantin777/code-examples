<?php

namespace App\Services\Merchant\Configurators;

use App\Enums\Merchant\MerchantAttributeCode;

class EasyTransferConfigurator extends BaseConfigurator
{
    public function configure(): void
    {
        $apiName = $this->merchant->getAttributeByCode(MerchantAttributeCode::API_NAME);
        $host = $this->merchant->getAttributeByCode(MerchantAttributeCode::HOST);

        config([
            'services.easytransfer.code_name' => $apiName->pivot->value,
            'services.easytransfer.api_uri' => $host->pivot->value,
        ]);
    }
}
