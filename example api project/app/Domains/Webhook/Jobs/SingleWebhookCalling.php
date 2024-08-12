<?php

namespace App\Domains\Webhook\Jobs;

use App\Domains\Webhook\Models\WebhookEvent;
use App\Domains\Webhook\Services\WebhookEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\WebhookCall;

class SingleWebhookCalling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly WebhookEvent $webhookEvent,
    )
    {
    }

    public function handle() : void
    {
        $webhookEventService = app(WebhookEventService::class);
        $webhook = $this->webhookEvent->webhook;
        $webhookEventService->attempt($this->webhookEvent->id);
        $tag = 'webhook_event:' . $this->webhookEvent->id;
        $payload = $this->webhookEvent->data;
        $url = $webhook->url;

        Log::channel('webhook')
            ->debug('Calling webhook', [
                'url' => $url,
                'tag' => $tag,
            ]);

        WebhookCall::create()
            ->url($url)
            ->payload($payload)
            ->doNotSign()
            ->withTags([$tag])
            ->dispatchSync();
    }
}
