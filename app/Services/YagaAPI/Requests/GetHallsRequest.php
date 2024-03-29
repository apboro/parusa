<?php

namespace App\Services\YagaAPI\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/yaga/halls',
    tags: ['Расписание'],
    parameters: [
        new OA\Parameter(name: 'offset', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'venueId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'hallId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'hallId[]', in: 'query', required: false, schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string'))),
        new OA\Parameter(name: 'updatedAfter', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])
]
#[OA\Response(response: '200', description: '')]
class GetHallsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'offset' => 'integer',
            'limit' => 'integer',
            'venueId' => 'integer',
            'updatedAfter' => 'integer'
        ];
    }

    protected function failedValidation(Validator $validator): bool
    {
        return false;
    }
}
