import TripsListPage from '@/Pages/Admin/Trips/TripsListPage';
import SettingsPage from '@/Pages/Admin/Settings/SettingsPage';
import OrdersRegistryPage from '@/Pages/Admin/Registries/OrdersRegistryPage';
import OrderPage from "@/Pages/Admin/Registries/OrderPage";
import TicketsRegistryPage from '@/Pages/Admin/Registries/TicketsRegistryPage';
import TicketInfoPage from "@/Pages/Admin/Registries/TicketInfoPage";
import ReservesRegistryPage from '@/Pages/Admin/Registries/ReservesRegistryPage';
import StaffListPage from '@/Pages/Admin/Staff/StaffListPage';
import StaffViewPage from '@/Pages/Admin/Staff/StaffViewPage';
import StaffEditPage from '@/Pages/Admin/Staff/StaffEditPage';
import PartnersListPage from '@/Pages/Admin/Partners/PartnersListPage';
import PartnerViewPage from '@/Pages/Admin/Partners/PartnerViewPage';
import PartnerEditPage from '@/Pages/Admin/Partners/PartnerEditPage';
import RepresentativesListPage from '@/Pages/Admin/Representatives/RepresentativesListPage';
import RepresentativeViewPage from '@/Pages/Admin/Representatives/RepresentativeViewPage';
import RepresentativeEditPage from '@/Pages/Admin/Representatives/RepresentativeEditPage';
import TerminalsListPage from '@/Pages/Admin/Terminals/TerminalsListPage';
import TerminalsViewPage from '@/Pages/Admin/Terminals/TerminalsViewPage';
import TerminalsEditPage from '@/Pages/Admin/Terminals/TerminalsEditPage';
import PiersListPage from '@/Pages/Admin/Piers/PiersListPage';
import PierViewPage from '@/Pages/Admin/Piers/PierViewPage';
import PierEditPage from '@/Pages/Admin/Piers/PierEditPage';
import ExcursionsListPage from '@/Pages/Admin/Excursions/ExcursionsListPage';
import ExcursionViewPage from '@/Pages/Admin/Excursions/ExcursionViewPage';
import ExcursionEditPage from '@/Pages/Admin/Excursions/ExcursionEditPage';
import TripViewPage from '@/Pages/Admin/Trips/TripViewPage';
import TripEditPage from '@/Pages/Admin/Trips/TripEditPage';
import DictionariesPage from '@/Pages/Admin/Dictionaries/DictionariesPage';
import TransactionsRegistryPage from "@/Pages/Admin/Registries/TransactionsRegistryPage";
import NotFound from '@/Pages/NotFound';
import StatisticsSalesPage from "@/Pages/Admin/Statistics/StatisticsSalesPage.vue";
import StatisticsSalesTodayPage from "@/Pages/Admin/Statistics/StatisticsSalesTodayPage.vue";
import StatisticsQrCodesPage from "@/Pages/Admin/Statistics/StatisticsQrCodesPage.vue";
import PromoCodeListPage from "@/Pages/Admin/PromoCode/PromoCodeListPage.vue";
import PromoCodeEditPage from "@/Pages/Admin/PromoCode/PromoCodeEditPage.vue";
import PromotersListPage from "@/Pages/Admin/Promoters/PromotersListPage.vue";
import PromoterEditPage from "@/Pages/Admin/Promoters/PromoterEditPage.vue";
import PromoterViewPage from "@/Pages/Admin/Promoters/PromoterViewPage.vue";
import PromotersRegistryPage from "@/Pages/Admin/Registries/PromotersRegistryPage.vue";
import HomePage from "@/Pages/HomePage.vue";
import ShipListPage from "@/Pages/Admin/Ships/ShipListPage.vue";
import ShipViewPage from "@/Pages/Admin/Ships/ShipViewPage.vue";
import ShipEditPage from "@/Pages/Admin/Ships/ShipEditPage.vue";
import NewsList from "@/Pages/Admin/News/NewsList.vue";
import NewsEditPage from "@/Pages/Admin/News/NewsEditPage.vue";
import NewsViewPage from "@/Pages/Admin/News/NewsViewPage.vue";

export default [
    {path: '/', name: 'home', component: HomePage, meta: {title: 'Главная', roles: ['admin', 'office_manager', 'piers_manager', 'accountant', 'promoter_manager']}},
    {path: '/settings', name: 'settings', component: SettingsPage, meta: {title: 'Настройки', roles: ['admin']}},
    {path: '/registry/orders', name: 'orders-registry', component: OrdersRegistryPage, meta: {title: 'Реестр заказов', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/registry/orders/:id', name: 'order-info', component: OrderPage, meta: {title: 'Заказ', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/registry/tickets', name: 'tickets-registry', component: TicketsRegistryPage, meta: {title: 'Реестр билетов', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/registry/tickets/:id', name: 'ticket-info', component: TicketInfoPage, meta: {title: 'Билет', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/registry/reserves', name: 'reserves-registry', component: ReservesRegistryPage, meta: {title: 'Реестр броней', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/registry/transactions', name: 'transactions-registry', component: TransactionsRegistryPage, meta: {title: 'Реестр транзакций по кассам', roles: ['admin', 'accountant']}},
    {path: '/registry/promoters', name: 'promoters-registry', component: PromotersRegistryPage, meta: {title: 'Реестр по промоутерам', roles: ['admin', 'accountant', 'promoter_manager']}},

    {path: '/statistics/sales', name: 'statistics-sales', component: StatisticsSalesPage, meta: {title: 'Статистика продаж', roles: ['admin']}},
    {path: '/statistics/sales-today', name: 'statistics-sales-today', component: StatisticsSalesTodayPage, meta: {title: 'Статистика продаж за сегодня', roles: ['admin']}},
    {path: '/statistics/qr_codes', name: 'statistics-qr-codes', component: StatisticsQrCodesPage, meta: {title: 'Статистика QR-коды', roles: ['admin']}},

    {path: '/ships', name: 'ship-list', component: ShipListPage, meta: {title: 'Теплоходы', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/ship/:id', name: 'ship-view', component: ShipViewPage, meta: {title: 'Просмотр теплохода', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/ship/:id/edit', name: 'ship-edit', component: ShipEditPage, meta: {title: 'Редактирование теплохода', roles: ['admin']}},

    {path: '/staff', name: 'staff-list', component: StaffListPage, meta: {title: 'Сотрудники', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/staff/:id', name: 'staff-view', component: StaffViewPage, meta: {title: 'Просмотр сотрудника', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/staff/:id/edit', name: 'staff-edit', component: StaffEditPage, meta: {title: 'Редактирование сотрудника', roles: ['admin']}},

    {path: '/partners', name: 'partners-list', component: PartnersListPage, meta: {title: 'Компании-партнёры', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/partners/:id', name: 'partners-view', component: PartnerViewPage, meta: {title: 'Просмотр партнёра', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/partners/:id/edit', name: 'partners-edit', component: PartnerEditPage, meta: {title: 'Редактирование партнёра', roles: ['admin', 'office_manager', 'accountant']}},

    {path: '/promoters', name: 'promoters-list', component: PromotersListPage, meta: {title: 'Промоутеры', roles: ['admin', 'office_manager', 'accountant', 'promoter_manager']}},
    {path: '/promoters/:id/edit', name: 'promoters-edit', component: PromoterEditPage, meta: {title: 'Редактирование промоутера', roles: ['admin', 'office_manager', 'accountant', 'promoter_manager']}},
    {path: '/promoters/:id', name: 'promoters-view', component: PromoterViewPage, meta: {title: 'Просмотр промоутера', roles: ['admin', 'office_manager', 'accountant', 'promoter_manager']}},

    {path: '/representatives', name: 'representatives-list', component: RepresentativesListPage, meta: {title: 'Представители', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/representatives/:id', name: 'representatives-view', component: RepresentativeViewPage, meta: {title: 'Просмотр представителя', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/representatives/:id/edit', name: 'representatives-edit', component: RepresentativeEditPage, meta: {title: 'Редактирование представителя', roles: ['admin', 'office_manager', 'accountant']}},

    {path: '/terminals', name: 'terminals-list', component: TerminalsListPage, meta: {title: 'Мобильные кассы', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/terminals/:id', name: 'terminals-view', component: TerminalsViewPage, meta: {title: 'Просмотр мобильной кассы', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/terminals/:id/edit', name: 'terminals-edit', component: TerminalsEditPage, meta: {title: 'Редактирование мобильной кассы', roles: ['admin', 'office_manager', 'accountant']}},

    {path: '/piers', name: 'pier-list', component: PiersListPage, meta: {title: 'Причалы/Остановки', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/piers/:id', name: 'pier-view', component: PierViewPage, meta: {title: 'Просмотр причала', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/piers/:id/edit', name: 'pier-edit', component: PierEditPage, meta: {title: 'Редактирование причала', roles: ['admin', 'office_manager']}},

    {path: '/excursions', name: 'excursion-list', component: ExcursionsListPage, meta: {title: 'Каталог экскурсий', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/excursions/:id', name: 'excursion-view', component: ExcursionViewPage, meta: {title: 'Просмотр экскурсии', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/excursions/:id/edit', name: 'excursion-edit', component: ExcursionEditPage, meta: {title: 'Редактирование экскурсии', roles: ['admin', 'office_manager', 'accountant']}},

    {path: '/promo-code', name: 'promo-code-list', component: PromoCodeListPage, meta: {title: 'Промокоды', roles: ['admin', 'office_manager', 'accountant']}},
    {path: '/promo-code/:id/edit', name: 'promo-code-edit', component: PromoCodeEditPage, meta: {title: 'Добавить промокод', roles: ['admin', 'office_manager', 'accountant']}},

    {path: '/trips', name: 'trip-list', component: TripsListPage, meta: {title: 'Список рейсов', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/trips/:id', name: 'trip-view', component: TripViewPage, meta: {title: 'Просмотр рейса', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},
    {path: '/trips/:id/edit', name: 'trip-edit', component: TripEditPage, meta: {title: 'Редактирование рейса', roles: ['admin', 'office_manager']}},

    {path: '/dictionaries', name: 'dictionaries', component: DictionariesPage, meta: {title: 'Справочники', roles: ['admin', 'office_manager', 'piers_manager', 'accountant', 'promoter_manager']}},

    {path: '/news', name: 'news-list', component: NewsList, meta: {title: 'Новости', roles: ['admin', 'office_manager']}},
    {path: '/news/:id', name: 'news-view', component: NewsViewPage, meta: {title: 'Просмотр новости', roles: ['admin', 'office_manager']}},
    {path: '/news/:id/edit', name: 'news-edit', component: NewsEditPage, meta: {title: 'Редактирование новости', roles: ['admin', 'office_manager']}},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},

];
