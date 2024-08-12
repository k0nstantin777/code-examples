<?php

namespace App\Domains\Account\Models;

use Database\Factories\Account\ApiUserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * App\Domains\Account\Models\ApiUser
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $ffc_id
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static ApiUserFactory factory(...$parameters)
 * @method static Builder|ApiUser newModelQuery()
 * @method static Builder|ApiUser newQuery()
 * @method static Builder|ApiUser query()
 * @method static Builder|ApiUser whereCreatedAt($value)
 * @method static Builder|ApiUser whereEmail($value)
 * @method static Builder|ApiUser whereEmailVerifiedAt($value)
 * @method static Builder|ApiUser whereFfcId($value)
 * @method static Builder|ApiUser whereId($value)
 * @method static Builder|ApiUser whereName($value)
 * @method static Builder|ApiUser wherePassword($value)
 * @method static Builder|ApiUser whereRememberToken($value)
 * @method static Builder|ApiUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ApiUser extends User
{
	use HasApiTokens;

	protected $table = 'users';

	protected $fillable = [
		'name',
		'email',
		'password',
		'ffc_id',
	];

	public static function newFactory(): ApiUserFactory
	{
		return new ApiUserFactory();
	}
}
