<?php

namespace App\Models;

use App\Models\Collections\PartnerTypeCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PartnerProfile extends Model
{
    use HasFactory;

    /**
     * The relations to eager load on every query.
     *
     * @var string[]
     */
    protected $with = ['type'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'partner_id';

    /**
     * Partner this profile belongs to.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Partner type relation to types collection.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PartnerTypeCollection::class, 'type', 'id');
    }
}
