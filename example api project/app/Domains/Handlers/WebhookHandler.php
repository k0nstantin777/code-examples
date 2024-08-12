<?php

namespace App\Domains\Handlers;

use App\Domains\Webhook\Models\Webhook;

interface WebhookHandler
{
    public function handle(Webhook $webhook): void;
}