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
class AccountTransactionPrimaryType extends AccountTransactionType implements AsDictionary
{
    /**
     * Make dictionary query.
     *
     * @return  Builder
     */
    public static function asDictionary(): Builder
    {
        return self::query()->whereNull('parent_type_id');
    }
}
