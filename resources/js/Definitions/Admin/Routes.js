import NotFound from "../../Pages/NotFound";

import SettingsPage from "../../Pages/Admin/Settings/SettingsPage";

import DictionariesPage from "../../Pages/Admin/Dictionaries/DictionariesPage";

import TestPage from "@/Pages/Admin/TestPage";

export default [
    {path: '/', name: 'home', component: () => import('@/Pages/Admin/Trips/TripsListPage'), meta: {title: 'Список рейсов'}},

    {path: '/settings', name: 'settings', component: SettingsPage, meta: {title: 'Настройки'}},

    {path: '/registry/orders', name: 'orders-registry', component: () => import('@/Pages/Admin/Registries/OrdersRegistryPage'), meta: {title: 'Реестр заказов'}},
    {path: '/registry/orders/:id', name: 'order-info', component: () => import("@/Pages/Admin/Registries/OrderPage"), meta: {title: 'Заказ'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: () => import('@/Pages/Admin/Registries/TicketsRegistryPage'), meta: {title: 'Реестр билетов'}},
    {path: '/registry/tickets/:id', name: 'ticket-info', component: () => import("@/Pages/Admin/Registries/TicketInfoPage"), meta: {title: 'Билет'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: () => import('@/Pages/Admin/Registries/ReservesRegistryPage'), meta: {title: 'Реестр броней'}},

    {path: '/staff', name: 'staff-list', component: () => import('@/Pages/Admin/Staff/StaffListPage'), meta: {title: 'Сотрудники'}},
    {path: '/staff/:id', name: 'staff-view', component: () => import('@/Pages/Admin/Staff/StaffViewPage'), meta: {title: 'Просмотр сотрудника'}},
    {path: '/staff/:id/edit', name: 'staff-edit', component: () => import('@/Pages/Admin/Staff/StaffEditPage'), meta: {title: 'Редактирование сотрудника'}},

    {path: '/partners', name: 'partners-list', component: () => import('@/Pages/Admin/Partners/PartnersListPage'), meta: {title: 'Компании-партнёры'}},
    {path: '/partners/:id', name: 'partners-view', component: () => import('@/Pages/Admin/Partners/PartnerViewPage'), meta: {title: 'Просмотр партнёра'}},
    {path: '/partners/:id/edit', name: 'partners-edit', component: () => import('@/Pages/Admin/Partners/PartnerEditPage'), meta: {title: 'Редактирование партнёра'}},

    {path: '/representatives', name: 'representatives-list', component: () => import('@/Pages/Admin/Representatives/RepresentativesListPage'), meta: {title: 'Представители'}},
    {path: '/representatives/:id', name: 'representatives-view', component: () => import('@/Pages/Admin/Representatives/RepresentativeViewPage'), meta: {title: 'Просмотр представителя'}},
    {path: '/representatives/:id/edit', name: 'representatives-edit', component: () => import('@/Pages/Admin/Representatives/RepresentativeEditPage'), meta: {title: 'Редактирование представителя'}},

    {path: '/terminals', name: 'terminals-list', component: () => import('@/Pages/Admin/Terminals/TerminalsListPage'), meta: {title: 'Мобильные кассы'}},
    {path: '/terminals/:id', name: 'terminals-view', component: () => import('@/Pages/Admin/Terminals/TerminalsViewPage'), meta: {title: 'Просмотр мобильной кассы'}},
    {path: '/terminals/:id/edit', name: 'terminals-edit', component: () => import('@/Pages/Admin/Terminals/TerminalsEditPage'), meta: {title: 'Редактирование мобильной кассы'}},

    /**
     * Sails part
     */
    {path: '/piers', name: 'pier-list', component: () => import('@/Pages/Admin/Piers/PiersListPage'), meta: {title: 'Причалы'}},
    {path: '/piers/:id', name: 'pier-view', component: () => import('@/Pages/Admin/Piers/PierViewPage'), meta: {title: 'Просмотр причала'}},
    {path: '/piers/:id/edit', name: 'pier-edit', component: () => import('@/Pages/Admin/Piers/PierEditPage'), meta: {title: 'Редактирование причала'}},

    {path: '/excursions', name: 'excursion-list', component: () => import('@/Pages/Admin/Excursions/ExcursionsListPage'), meta: {title: 'Каталог экскурсий'}},
    {path: '/excursions/:id', name: 'excursion-view', component: () => import('@/Pages/Admin/Excursions/ExcursionViewPage'), meta: {title: 'Просмотр экскурсии'}},
    {path: '/excursions/:id/edit', name: 'excursion-edit', component: () => import('@/Pages/Admin/Excursions/ExcursionEditPage'), meta: {title: 'Редактирование экскурсии'}},

    {path: '/trips', name: 'trip-list', component: () => import('@/Pages/Admin/Trips/TripsListPage'), meta: {title: 'Список рейсов'}},
    {path: '/trips/:id', name: 'trip-view', component: () => import('@/Pages/Admin/Trips/TripViewPage'), meta: {title: 'Просмотр рейса'}},
    {path: '/trips/:id/edit', name: 'trip-edit', component: () => import('@/Pages/Admin/Trips/TripEditPage'), meta: {title: 'Редактирование рейса'}},

    /**
     * Dictionaries
     */
    {path: '/dictionaries', name: 'dictionaries', component: DictionariesPage, meta: {title: 'Справочники'}},

    {path: '/test', name: 'test', component: TestPage, meta: {title: 'Страница для тестов'}},
    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
