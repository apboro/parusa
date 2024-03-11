<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\Provider;
use App\Models\Hit\Hit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryEditController extends ApiEditController
{
    use EditableDictionaries;

    /**
     * Get editable dictionaries list.
     *
     * @return  JsonResponse
     */
    public function index(): JsonResponse
    {
        Hit::register(HitSource::admin);
        return APIResponse::response(array_map(static function ($item) {
            return $item['name'];
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
        Hit::register(HitSource::admin);
        $name = $request->input('name');

        if (!array_key_exists($name, $this->dictionaries)) {
            return APIResponse::notFound("Словарь $name не найден.");
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name]['class'];

        $query = $class::query()->orderBy('order')->orderBy('name');

        if ($name === 'ticket_grades' || $name === 'seat_categories') {
            $query->where('provider_id', Provider::scarlet_sails);
        }

        $all = $query->get();

        return APIResponse::response([
            'items' => $all->filter(function ($item) {
              return !$item->invisible;
            })->values(),
            'item_name' => $this->dictionaries[$name]['item_name'],
            'fields' => $this->dictionaries[$name]['fields'],
            'titles' => $this->dictionaries[$name]['titles'],
            'validation' => $this->dictionaries[$name]['validation'],
            'hide' => $this->dictionaries[$name]['hide'] ?? null,
            'auto' => $this->dictionaries[$name]['auto'] ?? null,
        ]);
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
        Hit::register(HitSource::admin);
        $name = $request->input('name');

        if (!array_key_exists($name, $this->dictionaries)) {
            return APIResponse::notFound("Словарь $name не найден.");
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name]['class'];

        $data = $request->input('data');

        foreach ($data as $item) {
            $class::query()->where('id', $item['id'])->update(['order' => $item['order'], 'enabled' => $item['enabled']]);
        }

        return APIResponse::response([], [], "Справочник $name обновлён");
    }

    /**
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $name = $request->input('name');

        if (!array_key_exists($name, $this->dictionaries)) {
            return APIResponse::notFound("Словарь $name не найден.");
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name]['class'];
        $title = $this->dictionaries[$name]['name'];

        $fields = $this->dictionaries[$name]['fields'];
        $titles = $this->dictionaries[$name]['titles'];
        $validation = $this->dictionaries[$name]['validation'];

        $data = $this->getData($request);
        $data = array_intersect_key($data, $fields);

        if ($errors = $this->validate($data, $validation, $titles)) {
            return APIResponse::validationError($errors);
        }

        /** @var AbstractDictionary $item */
        $item = $this->firstOrNew($class, $request);

        if ($item === null) {
            return APIResponse::notFound("Запись в словаре \"$title\" не найдена");
        }

        foreach ($data as $key => $value) {
            $item->setAttribute($key, $value);
            if ($name === 'ticket_grades' || $name === 'seat_categories') {
                $item->setAttribute('provider_id', 5);
            }
        }


        if (!$item->exists) {
            $order = (int)$class::query()->max('order') + 1;
            $item->order = $order;
        }

        $item->save();
        $item->refresh();

        return APIResponse::success(
            $item->wasRecentlyCreated ? "Запись в словаре \"$title\" добавлена" : "Запись в словаре \"$title\" обновлена",
            $item->toArray()
        );
    }
}
