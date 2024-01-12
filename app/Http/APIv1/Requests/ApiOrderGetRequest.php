<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/orders',
    description: 'Запрос служит для получения всех заказов с указанными параметрами, если не указаны параметры, в ответе приходят все заказы партнёра за текущий месяц.',
    summary: 'Получить список заказов',
    tags: ['Заказы'],
    parameters: [
        new OA\Parameter(name: 'order_id', description: 'ID заказа', in: 'query', example: '128561'),
        new OA\Parameter(name: 'date_from', description: 'С даты: ГГГГ-ММ-ДД', in: 'query', example: '2024-01-01'),
        new OA\Parameter(name: 'date_to', description: 'До даты: ГГГГ-ММ-ДД', in: 'query', example: '2024-02-01'),
    ],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 401, description: 'Not allowed'),
    ],
)]

class ApiOrderGetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => 'nullable|numeric',
            'date_from' => 'nullable|string',
            'date_to' => 'nullable|string',
        ];
    }


}
