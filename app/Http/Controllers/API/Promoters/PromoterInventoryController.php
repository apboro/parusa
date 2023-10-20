<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\Inventory;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\Request;

class PromoterInventoryController extends Controller
{
    public function get(Partner $promoter)
    {
        $inventory = Inventory::all();
        $promoterInventory = $promoter->inventory()->pluck('inventory_item_id');

        return APIResponse::response(['inventory' => $inventory, 'promoterInventory' => $promoterInventory]);
    }

    public function store(Request $request)
    {
        $promoter = Partner::findOrFail($request->input('promoterId'));
        $promoter->inventory()->delete();

        $inventoryIds = $request->input('promoterInventory');
        foreach ($inventoryIds as $inventoryId) {
            $promoter->inventory()->create(['inventory_item_id' => $inventoryId]);
        }

        return APIResponse::success('Список сохранен');
    }

    public function getForPromotersPage(Request $request)
    {
        return APIResponse::response(['inventory' => Currents::get($request)->partner()->inventory]);
    }
}
