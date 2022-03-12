<?php

namespace App\Models\POS;

use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Positions\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TerminalPositions extends Position
{
    protected $table = 'positions';

    public static function asDictionary(): Builder
    {
        return self::query()
            ->leftJoin('users', 'users.id', '=', 'positions.user_id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('is_staff', true)
            ->whereHas('roles', function (Builder $query) {
                $query->where('id', Role::terminal);
            })
            ->select([
                'positions.id',
                DB::raw('CONCAT_WS(\' \', user_profiles.lastname, user_profiles.firstname, user_profiles.patronymic) as name'),
                DB::raw('IF(positions.status_id = ' . PositionStatus::active . ', true, false) as enabled'),
                'user_profiles.lastname as order',
                'positions.created_at as created_at',
                'positions.updated_at as updated_at',
            ]);
    }
}
