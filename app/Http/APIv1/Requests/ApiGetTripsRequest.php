<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/trips',
    summary: 'Получить список рейсов на день',
    security: [["sanctum" => []]],
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
//            'start_at' => ['required', 'date'],
//            'end_at' => ['required', 'date'],
//            'start_pier_id' => ['required', 'integer'],
//            'end_pier_id' => ['required', 'integer'],
//            'ship_id' => ['required', 'integer'],
//            'excursion_id' => ['required', 'integer'],
//            'status_id' => ['required', 'integer'],
//            'sale_status_id' => ['required', 'integer'],
//            'tickets_total' => ['required', 'integer'],
//            'discount_status_id' => ['required', 'integer'],
//            'cancellation_time' => ['required', 'integer'],
//            'provider_id' => ['nullable', 'integer'],
//            'name' => ['nullable'],
//            'is_single_ticket' => ['required', 'integer'],
//            'reverse_excursion_id' => ['required', 'integer'],
//            'external_id' => ['nullable'],
//            'getTripStarts' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
