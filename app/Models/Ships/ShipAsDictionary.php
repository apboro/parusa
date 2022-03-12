<?php

namespace App\Models\Ships;

use Illuminate\Database\Eloquent\Builder;

trait ShipAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                'enabled',
                'order',
                'capacity',
                'created_at',
                'updated_at',
            ]);
    }
}
