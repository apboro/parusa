import Rates from "../../Pages/Partner/Rates";

export default [
    {path: '/', name: 'home', component: () => import('@/Pages/Partner/TripsSelectPage'), meta: {title: 'Подбор билетов'}},
    {path: '/', name: 'tickets-select', component: () => import('@/Pages/Partner/TripsSelectPage'), meta: {title: 'Подбор билетов'}},
    {path: '/return', name: 'return', component: () => import("@/Pages/Partner/ReturnPage"), meta: {title: 'Возврат билетов'}},

    {path: '/settings', name: 'settings', component: () => import('@/Pages/Partner/SettingsPage'), meta: {title: 'Настройки'}},

    {path: '/registry/orders', name: 'orders-registry', component: () => import("@/Pages/Partner/Registries/OrdersRegistryPage"), meta: {title: 'Реестр заказов'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: () => import("@/Pages/Partner/Registries/ReservesRegistryPage"), meta: {title: 'Реестр броней'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: () => import("@/Pages/Partner/Registries/TicketsRegistryPage"), meta: {title: 'Реестр билетов'}},
    {path: '/registry/:id', name: 'order-info', component: () => import("@/Pages/Partner/Registries/OrderPage"), meta: {title: 'Заказ'}},
    {path: '/registry/tickets/:id', name: 'ticket-info', component: () => import("@/Pages/Partner/Registries/TicketPage"), meta: {title: 'Билет'}},

    {path: '/company/info', name: 'company-info', component: () => import('@/Pages/Partner/PartnerPage'), meta: {title: 'Карточка партнёра'}},
    {path: '/company/account', name: 'company-account', component: () => import("@/Pages/Partner/AccountPage"), meta: {title: 'Лицевой счёт'}},
    {path: '/company/rates', name: 'company-rates', component: Rates, meta: {title: 'Тарифы и комисионное вознаграждение'}},

    {path: '/order', name: 'order', component: () => import("@/Pages/Partner/OrderMakePage"), meta: {title: 'Оформление заказа'}},

    {path: '/:pathMatch(.*)*', name: '404', component: () => import('@/Pages/NotFound')},
];
