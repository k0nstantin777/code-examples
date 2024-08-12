<?php

namespace App\Domains\Webhook\Models;

use App\Domains\Account\Models\ApiUser;
use App\Domains\Handlers\DefaultWebhookHandler;
use App\Domains\Handlers\OrderShippedWebhookHandler;
use App\Domains\Handlers\WebhookHandler;
use App\Domains\Webhook\Enums\WebhookType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Domains\Webhook\Models\Webhook
 *
 * @property int $id
 * @property int $user_id
 * @property string $url
 * @property array $config
 * @property WebhookType $type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ApiUser $user
 * @property-read Collection<int, WebhookEvent> $webhookEvents
 * @property-read int|null $webhook_events_count
 * @method static Builder|Webhook newModelQuery()
 * @method static Builder|Webhook newQuery()
 * @method static Builder|Webhook query()
 * @method static Builder|Webhook whereConfig($value)
 * @method static Builder|Webhook whereCreatedAt($value)
 * @method static Builder|Webhook whereId($value)
 * @method static Builder|Webhook whereIsActive($value)
 * @method static Builder|Webhook whereType($value)
 * @method static Builder|Webhook whereUpdatedAt($value)
 * @method static Builder|Webhook whereUrl($value)
 * @method static Builder|Webhook whereUserId($value)
 * @mixin \Eloquent
 */
class Webhook extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'type' => WebhookType::class,
        'is_active' => 'bool',
        'config' => 'array'
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(ApiUser::class, 'user_id');
    }

    public function webhookEvents() : HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function getHandler(): WebhookHandler
    {
        $handlerClass = DefaultWebhookHandler::class;
        if ($this->type === WebhookType::ORDER_SHIPPED) {
            $handlerClass = OrderShippedWebhookHandler::class;
        }

        return app($handlerClass);
    }
}
