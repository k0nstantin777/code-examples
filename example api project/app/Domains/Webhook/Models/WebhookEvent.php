<?php

namespace App\Domains\Webhook\Models;

use App\Domains\Webhook\Enums\WebhookEventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Domains\Webhook\Models\WebhookEvent
 *
 * @property int $id
 * @property int $webhook_id
 * @property int $attempts
 * @property Carbon|null $last_attempted_at
 * @property WebhookEventStatus $status
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Webhook $webhook
 * @method static Builder|WebhookEvent canAttempts()
 * @method static Builder|WebhookEvent failed()
 * @method static Builder|WebhookEvent new()
 * @method static Builder|WebhookEvent newModelQuery()
 * @method static Builder|WebhookEvent newQuery()
 * @method static Builder|WebhookEvent query()
 * @method static Builder|WebhookEvent whereAttempts($value)
 * @method static Builder|WebhookEvent whereCreatedAt($value)
 * @method static Builder|WebhookEvent whereData($value)
 * @method static Builder|WebhookEvent whereId($value)
 * @method static Builder|WebhookEvent whereLastAttemptedAt($value)
 * @method static Builder|WebhookEvent whereStatus($value)
 * @method static Builder|WebhookEvent whereUpdatedAt($value)
 * @method static Builder|WebhookEvent whereWebhookId($value)
 * @mixin \Eloquent
 */
class WebhookEvent extends Model
{
    public const MAX_ATTEMPTS = 2;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'last_attempted_at' => 'date',
        'data' => 'array',
        'status' => WebhookEventStatus::class
    ];

    public function webhook() : BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function scopeCanAttempts(Builder $query): Builder
    {
        return $query->where('status', '!=', WebhookEventStatus::SUCCESS())
            ->where('attempts', '<', config('webhook-server.tries'));
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', WebhookEventStatus::FAILED());
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', WebhookEventStatus::NEW());
    }
}
