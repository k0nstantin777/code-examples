<?php

namespace App\Domains\Webhook\Jobs;

use App\Domains\Webhook\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebhookEventsCreate implements ShouldQueue
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
        $webhookServices = app(WebhookService::class);
        $webhooks = $webhookServices->getActiveCollection();

        foreach ($webhooks as $webhook) {
            $handler = $webhook->getHandler();
            $handler->handle($webhook);
        }
    }
}
