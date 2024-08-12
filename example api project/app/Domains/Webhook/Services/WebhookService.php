<?php

namespace App\Domains\Webhook\Services;

use App\Domains\Webhook\DataTransferObjects\CreateWebhookDto;
use App\Domains\Webhook\Enums\WebhookType;
use App\Domains\Webhook\Models\Webhook;
use App\Domains\Webhook\Repositories\Contracts\WebhookRepository;
use Illuminate\Support\Collection;

class WebhookService
{
	public function __construct(
		private readonly WebhookRepository $webhookRepository,
	){
	}

    public function getActiveCollection(): Collection
    {
        return $this->webhookRepository
            ->pushCondition(['is_active', true])
            ->get();
    }

	public function create(CreateWebhookDto $dto): Webhook
	{
		return $this->webhookRepository->create([
			'user_id' => $dto->userId,
			'type' => $dto->type,
			'url' => $dto->url,
			'config' => $dto->config,
		]);
	}

    public function update(int $id, CreateWebhookDto $dto): Webhook
    {
        return $this->webhookRepository->update([
            'user_id' => $dto->userId,
            'type' => $dto->type,
            'url' => $dto->url,
            'config' => $dto->config,
        ], $id);
    }

    public function delete(int $id): int|bool
    {
        return $this->webhookRepository->delete($id);
    }

    public function getByUserAndType(int $userId, WebhookType $type): ?Webhook
    {
        return $this->webhookRepository->pushCondition(
                ['user_id', $userId]
            )->pushCondition(
                ['type', $type->value]
            )->first();
    }
}