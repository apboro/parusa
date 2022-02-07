import NotFound from "../../Pages/NotFound";
import PartnerSelfPage from "../../Pages/Partner/PartnerSelfPage";
import TicketsSelect from "../../Pages/Partner/TicketsSelect";
import Account from "../../Pages/Partner/Account";
import OrdersRegistryPage from "../../Pages/Partner/Registries/OrdersRegistryPage";
import TicketsRegistryPage from "../../Pages/Partner/Registries/TicketsRegistryPage";
import ReservesRegistryPage from "../../Pages/Partner/Registries/ReservesRegistryPage";
import Rates from "../../Pages/Partner/Rates";
import OrderPage from "@/Pages/Partner/OrderPage";

export default [
    {path: '/', name: 'home', component: TicketsSelect, meta: {title: 'Подбор билетов'}},
    {path: '/', name: 'tickets-select', component: TicketsSelect, meta: {title: 'Подбор билетов'}},

    {path: '/registry/orders', name: 'orders-registry', component: OrdersRegistryPage, meta: {title: 'Реестр заказов'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: TicketsRegistryPage, meta: {title: 'Реестр билетов'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: ReservesRegistryPage, meta: {title: 'Реестр броней'}},

    {path: '/company/info', name: 'company-info', component: PartnerSelfPage, meta: {title: 'Карточка партнёра'}},
    {path: '/company/account', name: 'company-account', component: Account, meta: {title: 'Лицевой счёт'}},
    {path: '/company/rates', name: 'company-rates', component: Rates, meta: {title: 'Тарифы и комисионное вознаграждение'}},

    {path: '/order', name: 'order', component: OrderPage, meta: {title: 'Оформление заказа'}},
    {path: '/order-info/:id', name: 'order-info', component: () => import("@/Pages/Partner/OrderInfoPage"), meta: {title: 'Заказ'}},
    {path: '/ticket-info/:id', name: 'ticket-info', component: () => import("@/Pages/Partner/TicketInfoPage"), meta: {title: 'Билет'}},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
