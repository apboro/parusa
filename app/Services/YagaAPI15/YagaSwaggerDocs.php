<?php

namespace App\Services\YagaAPI15;

use FontLib\Table\Type\name;
use OpenApi\Attributes as OA;

#[OA\OpenApi(
    security: [["sanctum" => []]],
)]
#[OA\Info(
    version: '1.0.0',
    description: 'Точки для Яндекс Афишы 15%',
    title: 'API компании "Алые паруса" для Яндекс Афишы',
)]
#[OA\Tag(name: 'Расписание')]
#[OA\Tag(name: 'Заказ')]
class YagaSwaggerDocs
{

}
