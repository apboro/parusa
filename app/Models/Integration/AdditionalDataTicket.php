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

}
