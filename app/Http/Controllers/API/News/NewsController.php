<?php

namespace App\Http\Controllers\API\News;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\Image;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\News\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends ApiEditController
{
    protected array $rules = [
        'title' => ['required'],
        'description' => ['nullable'],
        'file_id' => ['nullable'],
    ];

    protected array $titles = [];

    public function list()
    {
        return;
    }

    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);

        /** @var News|null $news */
        $news = $this->firstOrNew(News::class, $request);

        if ($news === null) {
            return APIResponse::notFound('Новость не найдена');
        }

        // send response
        return APIResponse::form(
            [
                'title' => $news->title,
                'description' => $news->description,
                'images' => $news->images->map(function (Image $image) {
                    return ['id' => $image->id, 'url' => $image->url];
                }),
            ],
            $this->rules,
            [
                'name' => $news->exists ? $news->title : 'Добавление новости',
            ]
        );
    }

    public function update(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        $news = $this->firstOrNew(News::class, $request);

        if ($news === null) {
            return APIResponse::notFound('Новость не найдена');
        }

        //images
        $images = Image::createFromMany($data['images'], 'public_images');
        $imageIds = $images->pluck('id')->toArray();
        $news->images()->sync($imageIds);

        return APIResponse::success(
            'Черновик новости сохранен',
            [
                'id' => $news->id,
                'name' => $news->name,
            ]
        );
    }


}
