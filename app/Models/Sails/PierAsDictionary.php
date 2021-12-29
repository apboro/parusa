<?php

namespace App\Models\Sails;

use Illuminate\Database\Eloquent\Builder;

trait PierAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                'status_id as enabled', // TODO sub select status_id === PierStatus::active
                'name as order',
                'created_at',
                'updated_at',
            ]);
    }
}
