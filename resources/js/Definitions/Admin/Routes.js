import HomePage from "../../Pages/Admin/HomePage";
import NotFound from "../../Pages/NotFound";
import PartnersListPage from "../../Pages/Admin/PartnersListPage";
import TicketRefundsListPage from "../../Pages/Admin/TicketRefundsListPage";
import MobileSalesListPage from "../../Pages/Admin/MobileSalesListPage";
import RepresentativesListPage from "../../Pages/Admin/RepresentativesListPage";

import StaffUsersListPage from "../../Pages/Admin/Company/StaffUsersListPage";

import TestPage from "../../Pages/Admin/TestPage";
import StaffUserViewPage from "../../Pages/Admin/Company/StaffUserCardPage";
import StaffUserEditPage from "../../Pages/Admin/Company/StaffUserEditPage";

export default [
    {path: '/', name: 'home', component: HomePage},

    {path: '/partners', name: 'partners-list', component: PartnersListPage, meta: {title: 'Компании-партнёры'}},
    {path: '/representatives', name: 'representatives-list', component: RepresentativesListPage, meta: {title: 'Представители'}},
    {path: '/mobile-sales', name: 'mobile-sales-list', component: MobileSalesListPage, meta: {title: 'Мобильные кассы'}},
    {path: '/ticket-refund', name: 'ticket-refund-list', component: TicketRefundsListPage, meta: {title: 'Возврат билетов'}},

    {path: '/staff', name: 'staff-user-list', component: StaffUsersListPage, meta: {title: 'Сотрудники'}},
    {path: '/staff/:id', name: 'staff-user-view', component: StaffUserViewPage, meta: {title: 'Просмотр сотрудника'}},
    {path: '/staff/:id/edit', name: 'staff-user-edit', component: StaffUserEditPage, meta: {title: 'Редактирование сотрудника'}},


    {path: '/test', name: 'test', component: TestPage, meta: {title: 'Страница для тестов'}},
    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
