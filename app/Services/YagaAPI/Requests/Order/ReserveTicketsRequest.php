<?php

namespace App\Services\YagaAPI\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Post(path: '/api/yaga/reserve',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            example: '{
  "additional": {},
  "codeWord": string,
  "created": string,
  "customer": {
    "email": string,
    "firstName": string,
    "lastName": string,
    "phone": string
  },
  "deliveryType": string,
  "eventId": string,
  "hallId": string,
  "id": string,
  "items": [
    {
      "admission": boolean,
      "categoryId": string,
      "cost": {
        "fee": {
          "currencyCode": string,
          "value": string
        },
        "price": {
          "currencyCode": string,
          "value": string
        },
        "total": {
          "currencyCode": string,
          "value": string
        }
      },
      "levelId": string,
      "seat": {
        "fragment": string,
        "id": string,
        "place": string,
        "row": string,
        "x": integer,
        "y": integer
      },
      "seatId": string,
      "supplementType": string
    }
  ],
  "orderNumber": string,
  "paymentType": string,
  "promocode": string,
  "reservationTimeout": string,
  "sessionId": string,
  "sessionTime": {
    "sessionEnd": string,
    "sessionStart": string,
    "timezone": string,
    "type": string
  },
  "specificFields": {},
  "sum": {
    "fee": {
      "currencyCode": string,
      "value": string
    },
    "price": {
      "currencyCode": string,
      "value": string
    },
    "total": {
      "currencyCode": string,
      "value": string
    }
  },
  "venueId": string
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
