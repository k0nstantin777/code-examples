<?php

namespace App\Domains\User\Models;

use App\Domains\Exchanger\Models\ExchangeRequest;
use App\Domains\Exchanger\Models\ExchangerSession;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Domains\User\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $username
 * @property int $telegram_chat_id
 * @property Carbon|null $last_active_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereTelegramChatId($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
 * @property-read Collection|ExchangeRequest[] $exchangeRequests
 * @property-read int|null $exchange_requests_count
 * @property-read ExchangerSession|null $exchangerSession
 * @property string $lang
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User whereLang($value)
 * @method static Builder|User whereLastActiveAt($value)
 * @property string $telegram_bot_name
 * @method static Builder|User whereTelegramBotName($value)
 */
class User extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at', 'last_active_at'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static function newFactory(): UserFactory
    {
        return new UserFactory();
    }

    public function exchangerSession() : HasOne
    {
        return $this->hasOne(ExchangerSession::class);
    }

    public function exchangeRequests() : HasMany
    {
        return $this->hasMany(ExchangeRequest::class);
    }

    public function getExchangerUserIdOrNull() : ?int
    {
        $exchangerSession = $this->exchangerSession;

        return $exchangerSession && false === $exchangerSession->isExpired() ?
                    $exchangerSession->exchanger_user_id : null;
    }
}
