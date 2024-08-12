<?php

namespace App\Domains\Webhook\DataTransferObjects;

use App\Domains\Webhook\Enums\WebhookType;
use Spatie\LaravelData\Data;

class CreateWebhookDto extends Data
{
    public function __construct(
        public int $userId,
        public WebhookType $type,
        public string $url,
        public array $config = [],
    ) {
    }
}