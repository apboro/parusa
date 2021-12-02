import HomePage from "../../Pages/Admin/HomePage";
import NotFound from "../../Pages/NotFound";
import PartnersListPage from "../../Pages/Admin/PartnersListPage";
import TicketRefundsListPage from "../../Pages/Admin/TicketRefundsListPage";
import MobileSalesListPage from "../../Pages/Admin/MobileSalesListPage";
import RepresentativesListPage from "../../Pages/Admin/RepresentativesListPage";

export default [
    {path: '/', name: 'home', component: HomePage},

    {path: '/partners', name: 'partners-list', component: PartnersListPage, meta: {title: 'Компании-партнёры'}},
    {path: '/representatives', name: 'representatives-list', component: RepresentativesListPage, meta: {title: 'Представители'}},
    {path: '/mobile-sales', name: 'mobile-sales-list', component: MobileSalesListPage, meta: {title: 'Мобильные кассы'}},
    {path: '/ticket-refund', name: 'ticket-refund-list', component: TicketRefundsListPage, meta: {title: 'Возврат билетов'}},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
