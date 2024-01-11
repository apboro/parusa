<?php

namespace App\Http\APIv1;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Резерв на билеты действует 15 минут.',
    title: 'API Алые паруса',
)]
#[OA\Tag(name: 'Рейсы')]
class SwaggerDocs
{

}
