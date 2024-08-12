<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Services\Merchant\ValueObjects\CheckStatusRequest;

abstract class CheckStatusState
{
    protected CheckStatusContext $context;

    public function setContext(CheckStatusContext $context): void
    {
        $this->context = $context;
    }

    public function handle(CheckStatusRequest $checkStatusRequest): void
    {
        if ($this->check($checkStatusRequest)) {
            $this->success($checkStatusRequest);
            return;
        }

        $this->error($checkStatusRequest);
    }

    abstract protected function check(CheckStatusRequest $checkStatusRequest): bool;
    abstract protected function success(CheckStatusRequest $checkStatusRequest): void;
    abstract protected function error(CheckStatusRequest $checkStatusRequest): void;
}
