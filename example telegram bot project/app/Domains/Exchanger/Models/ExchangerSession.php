<?php

namespace App\Domains\Exchanger\Models;

use App\Domains\User\Models\User;
use Database\Factories\ExchangerSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Domains\Exchanger\Models\ExchangerSession
 *
 * @property int $id
 * @property int $user_id
 * @property int $exchanger_user_id
 * @property Carbon $session_updated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ExchangerSession newModelQuery()
 * @method static Builder|ExchangerSession newQuery()
 * @method static Builder|ExchangerSession query()
 * @method static Builder|ExchangerSession whereCreatedAt($value)
 * @method static Builder|ExchangerSession whereExchangerUserId($value)
 * @method static Builder|ExchangerSession whereId($value)
 * @method static Builder|ExchangerSession whereSessionUpdatedAt($value)
 * @method static Builder|ExchangerSession whereUpdatedAt($value)
 * @method static Builder|ExchangerSession whereUserId($value)
 * @mixin \Eloquent
 * @property-read User|null $user
 * @method static \Database\Factories\ExchangerSessionFactory factory(...$parameters)
 */
class ExchangerSession extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dates = ['session_updated_at'];

    public static function newFactory(): ExchangerSessionFactory
    {
        return new ExchangerSessionFactory();
    }

    public function isExpired() : bool
    {
        return $this->session_updated_at->lessThan(now()->subMinutes(config('session.lifetime')));
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
