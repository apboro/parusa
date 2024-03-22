<?php

namespace App\Services\YagaAPI;

use FontLib\Table\Type\name;
use OpenApi\Attributes as OA;

#[OA\OpenApi(
    security: [["sanctum" => []]],
)]
#[OA\Info(
    version: '1.0.0',
    description: 'Точки для Яндекс Афишы',
    title: 'API компании "Алые паруса" для Яндекс Афишы',
)]
#[OA\Tag(name: 'Расписание')]
class YagaSwaggerDocs
{

}
