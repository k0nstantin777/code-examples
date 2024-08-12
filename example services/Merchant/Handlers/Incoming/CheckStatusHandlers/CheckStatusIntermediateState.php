<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckStatusIntermediateState extends CheckStatusState
{
    protected function success(CheckStatusRequest $checkStatusRequest): void
    {
        $nextState = $this->context->getNextState();
        if (null === $nextState) {
            return;
        }

        $this->context->transitionTo($nextState);
        $this->context->handle($checkStatusRequest);
    }
}
