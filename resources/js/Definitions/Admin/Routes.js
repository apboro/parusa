import HomePage from "../../Pages/Admin/HomePage";
import NotFound from "../../Pages/NotFound";
import PartnersListPage from "../../Pages/Admin/Partner/Partner/PartnersListPage";
import TicketRefundsListPage from "../../Pages/Admin/TicketRefundsListPage";
import MobileSalesListPage from "../../Pages/Admin/MobileSalesListPage";

import StaffListPage from "../../Pages/Admin/Company/Staff/StaffListPage";
import StaffViewPage from "../../Pages/Admin/Company/Staff/StaffCardPage";
import StaffEditPage from "../../Pages/Admin/Company/Staff/StaffEditPage";

import PartnerCardPage from "../../Pages/Admin/Partner/Partner/PartnerCardPage";
import RepresentativeCardPage from "../../Pages/Admin/Partner/Representatives/RepresentativeCardPage";
import RepresentativeEditPage from "../../Pages/Admin/Partner/Representatives/RepresentativeEditPage";
import RepresentativesListPage from "../../Pages/Admin/Partner/Representatives/RepresentativesListPage";
import PiersListPage from "../../Pages/Admin/Sails/Piers/PiersListPage";
import PierCardPage from "../../Pages/Admin/Sails/Piers/PierCardPage";
import PierEditPage from "../../Pages/Admin/Sails/Piers/PierEditPage";
import ExcursionsListPage from "../../Pages/Admin/Sails/Excursions/ExcursionsListPage";
import ExcursionCardPage from "../../Pages/Admin/Sails/Excursions/ExcursionCardPage";
import ExcursionEditPage from "../../Pages/Admin/Sails/Excursions/ExcursionEditPage";

// import TestPage from "../../Pages/Admin/TestPage";

export default [
    {path: '/', name: 'home', component: HomePage},

    {path: '/partners', name: 'partners-list', component: PartnersListPage, meta: {title: 'Компании-партнёры'}},
    {path: '/partners/:id', name: 'partners-view', component: PartnerCardPage, meta: {title: 'Просмотр партнёра'}},
    {path: '/partners/:id/edit', name: 'partners-edit', component: PartnerCardPage, meta: {title: 'Редактирование партнёра'}},

    {path: '/representatives', name: 'representatives-list', component: RepresentativesListPage, meta: {title: 'Представители'}},
    {path: '/representatives/:id', name: 'representatives-view', component: RepresentativeCardPage, meta: {title: 'Просмотр представителя'}},
    {path: '/representatives/:id/edit', name: 'representatives-edit', component: RepresentativeEditPage, meta: {title: 'Редактирование представителя'}},

    {path: '/mobile-sales', name: 'mobile-sales-list', component: MobileSalesListPage, meta: {title: 'Мобильные кассы'}},
    {path: '/ticket-refund', name: 'ticket-refund-list', component: TicketRefundsListPage, meta: {title: 'Возврат билетов'}},

    {path: '/staff', name: 'staff-list', component: StaffListPage, meta: {title: 'Сотрудники'}},
    {path: '/staff/:id', name: 'staff-view', component: StaffViewPage, meta: {title: 'Просмотр сотрудника'}},
    {path: '/staff/:id/edit', name: 'staff-edit', component: StaffEditPage, meta: {title: 'Редактирование сотрудника'}},

    /**
     * Sails part
     */
    {path: '/piers', name: 'pier-list', component: PiersListPage, meta: {title: 'Причалы'}},
    {path: '/piers/:id', name: 'pier-view', component: PierCardPage, meta: {title: 'Просмотр причала'}},
    {path: '/piers/:id/edit', name: 'pier-edit', component: PierEditPage, meta: {title: 'Редактирование причала'}},

    {path: '/excursions', name: 'excursion-list', component: ExcursionsListPage, meta: {title: 'Каталог экскурсий'}},
    {path: '/excursions/:id', name: 'excursion-view', component: ExcursionCardPage, meta: {title: 'Просмотр экскурсии'}},
    {path: '/excursions/:id/edit', name: 'excursion-edit', component: ExcursionEditPage, meta: {title: 'Редактирование экскурсии'}},




    // {path: '/test', name: 'test', component: TestPage, meta: {title: 'Страница для тестов'}},
    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
