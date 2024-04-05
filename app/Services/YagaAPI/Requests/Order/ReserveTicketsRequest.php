<?php

namespace App\Services\YagaAPI\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Post(path: '/api/yaga/reserve',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            example: '{
  "created": string,
  "customer": {
    "email": "Yaga@baba.ru",
    "firstName": "Yaga",
    "lastName": "Baba",
    "phone": "867676767677"
  },
  "deliveryType": string,
  "eventId": "9",
  "hallId": "21",
  "id": string,
  "items": [
    {
      "admission": true,
      "categoryId": 1030,
      "cost": {
        "fee": {
          "currencyCode": "RUB",
          "value": 0
        },
        "price": {
          "currencyCode": "RUB",
          "value": "150"
        },
        "total": {
          "currencyCode": "RUB",
          "value": "150"
        }
      },
      "levelId": 17,
    }
  ],
  "orderNumber": string,
  "paymentType": string,
  "reservationTimeout": string,
  "sessionId": "181753",
  "sessionTime": {
    "sessionEnd": string,
    "sessionStart": string,
    "timezone": string,
    "type": string
  },
  "sum": {
    "fee": {
      "currencyCode": "RUB",
      "value": "0"
    },
    "price": {
      "currencyCode": "RUB",
      "value": "150"
    },
    "total": {
      "currencyCode": "RUB",
      "value": "150"
    }
  },
  "venueId": "21"
}'
        )
    ),
    tags: ['Заказ'])
]
#[OA\Response(response: '200', description: '')]
class ReserveTicketsRequest extends FormRequest
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
