<?php

namespace App\Models\Piers;

use App\Models\Dictionaries\PiersStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait PierAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                'provider_id',
                DB::raw('IF(status_id = ' . PiersStatus::active . ', true, false) as enabled'),
                'name as order',
                'created_at',
                'updated_at',
            ]);
    }
}
