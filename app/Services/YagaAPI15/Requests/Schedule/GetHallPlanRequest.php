<?php

namespace App\Services\YagaAPI15\Requests\Schedule;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(path: '/api/yaga/hallplan',
    tags: ['Расписание'],
    parameters: [
        new OA\Parameter(name: 'sessionId', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
    ])
]
#[OA\Response(response: '200', description: '')]
class GetHallPlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sessionId' => 'integer'
        ];
    }

    protected function failedValidation(Validator $validator): bool
    {
        return false;
    }
}
