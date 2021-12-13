<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Excursion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExcursionEditController extends ApiController
{
    protected array $rules = [
        'name' => 'required',
        'status_id' => 'required',
    ];

    protected array $titles = [
        'name' => 'Название',
        'status_id' => 'Статус',
    ];

    /**
     * Get edit data for excursion.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        if (($id = $request->input('id')) === null) {
            return APIResponse::notFound();
        }

        $id = (int)$id;

        /** @var Excursion $excursion */

        if ($id === 0) {
            $excursion = new Excursion;

        } else if (null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }

        // fill data
        $values = [
            'name' => $excursion->name,
            'status_id' => $excursion->status_id,
        ];

        // send response
        return APIResponse::form(
            $values,
            $this->rules,
            $this->titles,
            [
                'title' => $excursion->exists ? $excursion->name : 'Добавление экскурсии',
            ]
        );
    }

    /**
     * Update excursion data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->input('data', []);

        $validator = Validator::make($data, $this->rules, [], array_map(function ($value) {
            return '"' . strtolower($value) . '"';
        }, $this->titles));

        if ($validator->fails()) {
            return APIResponse::formError(
                $data,
                $this->rules,
                $this->titles,
                $validator->getMessageBag()->toArray()
            );
        }

        $id = $request->input('id');

        if ($id === null) {
            return APIResponse::notFound();
        }

        $id = (int)$id;

        if ($id === 0) {
            $excursion = new Excursion;
        $excursion->setAttribute('name', $data['name']);
            $excursion->setStatus($data['status_id'], false);
            $excursion->save();

            return APIResponse::formSuccess('Экскурсия добавлена', ['id' => $excursion->id]);
        }

        if (null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }
        /** @var Excursion $excursion */
        $excursion->setAttribute('name', $data['name']);
        $excursion->setStatus($data['status_id']);
        $excursion->save();

        return APIResponse::formSuccess('Данные экскурсии обновлены');
    }
}
