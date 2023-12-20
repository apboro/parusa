<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/ships',
    summary: 'Получить список теплоходов',
    security: [["sanctum" => []]],
    tags: ['Теплоходы'],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 401, description: 'Not allowed'),
    ],
)]
class ApiGetShipsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
//            'name' => ['required'],
//            'enabled' => ['nullable', 'boolean'],
//            'order' => ['nullable', 'integer'],
//            'status_id' => ['required', 'integer'],
//            'type_id' => ['nullable', 'integer'],
//            'owner' => ['required'],
//            'capacity' => ['required', 'integer'],
//            'description' => ['nullable'],
//            'external_id' => ['nullable'],
//            'label' => ['nullable'],
//            'provider_id' => ['nullable', 'integer'],
//            'ship_has_seats_scheme' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
