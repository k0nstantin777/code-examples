<?php

namespace App\Services\Merchant\Configurators;

use App\Models\Exchange\Merchant;

abstract class BaseConfigurator implements MerchantConfigurator
{
    public function __construct(
        protected Merchant $merchant,
    ) {
    }

    abstract public function configure() : void;
}
