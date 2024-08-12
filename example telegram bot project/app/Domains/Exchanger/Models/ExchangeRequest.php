<?php

namespace App\Domains\Exchanger\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Domains\Exchanger\Models\ExchangeRequest
 *
 * @method static Builder|ExchangeRequest newModelQuery()
 * @method static Builder|ExchangeRequest newQuery()
 * @method static \Illuminate\Database\Query\Builder|ExchangeRequest onlyTrashed()
 * @method static Builder|ExchangeRequest query()
 * @method static \Illuminate\Database\Query\Builder|ExchangeRequest withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ExchangeRequest withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static Builder|ExchangeRequest whereCreatedAt($value)
 * @method static Builder|ExchangeRequest whereData($value)
 * @method static Builder|ExchangeRequest whereDeletedAt($value)
 * @method static Builder|ExchangeRequest whereId($value)
 * @method static Builder|ExchangeRequest whereUpdatedAt($value)
 * @method static Builder|ExchangeRequest whereUserId($value)
 */
class ExchangeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $dates = ['deleted_at'];
}
