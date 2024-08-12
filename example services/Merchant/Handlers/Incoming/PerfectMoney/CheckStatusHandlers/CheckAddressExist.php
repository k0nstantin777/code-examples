<?php

namespace App\Services\Merchant\Handlers\Incoming\PerfectMoney\CheckStatusHandlers;

use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckAddressExist as BaseCheckAddressExist;
use App\Services\PerfectMoney\Exceptions\PerfectMoneyApiException;
use App\Services\PerfectMoney\PerfectMoneyService;
use App\Services\PerfectMoney\ValueObjects\Transaction;
use App\Services\PerfectMoney\ValueObjects\Wallet;

class CheckAddressExist extends BaseCheckAddressExist
{
    public function __construct(
        private PerfectMoneyService $perfectMoneyService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        /** @var Transaction  $transaction */
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);
        $wallets = $this->getBalance();

        foreach ($wallets as $wallet) {
            /** @var Wallet $wallet */
            if ($wallet->getId() === $transaction->getPayeeAccount()) {
                return true;
            }
        }

        return false;
    }

    private function getBalance() : array
    {
        try {
            return $this->perfectMoneyService->getBalance();
        } catch (PerfectMoneyApiException) {
            return [];
        }
    }
}
