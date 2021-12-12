<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Builder;

trait PartnerAsDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->select([
                'id',
                'name',
                'enabled', // TODO subselect status_id === PartnerStatus::active
                'name as order',
                'created_at',
                'updated_at',
            ]);
    }
}
