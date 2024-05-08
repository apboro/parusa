<?php

namespace App\Models\Integration;

use App\Helpers\PriceConverter;
use App\Models\Ships\Menu;
use Illuminate\Database\Eloquent\Model;

class AdditionalDataTicket extends Model
{
    protected $guarded = [];

    protected $table = 'additional_data_tickets';

    public $timestamps = false;

    public function menu()
    {
        return $this->hasOne(Menu::class, 'id', 'menu_id');
    }

    /**
     * Convert base_price from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getPenaltySumAttribute(?int $value): ?float
    {
        return $value !== null ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert base_price to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setPenaltySumAttribute(?float $value): void
    {
        $this->attributes['penalty_sum'] = $value !== null ? PriceConverter::priceToStore($value) : null;
    }

}
