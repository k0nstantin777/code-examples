<?php

namespace App\Domains\Webhook\Repositories\Eloquent;

use App\Domains\Webhook\Models\WebhookEvent;
use App\Domains\Webhook\Repositories\Contracts\WebhookEventRepository;
use App\Services\Repository\Eloquent\BaseEloquentRepository;

class WebhookEventEloquentRepository extends BaseEloquentRepository implements WebhookEventRepository
{
	protected function model(): string
	{
		return WebhookEvent::class;
	}
}