<?php

namespace App\Http\Controllers\API\Partners\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Positions\Position;
use App\Models\User\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepresentativePositionsController extends ApiEditController
{
    protected array $rules = [
        'partner_id' => 'required',
        'title' => 'required',
        'work_phone' => 'required',
        'email' => 'required|email',
    ];

    protected array $titles = [
        'partner_id' => 'Компания',
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
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        if (($userId = $request->input('representative_id')) === null || null === ($user = User::query()->where('id', $userId)->doesntHave('staffPosition')->first())) {
            return APIResponse::notFound('Такой сотрудник не найден');
        }

        $position = $this->firstOrNew(Position::class, $request);

        if ($position === null) {
            return APIResponse::notFound();
        }

        /** @var User $user */
        /** @var Position $position */

        $position->user_id = $user->id;
        $position->partner_id = $data['partner_id'];
        $position->title = $data['title'];
        $position->save();

        $position->info->work_phone = $data['work_phone'];
        $position->info->work_phone_additional = $data['work_phone_additional'];
        $position->info->email = $data['email'];
        $position->info->save();

        return APIResponse::formSuccess(
            $position->wasRecentlyCreated ? 'Представитель прикреплйм' : 'Запись обновлена',
            [
                'id' => $user->id,
                'positions' => $user->positions->map(function (Position $position) {
                    return [
                        'position_id' => $position->id,
                        'partner_id' => $position->partner->id,
                        'partner' => $position->partner->name,
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

        if ($id === null || null === ($user = User::query()
                ->with(['positions'])
                ->where('id', $id)
                ->doesntHave('staffPosition')->first())
        ) {
            return APIResponse::notFound('Представитель не найден');
        }

        $positionId = $request->input('position_id');

        if ($positionId === null || null === ($position = Position::query()
                ->where(['id' => $positionId, 'user_id' => $id])->first())
        ) {
            return APIResponse::notFound('Такая привязка пользователя к организации не найдена');
        }

        /** @var Position $position */

        try {
            $position->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно открепить представителя. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response('Представитель откреплён');
    }
}
