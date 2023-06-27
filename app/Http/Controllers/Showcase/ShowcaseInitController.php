<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;

class ShowcaseInitController extends ApiController
{
    /**
     * Initial data for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function init(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $originalKey = [
            'ip' => $request->ip(),
            'user-agent' => $request->userAgent(),
            'is_partner' => $originalKey['is_partner'] ?? $request->input('is_partner'),
            'partner_id' => $originalKey['partner'] ?? $request->input('partner_id'),
            'media' => $originalKey['media'] ?? $request->input('media'),
        ];

        $programs = ExcursionProgram::query()->where(['enabled' => true])->select(['id', 'name'])->orderBy('name')->get();

        return response()->json([
            'today' => Carbon::now()->format('Y-m-d'),
            'programs' => $programs->toArray(),
            'date_from' => Carbon::now()->format('Y-m-d'),
            'date_to' => null,
        ], 200, [ExternalProtect::HEADER_NAME => Crypt::encrypt(json_encode($originalKey, JSON_THROW_ON_ERROR))]);
    }
}
