<?php

namespace App\Http\Controllers\API\Piers;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\Image;
use App\Models\Piers\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PierEditController extends ApiEditController
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
        'phone' => 'Телефон',
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
        /** @var Pier|null $pier */
        $pier = $this->firstOrNew(Pier::class, $request, ['info', 'images']);

        if ($pier === null) {
            return APIResponse::notFound('Причал не найден');
        }

        // send response
        return APIResponse::form(
            [
                'name' => $pier->name,
                'status_id' => $pier->status_id,
                'work_time' => $pier->info->work_time,
                'phone' => $pier->info->phone,
                'address' => $pier->info->address,
                'latitude' => $pier->info->latitude,
                'longitude' => $pier->info->longitude,
                'description' => $pier->info->description,
                'way_to' => $pier->info->way_to,
                'images' => $pier->images->map(function (Image $image) {
                    return ['id' => $image->id, 'url' => $image->url];
                }),
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $pier->exists ? $pier->name : 'Добавление причала',
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
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        /** @var Pier|null $pier */
        $pier = $this->firstOrNew(Pier::class, $request);

        if ($pier === null) {
            return APIResponse::notFound('Причал не найден');
        }

        $pier->setAttribute('name', $data['name']);
        $pier->setStatus($data['status_id'], false);
        $pier->save();

        $info = $pier->info;
        $info->setAttribute('work_time', $data['work_time']);
        $info->setAttribute('phone', $data['phone']);
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

        return APIResponse::success(
            $pier->wasRecentlyCreated ? 'Причал добавлен' : 'Данные причала обновлены',
            [
                'id' => $pier->id,
                'name' => $pier->name,
            ]
        );
    }
}
