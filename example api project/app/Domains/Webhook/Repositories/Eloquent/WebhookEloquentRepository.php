<?php

namespace App\Domains\Webhook\Repositories\Eloquent;

use App\Domains\Webhook\Models\Webhook;
use App\Domains\Webhook\Repositories\Contracts\WebhookRepository;
use App\Services\Repository\Eloquent\BaseEloquentRepository;

class WebhookEloquentRepository extends BaseEloquentRepository implements WebhookRepository
{
	protected function model(): string
	{
		return Webhook::class;
	}
}