<?php

namespace App\Models\User;

use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $lastname
 * @property string $firstname
 * @property string $patronymic
 * @property string $gender
 * @property Carbon $birthdate
 *
 * @property User $user
 *
 * @property-read string|null $fullName
 * @property-read string|null $compactName
 */
class UserProfile extends Model
{
    use HasFactory;

    /** @var string The primary key associated with the table. */
    protected $primaryKey = 'user_id';

    /** @var bool Disable auto-incrementing on model. */
    public $incrementing = false;

    /** @var string[] Relations eager loading. */
    protected $with = ['user'];

    /** @var string[] Attributes casting */
    protected $casts = [
        'birthdate' => 'date',
    ];

    /** @var array The accessors to append to the model's array. */
    protected $appends = ['fullName', 'compactName'];

    /**
     * User this profile belongs to.
     *
     * @return  BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for full name generation.
     *
     * @return  string|null
     */
    public function getFullNameAttribute(): ?string
    {
        return $this->fullName();
    }

    /**
     * Accessor for compact name generation.
     *
     * @return  string|null
     */
    public function getCompactNameAttribute(): ?string
    {
        return $this->compactName();
    }

    /**
     * Return lastname.
     *
     * @return  string|null
     */
    public function lastName(): ?string
    {
        return $this->format('lastname', true);
    }

    /**
     * Return firstname in full or short mode.
     *
     * @param bool $full
     *
     * @return  string|null
     */
    public function firstName(bool $full = true): ?string
    {
        return $this->format('firstname', $full);
    }

    /**
     * Return fathers name in full or short mode.
     *
     * @param bool $full
     *
     * @return  string|null
     */
    public function patronymic(bool $full = true): ?string
    {
        return $this->format('patronymic', $full);
    }

    /**
     * Return fathers name in full or short mode.
     *
     * @return  string|null
     */
    public function fullName(): ?string
    {
        $value = str_replace('  ', ' ', trim(sprintf('%s %s %s', $this->lastName(), $this->firstName(), $this->patronymic())));

        return empty($value) ? null : $value;
    }

    /**
     * Return fathers name in full or short mode.
     *
     * @return  string|null
     */
    public function compactName(): ?string
    {
        $value = trim(sprintf('%s %s%s', $this->lastName(), $this->firstName(false), $this->patronymic(false)));

        return empty($value) ? null : $value;
    }

    /**
     * Format string in full or short mode.
     *
     * @param bool $full
     * @param string|null $attribute
     *
     * @return  string|null
     */
    protected function format(?string $attribute, bool $full): ?string
    {
        $value = $this->getAttribute($attribute);
        if (empty($value)) {
            return null;
        }

        return $full ? mb_strtoupper(mb_substr($value, 0, 1)) . mb_strtolower(mb_substr($value, 1)) : mb_strtoupper(mb_substr($value, 0, 1)) . '.';
    }
}
