<?php

namespace App\Http\Controllers\Cities;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ExcursionResource;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;

class CityController extends ApiController
{
    public function kazan()
    {
        return view('kazan');
    }

    public function get()
    {
        $excursions = Excursion::where('city_id', 2)->get();
        $programIds = $excursions->flatMap(function (Excursion $excursion) {
            return $excursion->programs->flatMap(function ($program) {
                return $program->pluck('id');
            });
        })->unique();

        $programsForCity = ExcursionProgram::whereIn('id', $programIds);
        $pierIds = Trip::whereHas('excursion', function ($query) {
            $query->where('city_id', 2);
        })->pluck('start_pier_id')->unique()->values()->toArray();

        $piers = Pier::whereIn('id', $pierIds)->get();
        return APIResponse::response([
            'excursions' => ExcursionResource::collection($excursions),
            'programs' => $programsForCity,
            'piers' => $piers,
        ]);
    }
}
