<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Patch(
    path: '/api/v1/order/return',
    summary: 'Возврат заказа',
    security: [["sanctum" => []]],
    tags: ['Заказы']
)]
#[OA\RequestBody(
    content: [
        new OA\JsonContent(
            properties: [
                new OA\Property(property: 'order_id', type: 'integer', example: 85627),
            ]
        )
    ]
)]
#[OA\Response(response: 200, description: 'OK')]
#[OA\Response(response: 401, description: 'Not allowed')]


class ApiOrderReturnRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => 'required|numeric'
        ];
    }
}
