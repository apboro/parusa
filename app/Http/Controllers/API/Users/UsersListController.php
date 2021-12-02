<?php

namespace App\Http\Controllers\API\Users;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ApiListRequest;
use Illuminate\Http\JsonResponse;

class UsersListController extends ApiController
{
    /**
     * Get staff list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function staffList(ApiListRequest $request): JsonResponse
    {

    }

    /**
     * Get representatives list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function usersList(ApiListRequest $request): JsonResponse
    {

    }
}
