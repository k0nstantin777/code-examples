<?php

namespace App\Domains\Webhook\Listeners;

use App\Domains\Webhook\Enums\WebhookEventStatus;
use App\Domains\Webhook\Services\WebhookEventService;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;

class FinalFailedHandler
{
    public function handle(FinalWebhookCallFailedEvent $event): void
    {
        Log::channel('webhook')
            ->error($event->errorMessage, [
                'url' => $event->webhookUrl,
                'tag' => $event->tags[0],
            ]);

        $webhookEventService = app(WebhookEventService::class);
        $tags = $event->tags;

        $id = preg_replace('/(webhook_event:)([0-9]+)/', '$2', $tags[0]);

        if (!$id) {
            return;
        }

        $webhookEventService->setStatus($id, WebhookEventStatus::FAILED);
    }
}
