import HomePage from "../../Pages/Admin/HomePage";
import NotFound from "../../Pages/NotFound";
import PartnersListPage from "../../Pages/Admin/PartnersListPage";
import TicketRefundsListPage from "../../Pages/Admin/TicketRefundsListPage";
import MobileSalesListPage from "../../Pages/Admin/MobileSalesListPage";
import RepresentativesListPage from "../../Pages/Admin/RepresentativesListPage";

export default [
    {path: '/', name: 'home', component: HomePage},

    {path: '/partners', name: 'partners-list', component: PartnersListPage},
    {path: '/partners', name: 'representative', component: RepresentativesListPage},
    {path: '/mobile-sales', name: 'mobile-sales', component: MobileSalesListPage},
    {path: '/ticket-refund', name: 'ticket-refund', component: TicketRefundsListPage},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
