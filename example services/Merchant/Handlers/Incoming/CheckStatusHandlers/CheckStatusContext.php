<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

use App\Services\Merchant\ValueObjects\CheckStatusRequest;

class CheckStatusContext
{
    public function __construct(
        private CheckStatusState $state,
        private CheckStatusChain $chain
    ) {
        $this->transitionTo($state);
    }

    public function transitionTo(CheckStatusState $state): void
    {
        $this->state = $state;
        $this->state->setContext($this);
    }

    public function getNextState() : ?CheckStatusState
    {
        return $this->chain->nextState($this->state);
    }

    public function handle(CheckStatusRequest $checkStatusRequest): void
    {
        $this->state->handle($checkStatusRequest);
    }
}
