<?php

namespace App\Models\Sails;

use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property Collection $trips
 */
class TripChain extends Model
{
    /**
     * Chained trips.
     *
     * @return  BelongsToMany
     */
    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_chain_has_trip', 'chain_id', 'trip_id');
    }
}
