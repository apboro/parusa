<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\Image;
use App\Models\Sails\Excursion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionEditController extends ApiEditController
{
    protected array $rules = [
        'name' => 'required',
        'status_id' => 'required',
        'images' => 'required|max:1',
        'duration' => 'required|integer|min:0',
    ];

    protected array $titles = [
        'name' => 'Название',
        'status_id' => 'Статус',
        'images' => 'Фотография экскурсии',
        'programs' => 'Типы программы',
        'duration' => 'Продолжительность, минут',
        'description' => 'Краткое описание',
        'announce' => 'Полное описание',
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
        /** @var Excursion|null $excursion */
        $excursion = $this->firstOrNew(Excursion::class, $request, ['status', 'images', 'programs', 'info']);

        if ($excursion === null) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        // send response
        return APIResponse::form(
            [
                'name' => $excursion->name,
                'status_id' => $excursion->status_id,
                'images' => $excursion->images->map(function (Image $image) {
                    return ['id' => $image->id, 'url' => $image->url];
                }),
                'programs' => $excursion->programs->pluck('id'),
                'duration' => $excursion->info->duration,
                'description' => $excursion->info->description,
                'announce' => $excursion->info->announce,
            ],
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
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        /** @var Excursion|null $excursion */
        $excursion = $this->firstOrNew(Excursion::class, $request);

        if ($excursion === null) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        $excursion->setAttribute('name', $data['name']);
        $excursion->setStatus($data['status_id'], false);
        $excursion->save();

        $info = $excursion->info;
        $info->setAttribute('duration', $data['duration']);
        $info->setAttribute('announce', $data['announce']);
        $info->setAttribute('description', $data['description']);
        $info->save();

        //images
        $images = Image::createFromMany($data['images'], 'public_images');
        $imageIds = $images->pluck('id')->toArray();
        $excursion->images()->sync($imageIds);

        $excursion->programs()->sync($data['programs']);

        return APIResponse::formSuccess(
            $excursion->wasRecentlyCreated ? 'Экскурсия добавлена' : 'Данные экскурсии обновлены',
            [
                'id' => $excursion->id,
                'title' => $excursion->name,
            ]
        );
    }
}
