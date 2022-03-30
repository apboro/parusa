<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\User\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerRepresentativesController extends ApiEditController
{
    protected array $rules = [
        'representative_id' => 'required',
        'title' => 'required',
        'work_phone' => 'required',
        'email' => 'required|email',
    ];

    protected array $titles = [
        'representative_id' => 'Представитель',
        'title' => 'Должность',
        'work_phone' => 'Рабочий телефон',
        'work_phone_additional' => 'Доп. номер к раб. телефону',
        'email' => 'Email',
    ];

    /**
     * Attach new or update existing representative position.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function attach(Request $request): JsonResponse
    {
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        /** @var User $user */
        if (null === ($user = User::query()->where('id', $data['representative_id'])->first())) {
            return APIResponse::notFound('Представитель не найден');
        }

        /** @var Partner $partner */
        if (($partnerId = $request->input('partner_id')) === null || null === ($partner = Partner::query()->where('id', $partnerId)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        $positionId = $request->input('position_id');

        if ($positionId === 0) {
            /** @var Position $position */
            $position = Position::query()->where(['partner_id' => $partner->id, 'user_id' => $user->id, 'is_staff' => false])->first();
            if ($position !== null) {
                return APIResponse::error('Представитель уже привязан к этой организации.');
            }
            $position = new Position;
        } else {
            $position = Position::query()->where(['id' => $positionId, 'partner_id' => $partner->id, 'user_id' => $user->id, 'is_staff' => false])->first();
            if ($position === null) {
                return APIResponse::error('Привязка представителя к организации не найдена.');
            }
        }

        $position->user_id = $user->id;
        $position->partner_id = $partner->id;
        $position->title = $data['title'];
        $position->save();

        $position->info->work_phone = $data['work_phone'];
        $position->info->work_phone_additional = $data['work_phone_additional'];
        $position->info->email = $data['email'];
        $position->info->save();

        return APIResponse::success(
            $position->wasRecentlyCreated ? 'Представитель прикреплён' : 'Запись обновлена',
            [
                'id' => $partner->id,
                'positions' => $partner->positions->map(function (Position $position) {
                    return [
                        'position_id' => $position->id,
                        'user_id' => $position->user->id,
                        'user' => $position->user->profile->fullName,
                        'title' => $position->title,
                        'work_phone' => $position->info->work_phone,
                        'work_phone_additional' => $position->info->work_phone_additional,
                        'email' => $position->info->email,
                        'status' => $position->accessStatus->name,
                        'status_id' => $position->access_status_id,
                        'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
                    ];
                }),
            ]
        );
    }

    /**
     * Delete representative position.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function detach(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }
        /** @var Partner $partner */

        $positionId = $request->input('position_id');

        if ($positionId === null || null === ($position = Position::query()
                ->where(['id' => $positionId, 'partner_id' => $partner->id])->first())
        ) {
            return APIResponse::notFound('Привязка пользователя к организации не найдена');
        }

        /** @var Position $position */

        try {
            $position->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно открепить представителя. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], 'Представитель откреплён');
    }

    /**
     * Get representative defaults for new position.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        if (($id = $request->input('id')) === null || null === ($user = User::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Такой представитель не найден');
        }
        /** @var User $user */
        return APIResponse::response([
            'name' => $user->profile->fullName,
            'default_position_title' => $user->profile->default_position_title,
            'work_phone' => $user->profile->work_phone,
            'work_phone_additional' => $user->profile->work_phone_additional,
            'email' => $user->profile->email,
        ]);
    }
}
