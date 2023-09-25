import TripsListPage from '@/Pages/Terminal/TripsListPage';
import OrderMakePage from "@/Pages/Terminal/OrderMakePage";
import ReserveSearchPage from "@/Pages/Terminal/ReserveSearchPage";
import ReservePage from "@/Pages/Terminal/ReservePage";
import ReturnSearchPage from "@/Pages/Terminal/ReturnSearchPage";
import ReturnPage from "@/Pages/Terminal/ReturnPage";
import OrdersRegistryPage from '@/Pages/Terminal/OrdersRegistryPage';
import OrderPage from "@/Pages/Terminal/OrderPage";
import NotFound from "@/Pages/NotFound";
import TransactionsRegistryPage from "@/Pages/Terminal/TransactionsRegistryPage";
import TripViewPage from "../../Pages/Admin/Trips/TripViewPage.vue";
import PromotersListPage from "@/Pages/Terminal/PromotersListPage.vue";

export default [
    {path: '/', name: 'home', component: TripsListPage, meta: {title: 'Подбор билетов'}},
    {path: '/order', name: 'order', component: OrderMakePage, meta: {title: 'Оформление заказа'}},
    {path: '/reserves', name: 'reserve-search', component: ReserveSearchPage, meta: {title: 'Поиск брони'}},
    {path: '/reserves/:id', name: 'reserve', component: ReservePage, meta: {title: 'Бронь'}},
    {path: '/return', name: 'return-search', component: ReturnSearchPage, meta: {title: 'Возврат билетов'}},
    {path: '/return/:id', name: 'return', component: ReturnPage, meta: {title: 'Заказ'}},
    {path: '/registry/orders', name: 'orders-registry', component: OrdersRegistryPage, meta: {title: 'Реестр заказов'}},
    {path: '/registry/orders/:id', name: 'order-info', component: OrderPage, meta: {title: 'Заказ'}},
    {path: '/registry/transactions', name: 'transactions-registry', component: TransactionsRegistryPage, meta: {title: 'Реестр транзакций'}},
    {path: '/terminal/promoters', name: 'terminal-promoters', component: PromotersListPage, meta: {title: 'Промоутеры'}},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
    {path: '/trips/:id', name: 'trip-view', component: TripViewPage, meta: {title: 'Просмотр рейса', roles: ['admin', 'office_manager', 'piers_manager', 'accountant']}},

];
