<?php

namespace App\Http\Controllers\API\Excursions;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\Image;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionEditController extends ApiEditController
{
    protected array $rules = [
        'name' => 'required',
        'name_receipt' => 'required|max:40',
        'status_id' => 'required',
        'provider_id' => 'required',
        'images' => 'required',
        'duration' => 'required|integer|min:0',
        'trip_images' => 'required|max:1',
    ];

    protected array $titles = [
        'name' => 'Название',
        'name_receipt' => 'Название для чека',
        'status_id' => 'Статус',
        'excursion_type_id' => 'Тип экскурсии',
        'only_site' => 'Эксклюзивная экскурсия - билеты продаются только через сайт Алые Паруса',
        'use_seat_scheme' => 'Использовать схему рассадки',
        'provider_id' => 'Поставщик',
        'images' => 'Фотография экскурсии',
        'programs' => 'Типы программы',
        'duration' => 'Продолжительность, минут',
        'announce' => 'Краткое описание',
        'description' => 'Полное описание',
        'trip_images' => 'Карта маршрута',
        'is_single_ticket' => 'Единый билет',
        'reverse_excursion_id' => 'Обратная экскурсия',
        'disk_url' => 'Ссылка на облако'
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
        Hit::register(HitSource::admin);
        /** @var Excursion|null $excursion */
        $excursion = $this->firstOrNew(Excursion::class, $request, ['status', 'images', 'tripImages', 'programs', 'info', 'provider']);

        if ($excursion === null) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        // send response
        return APIResponse::form(
            [
                'name' => $excursion->name,
                'name_receipt' => $excursion->name_receipt,
                'status_id' => $excursion->status_id,
                'only_site' => $excursion->only_site,
                'use_seat_scheme' => $excursion->use_seat_scheme,
                'provider_id' => $excursion->provider_id,
                'excursion_type_id' => $excursion->type_id,
                'is_single_ticket' => $excursion->is_single_ticket,
                'reverse_excursion_id' => $excursion->reverse_excursion_id,
                'images' => $excursion->images->map(function (Image $image) {
                    return ['id' => $image->id, 'url' => $image->url];
                }),
                'trip_images' => $excursion->tripImages->map(function (Image $image) {
                    return ['id' => $image->id, 'url' => $image->url];
                }),
                'programs' => $excursion->programs->pluck('id'),
                'duration' => $excursion->info->duration,
                'description' => $excursion->info->description,
                'announce' => $excursion->info->announce,
                'disk_url' => $excursion->disk_url,
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $excursion->exists ? $excursion->name : 'Добавление экскурсии',
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
        Hit::register(HitSource::admin);
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        /** @var Excursion|null $excursion */
        $excursion = $this->firstOrNew(Excursion::class, $request);

        if ($excursion === null) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        $excursion->setAttribute('name', $data['name']);
        $excursion->setAttribute('name_receipt', $data['name_receipt']);
        $excursion->setAttribute('type_id', $data['excursion_type_id']);
        $excursion->setAttribute('provider_id', $data['provider_id']);
        $excursion->setAttribute('only_site', $data['only_site'] ?? false);
        $excursion->setAttribute('use_seat_scheme', $data['use_seat_scheme'] ?? false);
        $excursion->setAttribute('is_single_ticket', $data['is_single_ticket'] ?? false);
        $excursion->setAttribute('reverse_excursion_id', $data['reverse_excursion_id'] ?? null);
        $excursion->setAttribute('disk_url', $data['disk_url'] ?? null);
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

        //trip images
        $images = Image::createFromMany($data['trip_images'], 'public_images');
        $imageIds = $images->pluck('id')->toArray();
        $excursion->tripImages()->sync($imageIds);

        $excursion->programs()->sync($data['programs']);

        return APIResponse::success(
            $excursion->wasRecentlyCreated ? 'Экскурсия добавлена' : 'Данные экскурсии обновлены',
            [
                'id' => $excursion->id,
                'name' => $excursion->name,
            ]
        );
    }
}
