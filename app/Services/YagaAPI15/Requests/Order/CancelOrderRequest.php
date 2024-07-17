<?php

namespace App\Services\YagaAPI15\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Post(path: '/api/yaga15/cancel-order',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent()
    ),
    tags: ['Заказ'])
]
#[OA\Response(response: '200', description: '')]
class CancelOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
        ];
    }


    protected function failedValidation(Validator $validator): bool
    {
        return false;
    }

}
