<?php

namespace App\Models\Partner;

use App\Models\Dictionaries\Inventory;
use Illuminate\Database\Eloquent\Model;

class PromoterInventory extends Model
{
    protected $table = 'promoter_inventory';
    protected $guarded = [];

    public function dictionary()
    {
        return $this->hasOne(Inventory::class, 'id', 'inventory_item_id');
    }
}
