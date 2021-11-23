<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerDocuments extends Model
{
    /**
     * Partner owned this document.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
