<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends ApiEditController
{
    protected array $settings = [
        'general' => [
            'fields' => [
                'default_cancellation_time' => Settings::int,
                'cancellation_notify_time' => Settings::int,
                'tickets_limit_by_capacity' => Settings::int,
                'buyer_email_welcome' => Settings::string,
            ],
            'titles' => [
                'default_cancellation_time' => 'Время аннулирования броней для всех рейсов по умолчанию, мин.',
                'cancellation_notify_time' => 'Время вывода предупреждения об окончании брони в кабинете партнера (за сколько начинать предупреждать), мин.',
                'tickets_limit_by_capacity' => 'Ограничивать количество билетов в 1 заказе вместимостью теплохода (остатком мест)',
                'buyer_email_welcome' => 'Текстовка письма плательщику с PDF-файлом заказа',
            ],
            'rules' => [
                'default_cancellation_time' => 'required|integer|min:0',
                'cancellation_notify_time' => 'required|integer|min:0',
                'tickets_limit_by_capacity' => 'required',
                'buyer_email_welcome' => '',
            ],
        ],
    ];

    /**
     * Get general settings.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getGeneral(Request $request): JsonResponse
    {
        return $this->get('general');
    }

    /**
     * Set general settings.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setGeneral(Request $request): JsonResponse
    {
        return $this->set($request, 'general');
    }

    /**
     * Get settings for section.
     *
     * @param string $section
     *
     * @return  JsonResponse
     */
    protected function get(string $section): JsonResponse
    {
        $values = [];

        foreach ($this->settings[$section]['fields'] as $key => $type) {
            $values[$key] = Settings::get($key, null, $type);
        }

        return APIResponse::form($values, $this->settings[$section]['rules'], $this->settings[$section]['titles']);
    }

    /**
     * Set settings for section.
     *
     * @param Request $request
     * @param string $section
     *
     * @return  JsonResponse
     */
    protected function set(Request $request, string $section): JsonResponse
    {
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->settings[$section]['rules'], $this->settings[$section]['titles'])) {
            return APIResponse::validationError($errors);
        }

        foreach ($this->settings[$section]['fields'] as $key => $type) {
            Settings::set($key, $data[$key], $type);
        }

        Settings::save();

        return APIResponse::success('Настройки сохранены');
    }
}
