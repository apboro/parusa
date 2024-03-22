<?php

namespace App\Services\YagaAPI\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/yaga/venues',
    tags: ['Расписание'],
    parameters: [
        new OA\Parameter(name: 'offset', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'venueId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'cityId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'updatedAfter', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])
]
#[OA\Response(response: '200', description: '')]
class GetVenuesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'offset' => 'integer',
            'limit' => 'integer',
            'venueId' => 'integer|exists:ships,id',
            'cityId' => 'integer',
            'updatedAfter' => 'integer'
        ];
    }

}
