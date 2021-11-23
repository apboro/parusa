<?php

namespace App\Models\User;

use App\Exceptions\User\WrongUserContactTypeException;
use App\Models\Dictionaries\UserContactType;
use App\Models\Model;
use App\Models\Traits\HasType;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $title
 * @property string $value
 * @property string|null $additional
 * @property string|null $comments
 * @property UserContactType $type
 * @property-read string|null $formatted
 */
class UserContact extends Model
{
    use HasType;

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
        return $this->hasOne(UserContactType::class, 'type_id', 'id');
    }

    /**
     * Check and set type of contact.
     *
     * @param int $typeId
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongUserContactTypeException
     */
    public function setType(int $typeId, bool $save = true): void
    {
        $this->checkAndSetType(UserContactType::class, $typeId, WrongUserContactTypeException::class, $save);
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
