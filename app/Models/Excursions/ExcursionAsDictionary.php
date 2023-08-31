<?php

namespace App\Models\Excursions;

use App\Models\Dictionaries\ExcursionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait ExcursionAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                'type_id',
                DB::raw('IF(status_id = ' . ExcursionStatus::active . ', true, false) as enabled'),
                'name as order',
                'created_at',
                'updated_at',
            ]);
    }
}
