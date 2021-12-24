<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $partner_id
 * @property int $tickets_for_guides
 * @property bool $can_reserve_tickets
 * @property string $notes
 */
class PartnerProfile extends Model
{
    /** @var string The primary key associated with the table. */
    protected $primaryKey = 'partner_id';

    /** @var bool Disable auto-incrementing on model. */
    public $incrementing = false;

    /** @var array Default attributes */
    protected $attributes = [
        'tickets_for_guides' => 0,
        'can_reserve_tickets' => true,
    ];
}
