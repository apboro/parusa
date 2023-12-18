<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/excursions',
    summary: 'Получить список экскурсий',
    security: [["sanctum" => []]],
    tags: ['Экскурсии'],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 401, description: 'Not allowed'),
    ],
)]
class ApiGetExcursionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
