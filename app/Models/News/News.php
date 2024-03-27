<?php

namespace App\Models\News;

use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\NewsStatus;
use App\Models\NewsRecipients;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class News extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['send_at'];

    public function status(): HasOne
    {
        return $this->hasOne(NewsStatus::class, 'id', 'status_id');
    }

    public function recipients(): HasOne
    {
        return $this->hasOne(NewsRecipients::class, 'id', 'recipients_id');
    }

    public function scopeSent(Builder $query)
    {
        return $query->where('status_id', NewsStatus::SENT);
    }
}
