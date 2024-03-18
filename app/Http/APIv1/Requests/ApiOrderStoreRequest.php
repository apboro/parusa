<?php

namespace App\Http\APIv1\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/order',
    description: 'Запрос служит для создания резерва заказа.
<br>Входные параметры:
<ul>
 <li>trip_id - id рейса, integer</li>
 <li>tickets - массив объектов:<br>
  grade_id - категория билета, integer<br>
  seat_id - id места, integer (может отсутствовать в запросе, если на рейсе не предусмотрена рассадка)
  </li>

</ul>',
    summary: 'Запрос заказа',
    tags: ['Заказы']
)]
#[OA\RequestBody(
    content: [
        new OA\JsonContent(
            properties: [
                new OA\Property(property: 'client_name', type: 'string', example: 'John'),
                new OA\Property(property: 'client_email', type: 'string', example: 'john@api.ru'),
                new OA\Property(property: 'client_phone', type: 'string', example: '+7(950)654-55-55'),
                new OA\Property(property: 'trip_id', type: 'integer', example: 189556),
                new OA\Property(property: 'tickets', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'grade_id', type: 'integer', example: 1003),
                        new OA\Property(property: 'seat_id', type: 'integer', example: null, nullable: true),
                    ]
                ))
            ]
        )
    ]
)]
#[OA\Response(response: 200, description: 'OK')]
#[OA\Response(response: 401, description: 'Not allowed')]

class ApiOrderStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_name' => ['nullable'],
            'client_email' => ['nullable', 'email', 'max:254'],
            'client_phone' => ['nullable'],
            'trip_id' => ['required'],
            'tickets' => ['array'],
        ];
    }
}
