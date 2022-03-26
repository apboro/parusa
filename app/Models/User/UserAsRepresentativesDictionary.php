<?php

namespace App\Models\User;

use App\Models\Dictionaries\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait UserAsRepresentativesDictionary
{
    public static function asDictionary(): Builder
    {
        return self::query()
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('positions', 'positions.id', '=', 'positions.user_id')
            ->select([
                'users.id',
                DB::raw('CONCAT_WS(\' \', user_profiles.lastname, user_profiles.firstname, user_profiles.patronymic) as name'),
                DB::raw('IF(positions.status_id = ' . UserStatus::active . ', true, false) as enabled'),
                'user_profiles.lastname as order',
                'users.created_at as created_at',
                'user_profiles.updated_at as updated_at',
            ])->distinct();
    }
}
