<?php

namespace App\Domains\Webhook\Services;

use App\Domains\Webhook\DataTransferObjects\CreateWebhookEventDto;
use App\Domains\Webhook\Enums\WebhookEventStatus;
use App\Domains\Webhook\Models\WebhookEvent;
use App\Domains\Webhook\Repositories\Contracts\WebhookEventRepository;
use Illuminate\Database\Eloquent\Collection;

class WebhookEventService
{
	public function __construct(
		private readonly WebhookEventRepository $webhookEventRepository,
	){
	}

    public function getById(int $id): WebhookEvent
    {
        return $this->webhookEventRepository->findOrFail($id);
    }

    public function getByWebhookIdAndDataId(int $webhookId, int $dataId): ?WebhookEvent
    {
        return WebhookEvent::where('data->id', $dataId)
            ->where('webhook_id', $webhookId)
            ->first();
    }

    public function getCollectionCanAttempts(): Collection
    {
        return WebhookEvent::canAttempts()->get();
    }

	public function create(CreateWebhookEventDto $dto): WebhookEvent
	{
		return $this->webhookEventRepository->create([
			'webhook_id' => $dto->webhookId,
			'data' => $dto->data,
		]);
	}

    public function attempt(int $id): WebhookEvent
    {
        $webhookEvent = $this->getById($id);

        return $this->webhookEventRepository->update([
            'last_attempted_at' => now(),
            'attempts' => $webhookEvent->attempts + 1,
        ], $id);
    }

    public function setStatus(int $id, WebhookEventStatus $status): WebhookEvent
    {
        return $this->webhookEventRepository->update([
            'status' => $status->value,
        ], $id);
    }
}