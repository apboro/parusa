<?php

namespace App\Models\Hit;

use App\Models\Model;
use Carbon\Carbon;
use DateTime;

/**
 * @property int $id
 * @property int $source_id
 * @property int $count
 * @property DateTime $timestamps
 */
class Hit extends Model
{
    protected $fillable = ['id', 'source_id', 'count', 'timestamp'];

    /** @var array Attribute casting */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public static function register($hitSourceId): void
    {
        $now = Carbon::now();
        $minutes = $now->minute;
        $remainder = $minutes % 10;
        if ($remainder >= 5) {
            $remainder = 10 - $remainder;
        }
        $rounded = $now->subMinutes($remainder)->second(0)->millisecond(0);

        $hit = self::where('timestamp', $rounded)
            ->where('source_id', $hitSourceId)
            ->first();

        if (isset($hit)) {
            $hit->count = $hit->count + 1;
        } else {
            $hit = new Hit();
            $hit->count = 1;
            $hit->source_id = $hitSourceId;
            $hit->timestamp = $rounded;
        }
        $hit->save();

    }
}
