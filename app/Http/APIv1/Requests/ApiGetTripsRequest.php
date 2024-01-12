<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/trips',
    description: 'Запрос служит для получения списка всех актуальных на запрошенный день экскурсий системы.
<br> Если не задана дата, ответ будет содержать актуальные рейсы на сегодняшний день',
    summary: 'Получить список рейсов на день',
    tags: ['Рейсы'],
    parameters: [
        new OA\Parameter(name: 'date', description: 'Дата рейса, ГГГГ-ММ-ДД', in: 'query', example: '2024-03-20'),
        new OA\Parameter(name: 'excursion_ids[]', description: 'ID экскурсий', in: 'query',
            schema: new OA\Schema(type: "array", items: new OA\Items(type: "integer")))
    ],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 401, description: 'Not allowed'),
    ],
)]
class ApiGetTripsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'string|nullable',
            'excursion_ids' => 'array',
            'excursion_ids.*' => 'integer'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
