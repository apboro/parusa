<?php

namespace App\Models\Hit;

use App\Models\Model;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $source_id
 * @property int $count
 * @property DateTime $timestamp
 */
class Hit extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'source_id', 'count', 'timestamp'];

    /** @var array Attribute casting */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public static function register($hitSourceId): void
    {
        try {
            $now = Carbon::now();
            $rounded = $now->minute(floor($now->minute / 10) * 10)->second(0)->millisecond(0);

            /** @var Hit|null $hit */
            $hit = self::query()
                ->where('timestamp', $rounded)
                ->where('source_id', $hitSourceId)
                ->first();

            if ($hit !== null) {
                $hit->count++;
            } else {
                $hit = new Hit();
                $hit->count = 1;
                $hit->source_id = $hitSourceId;
                $hit->timestamp = $rounded;
            }
            $hit->save();
        } catch (Exception $exception) {
            Log::error('Hit registering error: ' . $exception->getMessage());
        }
    }
}
