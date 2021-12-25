<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\PartnerType;
use App\Models\Sails\Ship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryEditController extends ApiEditController
{
    protected array $dictionaries = [
        'ships' => [
            'title' => 'Теплоходы',
            'class' => Ship::class,
        ],
        'excursion_programs' => [
            'title' => 'Типы программ',
            'class' => ExcursionProgram::class,
        ],
        'partner_types' => [
            'title' => 'Типы партнёров',
            'class' => PartnerType::class,
        ],
    ];

    /**
     * Get editable dictionaries list.
     *
     * @return  JsonResponse
     */
    public function index(): JsonResponse
    {
        return APIResponse::response(array_map(function ($item) {
            return $item['title'];
        }, $this->dictionaries), []);
    }

    /**
     * Get details for selected dictionary.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $name = $request->input('name');

        if (!array_key_exists($name, $this->dictionaries)) {
            return APIResponse::notFound("Словарь {$name} не найден.");
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name]['class'];

        $all = $class::query()->orderBy('order')->orderBy('name')->get();

        return APIResponse::response($all, []);
    }

    /**
     * Update order and status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $name = $request->input('name');

        if (!array_key_exists($name, $this->dictionaries)) {
            return APIResponse::notFound("Словарь {$name} не найден.");
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name]['class'];

        $data = $request->input('data');

        foreach ($data as $item) {
            $class::query()->where('id', $item['id'])->update(['order' => $item['order'], 'enabled' => $item['enabled']]);
        }

        return APIResponse::response('Success');
    }
}
