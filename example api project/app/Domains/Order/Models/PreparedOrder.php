<?php

namespace App\Domains\Order\Models;

use App\Domains\Account\Models\ApiUser;
use Database\Factories\Order\PreparedOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Domains\Order\Models\PreparedOrder
 *
 * @property int $id
 * @property int $user_id
 * @property array $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ApiUser $user
 * @method static Builder|PreparedOrder newModelQuery()
 * @method static Builder|PreparedOrder newQuery()
 * @method static Builder|PreparedOrder query()
 * @method static Builder|PreparedOrder whereCreatedAt($value)
 * @method static Builder|PreparedOrder whereId($value)
 * @method static Builder|PreparedOrder whereOrder($value)
 * @method static Builder|PreparedOrder whereUpdatedAt($value)
 * @method static Builder|PreparedOrder whereUserId($value)
 * @property Carbon|null $deleted_at
 * @method static \Database\Factories\Order\PreparedOrderFactory factory(...$parameters)
 * @method static \Illuminate\Database\Query\Builder|PreparedOrder onlyTrashed()
 * @method static Builder|PreparedOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PreparedOrder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PreparedOrder withoutTrashed()
 * @mixin \Eloquent
 */
class PreparedOrder extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'order' => 'array',
    ];

    protected $dates = ['deleted_at'];

	public static function newFactory(): PreparedOrderFactory
	{
		return new PreparedOrderFactory();
	}

    public function user() : BelongsTo
    {
        return $this->belongsTo(ApiUser::class, 'user_id');
    }
}
