<?php

namespace App\Http\Controllers\API\Sails;

use App\Exceptions\Sails\WrongExcursionStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Sails\Excursion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExcursionEditController extends ApiController
{
    protected array $rules = [
        'name' => 'required',
        'status_id' => 'required',
        'images' => 'required|max:1',
        'duration' => 'required|numeric',
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

        $images = $excursion->images->map(function (Image $image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
            ];
        });

        $programs = $excursion->programs->map(function (ExcursionProgram $program) {
            return $program->id;
        });

        // fill data
        $values = [
            'name' => $excursion->name,
            'status_id' => $excursion->status_id,
            'images' => $images,
            'programs' => $programs,
            'duration' => $excursion->info->duration,
            'description' => $excursion->info->description,
            'announce' => $excursion->info->announce,
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

            $excursion->info()->create([
                'duration' => $data['duration'],
                'announce' => $data['announce'],
                'description' => $data['description'],
            ]);

            //images
            $images = Image::createFromMany($data['images'], 'public_images');
            $imageIds = $images->pluck('id')->toArray();
            $excursion->images()->sync($imageIds);

            $excursion->programs()->sync($data['programs']);

            return APIResponse::formSuccess('Экскурсия добавлена', ['id' => $excursion->id]);
        }

        if (null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }
        /** @var Excursion $excursion */
        $excursion->setAttribute('name', $data['name']);
        $excursion->setStatus($data['status_id']);
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

        return APIResponse::formSuccess('Данные экскурсии обновлены', [
            'title' => $excursion->name,
        ]);
    }

    /**
     * Update excursion status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        /** @var Excursion $excursion */
        try {
            $excursion->setStatus((int)$request->input('status_id'));
            $excursion->save();
        } catch (WrongExcursionStatusException $e) {
            return APIResponse::error('Неверный статус экскурсии');
        }

        return APIResponse::response([
            'status' => $excursion->status->name,
            'status_id' => $excursion->status_id,
            'message' => 'Статус экскурсии обновлён',
        ]);
    }
}
