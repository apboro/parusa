import TripsSelectPage from '@/Pages/Partner/TripsSelectPage';
import ReturnPage from "@/Pages/Partner/ReturnPage";
import SettingsPage from '@/Pages/Partner/SettingsPage';
import OrdersRegistryPage from "@/Pages/Partner/Registries/OrdersRegistryPage";
import ReservesRegistryPage from "@/Pages/Partner/Registries/ReservesRegistryPage";
import TicketsRegistryPage from "@/Pages/Partner/Registries/TicketsRegistryPage";
import OrderPage from "@/Pages/Partner/Registries/OrderPage";
import TicketPage from "@/Pages/Partner/Registries/TicketPage";
import PartnerPage from '@/Pages/Partner/PartnerPage';
import AccountPage from "@/Pages/Partner/AccountPage";
import Rates from "@/Pages/Partner/Rates";
import OrderMakePage from "@/Pages/Partner/OrderMakePage";
import NotFound from '@/Pages/NotFound';
import QrCodesPage from "@/Pages/Partner/QrCodesPage.vue";

export default [
    {path: '/', name: 'home', component: TripsSelectPage, meta: {title: 'Подбор билетов'}},
    {path: '/', name: 'tickets-select', component: TripsSelectPage, meta: {title: 'Подбор билетов'}},
    {path: '/return', name: 'return', component: ReturnPage, meta: {title: 'Возврат билетов'}},
    {path: '/settings', name: 'settings', component: SettingsPage, meta: {title: 'Настройки и коды'}},
    {path: '/qrcodes', name: 'qrcodes', component: QrCodesPage, meta: {title: 'QR-коды'}},
    {path: '/registry/orders', name: 'orders-registry', component: OrdersRegistryPage, meta: {title: 'Реестр заказов'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: ReservesRegistryPage, meta: {title: 'Реестр броней'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: TicketsRegistryPage, meta: {title: 'Реестр билетов'}},
    {path: '/registry/:id', name: 'order-info', component: OrderPage, meta: {title: 'Заказ'}},
    {path: '/registry/tickets/:id', name: 'ticket-info', component: TicketPage, meta: {title: 'Билет'}},
    {path: '/company/info', name: 'company-info', component: PartnerPage, meta: {title: 'Карточка партнёра'}},
    {path: '/company/account', name: 'company-account', component: AccountPage, meta: {title: 'Лицевой счёт'}},
    {path: '/company/rates', name: 'company-rates', component: Rates, meta: {title: 'Тарифы и комиссионное вознаграждение'}},
    {path: '/order', name: 'order', component: OrderMakePage, meta: {title: 'Оформление заказа'}},
    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
