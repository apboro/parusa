<?php

namespace App\Models\User;

use App\Models\Dictionaries\ContactType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $title
 * @property string $value
 * @property string|null $additional
 * @property string|null $comments
 * @property ContactType $type
 * @property-read string|null $formatted
 */
class UserContact extends Model
{
    /** @var string[] Relations eager loading. */
    protected $with = ['type'];

    /** @var array The accessors to append to the model's array. */
    protected $appends = ['formatted'];

    /**
     * Related contact class.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(ContactType::class, 'type_id', 'id');
    }

    /**
     * Accessor for contact link generation.
     *
     * @return  string|null
     */
    public function getFormattedAttribute(): ?string
    {
        $pattern = $this->type->link_pattern;
        $value = $this->value;

        if (empty($pattern) || empty($value)) {
            return null;
        }

        return sprintf($pattern, $value, $this->additional);
    }
}
