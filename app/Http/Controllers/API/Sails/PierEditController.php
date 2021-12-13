<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PierEditController extends ApiController
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
     * Get edit data for pier.
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

        /** @var Pier $pier */

        if ($id === 0) {
            // new user
            $pier = new Pier();

        } else if (null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }

        // fill data
        $values = [
            'name' => $pier->name,
            'status_id' => $pier->status_id,
        ];

        // send response
        return APIResponse::form(
            $values,
            $this->rules,
            $this->titles,
            [
                'title' => $pier->exists ? $pier->name : 'Добавление причала',
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
            $pier = new Pier;
            $pier->setAttribute('name', $data['name']);
            $pier->setStatus($data['status_id'], false);
            $pier->save();

            return APIResponse::formSuccess('Причал добавлен', ['id' => $pier->id]);
        }

        if (null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }
        /** @var Pier $pier */
        $pier->setAttribute('name', $data['name']);
        $pier->setStatus($data['status_id']);
        $pier->save();

        return APIResponse::formSuccess('Данные причала обновлены');
    }
}
