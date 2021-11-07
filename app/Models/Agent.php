<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Agent's profile.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(AgentProfile::class);
    }

    /**
     * All active agents of this partner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_has_agent', 'agent_id', 'partner_id')
            ->withPivot('position')
            ->wherePivot('active', true);
    }

    /**
     * All agents of this partner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allPartners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_has_agent', 'agent_id', 'partner_id')
            ->withPivot(['active', 'position']);
    }
}
