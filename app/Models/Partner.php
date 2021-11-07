<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Partner extends Model
{
    use HasFactory;

    /**
     * Partner profile.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    /**
     * Loaded partner's documents.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PartnerDocuments::class);
    }

    /**
     * All active agents of this partner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'partner_has_agent', 'partner_id', 'agent_id')
            ->withPivot('position')
            ->wherePivot('active', true);
    }

    /**
     * All agents of this partner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allAgents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'partner_has_agent', 'partner_id', 'agent_id')
            ->withPivot(['active', 'position']);
    }
}
