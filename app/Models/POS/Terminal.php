<?php

namespace App\Models\POS;

use App\Exceptions\POS\WrongTerminalStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\Model;
use App\Models\Piers\Pier;
use App\Models\Positions\Position;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $status_id
 * @property string $workplace_id
 * @property string $outlet_id
 * @property string $organization_id
 * @property int $pier_id
 * @property bool $show_all_orders
 *
 * @property-read string $name
 * @property TerminalStatus $status
 * @property Pier $pier
 * @property Collection $staff
 */
class Terminal extends Model implements Statusable
{
    use HasStatus;

    /** @var array Attributes casting. */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'show_all_orders' => 'bool',
    ];

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => TerminalStatus::default,
        'show_all_orders' => false
    ];

    /**
     * Accessor for name generation.
     *
     * @return  string|null
     */
    public function getNameAttribute(): ?string
    {
        return $this->exists ? 'Касса №' . $this->id : null;
    }

    /**
     * Ship status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(TerminalStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for ship.
     *
     * @param int|TerminalStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTerminalStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(TerminalStatus::class, $status, WrongTerminalStatusException::class, $save);
    }

    /**
     * Pier this terminal assigned.
     *
     * @return  HasOne
     */
    public function pier(): HasOne
    {
        return $this->hasOne(Pier::class, 'id', 'pier_id');
    }

    /**
     * Staff can use this terminal.
     *
     * @return  BelongsToMany
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'terminal_users', 'terminal_id', 'position_id');
    }
}

