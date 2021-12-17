<?php

namespace App\Http\Controllers\API\Sails;

use App\Exceptions\Sails\WrongPierStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Sails\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PierEditController extends ApiController
{
    protected array $rules = [
        'name' => 'required',
        'status_id' => 'required',
        'images' => 'required|max:1',
    ];

    protected array $titles = [
        'name' => 'Название',
        'status_id' => 'Статус',
        'work_time' => 'Время работы',
        'address' => 'Адрес',
        'latitude' => 'Широта',
        'longitude' => 'Долгота',
        'description' => 'Описание причала',
        'way_to' => 'Как добраться',
        'images' => 'Фотография причала',
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

        $images = $pier->images->map(function (Image $image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
            ];
        });

        // fill data
        $values = [
            'name' => $pier->name,
            'status_id' => $pier->status_id,
            'work_time' => $pier->info->work_time,
            'address' => $pier->info->address,
            'latitude' => $pier->info->latitude,
            'longitude' => $pier->info->longitude,
            'description' => $pier->info->description,
            'way_to' => $pier->info->way_to,
            'images' => $images,
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

            $pier->info()->create([
                'work_time' => $data['work_time'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'description' => $data['description'],
                'way_to' => $data['way_to'],
            ]);
            //images
            $images = Image::createFromMany($data['images'], 'public_images');
            $imageIds = $images->pluck('id')->toArray();
            $pier->images()->sync($imageIds);

            return APIResponse::formSuccess('Причал добавлен', ['id' => $pier->id]);
        }

        if (null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }
        /** @var Pier $pier */
        $pier->setAttribute('name', $data['name']);
        $pier->setStatus($data['status_id']);
        $pier->save();

        $info = $pier->info;
        $info->setAttribute('work_time', $data['work_time']);
        $info->setAttribute('address', $data['address']);
        $info->setAttribute('latitude', $data['latitude']);
        $info->setAttribute('longitude', $data['longitude']);
        $info->setAttribute('description', $data['description']);
        $info->setAttribute('way_to', $data['way_to']);
        $info->save();

        //images
        $images = Image::createFromMany($data['images'], 'public_images');
        $imageIds = $images->pluck('id')->toArray();
        $pier->images()->sync($imageIds);

        return APIResponse::formSuccess('Данные причала обновлены', [
            'title' => $pier->name,
        ]);
    }

    /**
     * Update pier status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Причал не найден');
        }

        /** @var Pier $pier */
        try {
            $pier->setStatus((int)$request->input('status_id'));
            $pier->save();
        } catch (WrongPierStatusException $e) {
            return APIResponse::error('Неверный статус причала');
        }

        return APIResponse::response([
            'status' => $pier->status->name,
            'status_id' => $pier->status_id,
            'message' => 'Статус причала обновлён',
        ]);
    }
}
