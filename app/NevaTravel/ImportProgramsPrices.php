<?php

namespace App\NevaTravel;


use App\Http\APIResponse;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Excursions\ExcursionInfo;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;


class ImportProgramsPrices
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaPrograms = $nevaApiData->getProgramsInfo();
        $this->addOrUpdateGradesInDictionary();
        foreach ($nevaPrograms['body'] as $nevaProgram) {
            $excursion = Excursion::where('external_id', $nevaProgram)->first();

            $ratesList = $this->createOrUpdateRateList($excursion);

            $prices = $nevaProgram['prices_table'][0]['default_price'];

            $grades = TicketGrade::whereIn('id', TicketGrade::neva_grades_array)->pluck('external_grade_name', 'id');

            foreach ($grades as $grade_id => $grade_name) {
                TicketRate::updateOrCreate(
                    [
                        'rate_id' => $ratesList->id,
                        'grade_id' => $grade_id,
                    ],
                    [
                        'base_price' => $prices[$grade_name]['price']/100,
                        'min_price' => $prices[$grade_name]['price']/100,
                        'max_price' => $prices[$grade_name]['price']/100,
                        'commission_type' => 'fixed',
                        'commission_value' => 100,
                        'site_price' => $prices[$grade_name]['price']/100,
                    ]
                );
            }
        }
    }

    public function addOrUpdateGradesInDictionary()
    {
        $grades = [
            ['external_name' =>'full',
            'id'=> TicketGrade::neva_full,
            'inner_name' => 'Полный'
            ],
            ['external_name' =>'child',
            'id'=> TicketGrade::neva_child,
            'inner_name' => 'Детский (3-10 лет)'
            ],
            ['external_name' =>'infant',
            'id'=> TicketGrade::neva_infant,
            'inner_name' => 'Десткий (до 3 лет)'
            ],
            ['external_name' =>'privileged',
            'id'=> TicketGrade::neva_privileged,
            'inner_name' => 'Льготный'
            ],
            ['external_name' =>'attendant',
            'id'=> TicketGrade::neva_attendant,
            'inner_name' => 'Гид'
            ],
        ];
        foreach ($grades as $grade) {
            TicketGrade::updateOrCreate(['id' => $grade['id']],
                [
                    'name' => $grade['inner_name'],
                    'enabled' => 1,
                    'locked' => 1,
                    'external_grade_name' => $grade['external_name']
                ]);
        }
    }

    public function createOrUpdateRateList($excursion): TicketsRatesList
    {
        return TicketsRatesList::updateOrCreate(
            ['excursion_id' => $excursion->id],
            [
                'start_at' => now()->format('Y-m-d'),
                'end_at' => now()->addDays(90)->format('Y-m-d')
            ]);
    }

}
