<?php

namespace App\Models\Sails;

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
                'created_at',
                'updated_at',
            ]);
    }
}
