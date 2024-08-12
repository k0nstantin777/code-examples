<?php

namespace App\Domains\Webhook\DataTransferObjects;

use Spatie\LaravelData\Data;

class CreateWebhookEventDto extends Data
{
    public function __construct(
        public int $webhookId,
        public array $data = [],
    ) {
    }
}