<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProfile extends Model
{
    /** @var string The primary key associated with the table. */
    protected $primaryKey = 'partner_id';

    /** @var bool Disable auto-incrementing on model. */
    public $incrementing = false;

    /**
     * Partner this profile belongs to.
     *
     * @return  BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
