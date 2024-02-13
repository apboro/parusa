<?php

namespace App\Models\Dictionaries;

use App\Models\Ships\Menu;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property bool $preferential
 * @property int $order
 */
class TicketGrade extends AbstractDictionary
{
    use HasFactory;
    protected $guarded = [];
    /** @var int The id of guide ticket grade */
    public const guide = 1;

    /** @var int The id of adult ticket grade */
    public const adult = 2;
    public const neva_full = 50;
    public const neva_privileged = 52;
    public const neva_child = 54;
    public const neva_infant = 56;
    public const neva_attendant = 58;
    public const showcaseDisplayPrice = [50, 2];
    public const neva_grades_array = [
        self::neva_full,
        self::neva_privileged,
        self::neva_child,
        self::neva_infant,
        self::neva_attendant
        ];

    /** @var string Referenced table */
    protected $table = 'dictionary_ticket_grades';

    /** @var string[] Attributes casting */
    protected $casts = [
        'enabled' => 'boolean',
        'locked' => 'boolean',
        'preferential' => 'boolean',
        'order' => 'int',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'grade_has_menus', 'grade_id', 'menu_id');
    }
}
