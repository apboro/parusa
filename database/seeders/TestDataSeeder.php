<?php

namespace Database\Seeders;

use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\Positions\PositionInfo;
use App\Models\Positions\StaffPositionInfo;
use App\Models\Sails\Excursion;
use App\Models\Sails\Pier;
use App\Models\Sails\Ship;
use App\Models\User\User;
use App\Models\User\UserProfile;
use Carbon\Carbon;
use Database\Seeders\Initial\ExcursionProgramsSeeder;
use Database\Seeders\Initial\PartnerTypesSeeder;
use Exception;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    protected array $seeders = [
        PartnerTypesSeeder::class,
        ExcursionProgramsSeeder::class,
    ];

    /**
     * Run the database seeds.
     *
     * @return  void
     *
     * @throws Exception
     */
    public function run(): void
    {
        foreach ($this->seeders as $seederClass) {

            /** @var GenericSeeder $seeder */
            $seeder = $this->container->make($seederClass);

            $seeder->run();
        }

        // Create staff users
        $staff = User::factory(30)
            ->afterCreating(function (User $user) {
                UserProfile::factory()->create(['user_id' => $user->id]);
                /** @var Position $position */
                $position = Position::factory()->create(['user_id' => $user->id, 'is_staff' => true]);
                StaffPositionInfo::factory()->create(['position_id' => $position->id]);
            })
            ->create();

        // Create partners with accounts
        $typeIds = AccountTransactionType::query()->where('final', 1)->pluck('id')->toArray();
        $staffIds = $staff->pluck('id')->toArray();

        $partners = Partner::factory(50)->afterCreating(function (Partner $partner) use ($typeIds, $staffIds) {
            $partner->account->save();
            $date = Carbon::today()->subDays(40);
            $partner->account->attachTransaction(new AccountTransaction([
                'type_id' => AccountTransactionType::account_refill_invoice,
                'amount' => 1000,
                'reason' => 'Оплата по счёту №1',
                'reason_date' => Carbon::now(),
                'committer_id' => $staffIds[random_int(0, count($staffIds) - 1)],
                'comments' => 'Начальное пополнение',
                'timestamp' => $date,
            ]));
            for ($i = 1; $i <= 30; $i++) {
                $date->addDay();
                $partner->account->attachTransaction(new AccountTransaction([
                    'type_id' => $typeIds[random_int(0, count($typeIds) - 1)],
                    'amount' => 10,
                    'reason' => "Тестовая операция №$i",
                    'reason_date' => $date,
                    'committer_id' => $staffIds[random_int(0, count($staffIds) - 1)],
                    'comments' => 'Тестовая операция',
                    'timestamp' => $date,
                ]));
            }
        })->create();

        // Create users
        User::factory(200)->afterCreating(function (User $user) {
            UserProfile::factory()->create(['user_id' => $user->id]);
        })->create();

        $partners = Partner::query()->pluck('id')->toArray();
        $partnersCount = count($partners);

        // Attach some users to organizations
        $users = User::query()->doesntHave('staffPosition')->get();
        $users->map(function (User $user) use ($partners, $partnersCount) {
            if (random_int(0, 100) > 10) {
                $pc = random_int(1, 3);
                for ($i = 1; $i <= $pc; $i++) {
                    $pid = random_int(1, $partnersCount);
                    /** @var Position $position */
                    $position = Position::factory()->create(['user_id' => $user->id, 'partner_id' => $pid]);
                    PositionInfo::factory()->create(['position_id' => $position->id]);
                }
            }
        });

        $ships = ['Антверпен', 'Аполлон', 'Атлант', 'Афродита', 'Барселона', 'Британия', 'Галактика', 'Гармония', 'Генерал', 'Кассандра'];
        foreach ($ships as $ship) {
            Ship::factory()->create(['name' => $ship]);
        }

        $piers = [
            ['name' => 'Медный всадник', 'address' => 'Санкт-Петербург, участок набережной реки Невы "пл. Декабристов - Благовещенский мост", часть спуска № 2'],
            ['name' => 'Адмиралтейство', 'address' => 'Санкт-Петербург, участок набережной реки Невы "Дворцовый мост - пл. Декабристов", лит. А, спуск № 2'],
            ['name' => 'Петропавловская крепость', 'address' => 'Санкт-Петербург, участок набережной Кронверкского пролива, в 90 метрах ниже по течению Кронверкского моста "Кронверкский мост - Биржевой мост", лит. А'],
            ['name' => 'Летний сад', 'address' => 'Санкт-Петербург, участок набережной реки Невы "Прачечный мост - Верхний Лебяжий мост", лит. Б,  спуск № 1'],
            ['name' => 'Петровская набережная', 'address' => 'Санкт-Петербург, участок набережной реки Невы и Большой Невки "Троицкий мост - Сампсониевский мост", лит. А, спуск № 2'],
            ['name' => 'Арсенальная набережная', 'address' => 'Санкт-Петербург, участок набережной реки Невы "Арсенальная улица - Литейный мост", лит. А, спуск № 5'],
            ['name' => 'Университетская набережная', 'address' => 'Санкт-Петербург, участок набережной реки Невы (в створе Менделеевской линии) "Дворцовый мост - Благовещенский мост", лит. А, спуск № 3 (30 м от края спуска в сторону Дворцового моста)'],
            ['name' => 'Приморский проспект', 'address' => 'Санкт-Петербург, участок набережной реки Большой Невки "3-й Елагин мост - Приморский проспект, д. 40", лит. А (спуск в 50 м от 3-го Елагина моста)'],
            ['name' => 'Набережная Мартынова', 'address' => 'Санкт-Петербург, участок набережной реки Крестовки и Средней Невки "Мало-Крестовский мост - 2-й Елагин мост", лит. А, в 78 м от 2-го Елагина моста'],
            ['name' => 'Юсуповский дворец', 'address' => 'Санкт-Петербург, участок набережной реки Мойки "Почтамтский мост - Поцелуев мост", лит. Б, спуск № 17'],
            ['name' => 'Конюшенное ведомство на Мойке', 'address' => 'Санкт-Петербург, участок набережной реки Мойки «Мало-Конюшенный мост – Б.Конюшенный мост», лит. Б, спуск № 19'],
            ['name' => 'Университет путей сообщения на Фонтанке', 'address' => 'Санкт-Петербург, участок набережной реки Фонтанки «Обуховский мост – Измайловский мост», лит А, спуск № 10(напротив дома №115 по наб. р.Фонтанки)'],
            ['name' => 'Набережная Макарова', 'address' => 'Санкт-Петербург, причал N 7 - набережная Макарова, д. 3, лит. Г5, сооружение 5'],
            ['name' => 'Южная дорога', 'address' => 'Санкт-Петербург, Южная дорога, д. 12, сооружение 1, лит. И'],
            ['name' => 'Петроградская набережная', 'address' => 'Санкт-Петербург, участок набережной реки Большой Невки "Сампсониевский мост - Гренадерский мост", лит. Б,  спуск № 3'],
            ['name' => 'Свердловская набережная', 'address' => 'Санкт-Петербург, участок набережной реки Невы "Малоохтинский мост - Арсенальная наб.", лит. А, спуск № 1 (напротив гостиницы "Охтинская")'],
            ['name' => 'Синопская набережная', 'address' => 'Санкт-Петербург, участок набережной реки Невы "мост Обуховской Обороны - Большеохтинский мост", лит. Б (симметрично от оси спуска №3, напротив дома 28 по Синопской набережной)'],
            ['name' => 'Речной вокзал', 'address' => 'Санкт-Петербург, пр.Обуховской Обороны, д.106 (сооружение 1, литера Б; литера В; сооружение 2, литера Е)'],
            ['name' => 'Смольная набережная', 'address' => 'Санкт-Петербург, участок набережной реки Невы, Смольная набережная, спуск в створе ул. Смольного. Спуск - 25 метров'],
        ];

        foreach ($piers as $pierData) {
            /** @var Pier $pier */
            $pier = Pier::factory()->create(['name' => $pierData['name']]);
            $pier->info()->create([
                'address' => $pierData['address'],
                'work_time' => random_int(8, 12) . ':00 - ' . random_int(19, 23) . ':00',
            ]);
        }

        $excursions = [
            ['name' => 'Прогулка по Неве с выходом в Финский залив'],
            ['name' => 'Вечерняя прогулка на теплоходе «Золотой час»'],
            ['name' => 'Экскурсия Северные острова с Финским заливом'],
            ['name' => 'Забытые острова с выходом в Финский залив'],
            ['name' => 'Экскурсия под развод мостов на теплоходе'],
            ['name' => 'Прогулка на теплоходе в Севкабель Порт'],
            ['name' => 'Музыкальный теплоход «Ночной Петербург»'],
            ['name' => 'Ночной круиз «Истории разводных мостов»'],
            ['name' => 'Северные острова, каналы и развод мостов'],
            ['name' => 'Ночной Петербург: «Тайны города на Неве»'],
        ];

        foreach ($excursions as $excursion) {
            Excursion::factory()->create($excursion);
        }
    }
}
