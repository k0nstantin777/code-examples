<?php

namespace App\Domains\Handlers;

use App\Domains\Webhook\Enums\WebhookEventStatus;
use App\Domains\Webhook\Models\Webhook;

class DefaultWebhookHandler implements WebhookHandler
{
    public function handle(Webhook $webhook): void
    {

    }
}