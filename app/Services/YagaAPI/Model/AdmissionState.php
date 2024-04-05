<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Sails\Trip;

class AdmissionState
{
    public static function getResource($grades, $ticketsCount): array
    {
        foreach ($grades as $grade) {
            $result[] = [
                "availableSeatCount" => $ticketsCount,
                "categoryId" => $grade->id,
            ];
        }

        return $result ?? [];
    }

}
