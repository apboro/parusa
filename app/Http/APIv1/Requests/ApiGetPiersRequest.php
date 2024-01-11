<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/piers',
    description: 'Запрос служит для получения списка всех активных причалов системы',
    summary: 'Получить список причалов',
    tags: ['Причалы'],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 401, description: 'Not allowed'),
    ],
)]
class ApiGetPiersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
//            'name' => ['required'],
//            'status_id' => ['required', 'integer'],
//            'external_id' => ['nullable'],
//            'external_parent_id' => ['nullable'],
//            'provider_id' => ['required', 'exists:dictionary_providers'],
//            'source' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
