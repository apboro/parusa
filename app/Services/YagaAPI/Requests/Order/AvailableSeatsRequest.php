<?php

namespace App\Services\YagaAPI\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/yaga/available-seats',
    tags: ['Заказ'],
    parameters: [
        new OA\Parameter(name: 'sessionId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'venueId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'eventId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'hallId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'sessionTime', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])
]
#[OA\Response(response: '200', description: '')]
class AvailableSeatsRequest extends FormRequest
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
