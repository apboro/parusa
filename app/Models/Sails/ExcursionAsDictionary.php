<?php

namespace App\Models\Sails;

use Illuminate\Database\Eloquent\Builder;

trait ExcursionAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                'enabled', // TODO sub select status_id === ExcursionStatus::active
                'name as order',
                'created_at',
                'updated_at',
            ]);
    }
}
