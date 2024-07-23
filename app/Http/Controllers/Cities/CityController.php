<?php

namespace App\Http\Controllers\Cities;

use App\Http\Controllers\ApiController;
use App\Models\City;

class CityController extends ApiController
{
    public function kazan()
    {
        return view('kazan');
    }
}
