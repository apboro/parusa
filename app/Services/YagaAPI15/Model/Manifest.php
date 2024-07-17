<?php

namespace App\Services\YagaAPI15\Model;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class Manifest
{
    protected string $citiesUpdateTime;
    protected string $venuesUpdateTime;
    protected string $eventsUpdateTime;
    protected string $hallsUpdateTime;
    protected string $schedulesUpdateTime;

    public function __construct(array $data = null)
    {
        $this->citiesUpdateTime = $data['citiesUpdateTime'] ?? now()->toDateTimeString();
        $this->venuesUpdateTime = $data['venuesUpdateTime'] ?? now()->toDateTimeString();
        $this->eventsUpdateTime = $data['eventsUpdateTime'] ?? now()->toDateTimeString();
        $this->hallsUpdateTime = $data['hallsUpdateTime'] ?? now()->toDateTimeString();
        $this->schedulesUpdateTime = $data['schedulesUpdateTime'] ?? now()->toDateTimeString();
    }

    public function getResource()
    {
        return response()->json([
            "manifest" => [
                "citiesUpdateTime" => $this->citiesUpdateTime,
                "eventsUpdateTime" => $this->eventsUpdateTime,
                "hallsUpdateTime" => $this->hallsUpdateTime,
                "schedulesUpdateTime" => $this->schedulesUpdateTime,
                "venuesUpdateTime" => $this->venuesUpdateTime
            ]]);
    }

}


