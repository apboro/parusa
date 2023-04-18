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
class PartnerOrderType extends OrderType implements AsDictionary
{
    /**
     * Make dictionary query.
     *
     * @return  Builder
     */
    public static function asDictionary(): Builder
    {
        $ids = [
            self::qr_code,
            self::site,
            self::partner_site,
            self::partner_sale,
        ];
        return self::query()->whereIn('id', $ids);
    }
}
