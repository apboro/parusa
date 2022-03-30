<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\POS\Terminal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalEditController extends ApiEditController
{
    protected array $rules = [
        'status_id' => 'required',
        'pier_id' => 'required',
        'workplace_id' => 'required',
        'outlet_id' => 'required',
    ];

    protected array $titles = [
        'status_id' => 'Статус',
        'pier_id' => 'Причал',
        'workplace_id' => 'Внешний ID мобильной кассы',
        'outlet_id' => 'Внешний ID торговой точки',
    ];

    /**
     * Get edit data for pier.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        /** @var Terminal|null $terminal */
        $terminal = $this->firstOrNew(Terminal::class, $request, []);

        if ($terminal === null) {
            return APIResponse::notFound('Касса не найдена');
        }

        // send response
        return APIResponse::form(
            [
                'status_id' => $terminal->status_id,
                'pier_id' => $terminal->pier_id,
                'workplace_id' => $terminal->workplace_id,
                'outlet_id' => $terminal->outlet_id,
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $terminal->exists ? 'Касса №' . $terminal->id : 'Добавление кассы',
            ]
        );
    }

    /**
     * Update pier data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        /** @var Terminal|null $terminal */
        $terminal = $this->firstOrNew(Terminal::class, $request);

        if ($terminal === null) {
            return APIResponse::notFound('Касса не найдена');
        }

        $terminal->setAttribute('pier_id', $data['pier_id']);
        $terminal->setAttribute('workplace_id', $data['workplace_id']);
        $terminal->setAttribute('outlet_id', $data['outlet_id']);
        $terminal->setStatus($data['status_id'], false);
        $terminal->save();

        return APIResponse::success(
            $terminal->wasRecentlyCreated ? 'Касса добавлена' : 'Данные кассы обновлены',
            [
                'id' => $terminal->id,
                'name' => 'Касса №' . $terminal->id,
            ]
        );
    }
}
