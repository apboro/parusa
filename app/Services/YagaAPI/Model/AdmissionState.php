<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Sails\Trip;

class AdmissionState
{
    public static function getResource($grades, $ticketsCount): array
    {
        $result = [];
        if ($ticketsCount <= 0) {
            foreach ($grades as $grade) {
                $result[] = [
                        "availableSeatCount" => 0,
                        "categoryId" => $grade->id,
                    ];
            }
        }

        return $result;
    }

}
