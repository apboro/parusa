import NotFound from "../../Pages/NotFound";
import PartnerSelfPage from "../../Pages/Partner/PartnerSelfPage";
import TicketsSelect from "../../Pages/Partner/TicketsSelect";
import Account from "../../Pages/Partner/Account";
import OrdersRegistryPage from "../../Pages/Partner/Registries/OrdersRegistryPage";
import TicketsRegistryPage from "../../Pages/Partner/Registries/TicketsRegistryPage";
import ReservesRegistryPage from "../../Pages/Partner/Registries/ReservesRegistryPage";
import Rates from "../../Pages/Partner/Rates";

export default [
    {path: '/', name: 'home', component: TicketsSelect},

    {path: '/tickets', name: 'tickets-select', component: TicketsSelect},

    {path: '/registry/orders', name: 'orders-registry', component: OrdersRegistryPage, meta: {title: 'Реестр заказов'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: TicketsRegistryPage, meta: {title: 'Реестр билетов'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: ReservesRegistryPage, meta: {title: 'Реестр броней'}},

    {path: '/company/info', name: 'company-info', component: PartnerSelfPage},
    {path: '/company/account', name: 'company-account', component: Account},
    {path: '/company/rates', name: 'company-rates', component: Rates},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
