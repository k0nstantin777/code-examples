<?php

namespace App\Services\Merchant\Handlers\Incoming\CheckStatusHandlers;

class CheckStatusChain
{
    private array $handlers;

    public function pushHandler(CheckStatusState $state): void
    {
        $this->handlers[] = $state;
    }

    public function nextState(CheckStatusState $currentState): ?CheckStatusState
    {
        foreach ($this->handlers as $index => $handler) {
            if (get_class($currentState) !== get_class($handler)) {
                continue;
            }

            return $this->handlers[$index+1] ?? null;
        }

        return null;
    }
}
