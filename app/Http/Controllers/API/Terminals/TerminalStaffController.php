<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Hit\Hit;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalStaffController extends ApiController
{
    /**
     * Attach staff user to terminal.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function attach(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        /** @var Terminal $terminal */
        if (null === ($terminal = $this->getTerminal($request))) {
            return APIResponse::notFound('Касса не найдена');
        }

        /** @var Position $position */
        if (null === ($position = $this->getPosition($request))) {
            return APIResponse::notFound('Сотрудник не найден');
        }

        if ($terminal->staff()->where('id', $position->id)->count() > 0) {
            return APIResponse::error('Сотрудник уже прикреплён к этой кассе');
        }

        $terminal->staff()->attach($position->id);

        return APIResponse::success('Кассир прикреплён', [
            'id' => $position->id,
            'name' => $position->user->profile->fullName,
            'active' => $position->hasStatus(PositionStatus::active),
            'position' => $position->title,
            'email' => $position->staffInfo->email,
            'work_phone' => $position->staffInfo->work_phone,
            'work_phone_add' => $position->staffInfo->work_phone_additional,
            'mobile_phone' => $position->staffInfo->mobile_phone,
            'has_access' => !empty($position->user->login) && !empty($position->user->password),
        ]);
    }

    /**
     * Detach staff user from terminal.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function detach(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        /** @var Terminal $terminal */
        if (null === ($terminal = $this->getTerminal($request))) {
            return APIResponse::notFound('Касса не найдена');
        }

        /** @var Position $position */
        if (null === ($position = $this->getPosition($request))) {
            return APIResponse::notFound('Сотрудник не найден');
        }

        $terminal->staff()->detach($position->id);

        return APIResponse::success('Кассир откреплён');
    }

    /**
     * Get terminal.
     *
     * @param Request $request
     *
     * @return  Terminal|null
     */
    protected function getTerminal(Request $request): ?Terminal
    {
        /** @var ?Terminal $terminal */
        $terminal = Terminal::query()->where('id', $request->input('id'))->first();

        return $terminal;
    }

    /**
     * Get user.
     *
     * @param Request $request
     *
     * @return  Position|null
     */
    protected function getPosition(Request $request): ?Position
    {
        /** @var ?Position $position */
        $position = Position::query()
            ->where('id', $request->input('data.staff_id'))
            ->where('is_staff', true)
            ->whereHas('roles', function (Builder $query) {
                $query->where('id', Role::terminal);
            })
            ->first();

        return $position;
    }
}
