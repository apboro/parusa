<?php

namespace App\Http\Controllers\API;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class NotFoundController extends ApiController
{
    public function notFound(): JsonResponse
    {
        return APIResponse::notFound();
    }
}
