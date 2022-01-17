<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait UserAsRepresentativesDictionary
{
    public static function asDictionary(): Builder
    {
        if(DB::getDefaultConnection() === 'sqlite') {
            $name = DB::raw('user_profiles.lastname || \' \' || user_profiles.firstname || \' \' || user_profiles.patronymic as name');
        } else {
            $name = DB::raw('CONCAT_WS(\' \', user_profiles.lastname, user_profiles.firstname, user_profiles.patronymic) as name');
        }

        return self::query()
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select([
                'id',
                $name,
                'status_id as enabled', // TODO subselect status_id === PartnerStatus::active
                'user_profiles.lastname as order',
                'users.created_at as created_at',
                'user_profiles.updated_at as updated_at',
            ]);
    }
}
