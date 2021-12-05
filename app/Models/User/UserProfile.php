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

    /**
     * User this profile belongs to.
     *
     * @return  BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
