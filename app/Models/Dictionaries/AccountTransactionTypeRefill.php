<?php

namespace App\Models\Dictionaries;

use App\Models\Dictionaries\Interfaces\AsDictionary;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 * @property int $sign
 */
class AccountTransactionTypeRefill extends AccountTransactionType implements AsDictionary
{
    /**
     * Make dictionary query.
     *
     * @return  Builder
     */
    public static function asDictionary(): Builder
    {
        return self::query()->where('parent_type_id', self::account_refill);
    }

    /**
     * Name attribute mutator.
     *
     * @param $value
     *
     * @return  string|null
     */
    public function getNameAttribute($value): ?string
    {
        return $value;
    }
}
