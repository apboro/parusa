<?php

namespace App\Models\Partner;

use App\Models\Dictionaries\PartnerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait PartnerAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                DB::raw('IF(status_id = ' . PartnerStatus::active . ', true, false) as enabled'),
                'name as order',
                'created_at',
                'updated_at',
            ]);
    }
}
