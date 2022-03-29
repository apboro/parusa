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
import NotFound from '@/Pages/NotFound';

export default [
    {path: '/', name: 'home', component: TripsListPage, meta: {title: 'Список рейсов'}},
    {path: '/settings', name: 'settings', component: SettingsPage, meta: {title: 'Настройки'}},
    {path: '/registry/orders', name: 'orders-registry', component: OrdersRegistryPage, meta: {title: 'Реестр заказов'}},
    {path: '/registry/orders/:id', name: 'order-info', component: OrderPage, meta: {title: 'Заказ'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: TicketsRegistryPage, meta: {title: 'Реестр билетов'}},
    {path: '/registry/tickets/:id', name: 'ticket-info', component: TicketInfoPage, meta: {title: 'Билет'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: ReservesRegistryPage, meta: {title: 'Реестр броней'}},
    {path: '/staff', name: 'staff-list', component: StaffListPage, meta: {title: 'Сотрудники'}},
    {path: '/staff/:id', name: 'staff-view', component: StaffViewPage, meta: {title: 'Просмотр сотрудника'}},
    {path: '/staff/:id/edit', name: 'staff-edit', component: StaffEditPage, meta: {title: 'Редактирование сотрудника'}},
    {path: '/partners', name: 'partners-list', component: PartnersListPage, meta: {title: 'Компании-партнёры'}},
    {path: '/partners/:id', name: 'partners-view', component: PartnerViewPage, meta: {title: 'Просмотр партнёра'}},
    {path: '/partners/:id/edit', name: 'partners-edit', component: PartnerEditPage, meta: {title: 'Редактирование партнёра'}},
    {path: '/representatives', name: 'representatives-list', component: RepresentativesListPage, meta: {title: 'Представители'}},
    {path: '/representatives/:id', name: 'representatives-view', component: RepresentativeViewPage, meta: {title: 'Просмотр представителя'}},
    {path: '/representatives/:id/edit', name: 'representatives-edit', component: RepresentativeEditPage, meta: {title: 'Редактирование представителя'}},
    {path: '/terminals', name: 'terminals-list', component: TerminalsListPage, meta: {title: 'Мобильные кассы'}},
    {path: '/terminals/:id', name: 'terminals-view', component: TerminalsViewPage, meta: {title: 'Просмотр мобильной кассы'}},
    {path: '/terminals/:id/edit', name: 'terminals-edit', component: TerminalsEditPage, meta: {title: 'Редактирование мобильной кассы'}},
    {path: '/piers', name: 'pier-list', component: PiersListPage, meta: {title: 'Причалы'}},
    {path: '/piers/:id', name: 'pier-view', component: PierViewPage, meta: {title: 'Просмотр причала'}},
    {path: '/piers/:id/edit', name: 'pier-edit', component: PierEditPage, meta: {title: 'Редактирование причала'}},
    {path: '/excursions', name: 'excursion-list', component: ExcursionsListPage, meta: {title: 'Каталог экскурсий'}},
    {path: '/excursions/:id', name: 'excursion-view', component: ExcursionViewPage, meta: {title: 'Просмотр экскурсии'}},
    {path: '/excursions/:id/edit', name: 'excursion-edit', component: ExcursionEditPage, meta: {title: 'Редактирование экскурсии'}},
    {path: '/trips', name: 'trip-list', component: TripsListPage, meta: {title: 'Список рейсов'}},
    {path: '/trips/:id', name: 'trip-view', component: TripViewPage, meta: {title: 'Просмотр рейса'}},
    {path: '/trips/:id/edit', name: 'trip-edit', component: TripEditPage, meta: {title: 'Редактирование рейса'}},
    {path: '/dictionaries', name: 'dictionaries', component: DictionariesPage, meta: {title: 'Справочники'}},
    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
