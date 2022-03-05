<?php

namespace App\Models\POS;

use App\Models\Dictionaries\Role;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TerminalUser extends User
{
    public static function asDictionary(): Builder
    {
        if (DB::getDefaultConnection() === 'sqlite') {
            $name = DB::raw('user_profiles.lastname || \' \' || user_profiles.firstname || \' \' || user_profiles.patronymic as name');
        } else {
            $name = DB::raw('CONCAT_WS(\' \', user_profiles.lastname, user_profiles.firstname, user_profiles.patronymic) as name');
        }

        return self::query()
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->whereHas('staffPosition', function (Builder $query) {
                $query->whereHas('roles', function (Builder $query) {
                    $query->where('id', Role::terminal);
                });
            })
            ->select([
                'id',
                $name,
                'status_id as enabled', // TODO subselect status_id === PartnerStatus::active
                'user_profiles.lastname as order',
                'users.created_at as created_at',
                'users.updated_at as updated_at',
            ]);
    }
}
