<?php

namespace App\Console\Commands;

use App\Models\Dictionaries\PartnerType;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ChangePromotersTariffCommand extends Command
{
    protected $signature = 'change:promoters-tariff';

    protected $description = 'В 11:00 меняет промоутерам тарифы на индивидуальные';

    public function handle(): void
    {
        $promoters = Partner::query()
            ->active()
            ->promoter()
            ->hasOpenedShift()
            ->hasAutoChangeTariffTrue()
            ->get();

        foreach ($promoters as $promoter){
            try {
                $openedShift = $promoter->getOpenedShift();
                $openedShift->tariff_id = $promoter->tariff()->first()->id;
                $openedShift->save();
            } catch (\Exception $e) {
                Log::error($e);
            }
        }
    }
}
