<?php

namespace App\Models\POS;

use App\Models\Dictionaries\Role;
use App\Models\Positions\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TerminalPositions extends Position
{
    protected $table = 'positions';

    public static function asDictionary(): Builder
    {
        if (DB::getDefaultConnection() === 'sqlite') {
            $name = DB::raw('user_profiles.lastname || \' \' || user_profiles.firstname || \' \' || user_profiles.patronymic as name');
        } else {
            $name = DB::raw('CONCAT_WS(\' \', user_profiles.lastname, user_profiles.firstname, user_profiles.patronymic) as name');
        }

        return self::query()
            ->leftJoin('users', 'users.id', '=', 'positions.user_id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('is_staff', true)
            ->whereHas('roles', function (Builder $query) {
                $query->where('id', Role::terminal);
            })
            ->select([
                'positions.id',
                $name,
                'positions.status_id as enabled', // TODO subselect status_id === PartnerStatus::active
                'user_profiles.lastname as order',
                'users.created_at as created_at',
                'users.updated_at as updated_at',
            ]);
    }
}
