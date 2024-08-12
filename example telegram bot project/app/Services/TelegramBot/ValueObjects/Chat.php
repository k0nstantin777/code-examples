<?php

namespace App\Services\TelegramBot\ValueObjects;

use App\Domains\User\Models\User;
use App\Services\TelegramBot\Flows\ExchangeRequest\ExchangeRequestProcessingFlow;
use App\Services\TelegramBot\Flows\Flow;

class Chat
{
    private Flow $flow;

    private const DEFAULT_FLOW = ExchangeRequestProcessingFlow::class;

    public function __construct(
        private readonly User $user,
    ) {
        $this->changeFlow(app(self::DEFAULT_FLOW));
    }

    public function changeFlow(Flow $flow) : void
    {
        $this->flow = $flow;
        $this->flow->setChat($this);
    }

    public function getFlow() : Flow
    {
        return $this->flow;
    }

    public function getUser() : User
    {
        return $this->user;
    }
}
