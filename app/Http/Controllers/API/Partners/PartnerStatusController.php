<?php

namespace App\Http\Controllers\API\Partners;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Partner\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerStatusController extends ApiEditController
{
    protected array $rules = [
        'tickets_for_guides' => 'required|integer|min:0',
    ];

    protected array $titles = [
        'tickets_for_guides' => 'Билеты для гидов',
    ];

    /**
     * Update partner status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */
        try {
            $partner->setStatus((int)$request->input('status_id'));
            $partner->save();
        } catch (WrongPartnerStatusException $e) {
            return APIResponse::error('Неверный статус партнёра');
        }

        return APIResponse::response([
            'active' => $partner->hasStatus(PartnerStatus::active),
            'status' => $partner->status->name,
            'status_id' => $partner->status_id,
            'message' => 'Статус партнёра обновлён',
        ]);
    }

    /**
     * Update partner can reserve tickets.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setCanReserve(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */

        $partner->profile->can_reserve_tickets = (bool)$request->input('can_reserve_tickets');
        $partner->profile->save();

        return APIResponse::response([
            'can_reserve_tickets' => $partner->profile->can_reserve_tickets ? 1 : 0,
            'message' => 'Данные обновлёны',
        ]);
    }

    /**
     * Set tickets for guides quantity.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setGuideTickets(Request $request): JsonResponse
    {
        $data = $this->getData($request);
        $data['tickets_for_guides'] = (int)$data['tickets_for_guides'];

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        $id = $request->input('id');

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */

        $partner->profile->tickets_for_guides = $data['tickets_for_guides'];
        $partner->profile->save();

        return APIResponse::formSuccess('Данные обновлены', [
            'id' => $partner->id,
            'tickets_for_guides' => $partner->profile->tickets_for_guides,
        ]);
    }
}
