<?php

namespace App\Domains\Webhook\Jobs;

use App\Domains\Webhook\Services\WebhookEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebhooksCalling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
    )
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : void
    {
        $webhookEventService = app(WebhookEventService::class);
        $webhookEvents = $webhookEventService->getCollectionCanAttempts();

        foreach ($webhookEvents as $index => $webhookEvent) {
            SingleWebhookCalling::dispatch($webhookEvent)->delay($index);
        }
    }
}
