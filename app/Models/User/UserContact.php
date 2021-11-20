<?php

namespace App\Models\User;

use App\Models\Dictionaries\ContactType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property string title
 * @property string value
 * @property string|null additional
 * @property string|null comments
 * @property ContactType type
 * @property-read string|null link
 */
class UserContact extends Model
{
    /** @var string[] Relations eager loading. */
    protected $with = ['type'];

    /** @var array The accessors to append to the model's array. */
    protected $appends = ['link'];

    /**
     * Related contact class.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(ContactType::class);
    }

    /**
     * Accessor for contact link generation.
     *
     * @return  string|null
     */
    public function getLinkAttribute(): ?string
    {
        if (empty($this->type->link_pattern)) {
            return $this->value;
        }

        if ($this->type->has_additional) {
            return sprintf($this->type->link_pattern, $this->value, $this->additional);
        }

        return sprintf($this->type->link_pattern, $this->value);
    }
}
