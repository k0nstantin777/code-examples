<?php

namespace App\Services\Merchant\Handlers\Incoming\AdvancedCash\CheckStatusHandlers;

use App\Services\AdvancedCash\AdvancedCashService;
use App\Services\AdvancedCash\Exceptions\AdvancedCashApiException;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckStatusRequestProperty;
use App\Services\Merchant\ValueObjects\CheckStatusRequest;
use App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers\CheckAddressExist as BaseCheckAddressExist;

class CheckAddressExist extends BaseCheckAddressExist
{
    public function __construct(
        private AdvancedCashService $advancedCashService
    ) {
    }

    public function check(CheckStatusRequest $checkStatusRequest): bool
    {
        $transaction = $checkStatusRequest->get(CheckStatusRequestProperty::TRANSACTION);
        $balances = $this->getBalances();

        foreach ($balances as $wallet) {
            if ($wallet->id === $transaction->walletDestId) {
                return true;
            }
        }

        return false;
    }

    private function getBalances() : array
    {
        try {
            return $this->advancedCashService->getBalances()->return;
        } catch (AdvancedCashApiException) {
            return [];
        }
    }
}
