export default [
    {path: '/', name: 'home', component: () => import('@/Pages/Terminal/TripsSelectPage'), meta: {title: 'Подбор билетов'}},
    {path: '/', name: 'tickets-select', component: () => import('@/Pages/Terminal/TripsSelectPage'), meta: {title: 'Подбор билетов'}},

    {path: '/order', name: 'order', component: () => import("@/Pages/Terminal/OrderMakePage"), meta: {title: 'Оформление заказа'}},
    {path: '/reserves', name: 'reserves', component: () => import("@/Pages/Terminal/ReservesPage"), meta: {title: 'Выкуп брони'}},
    {path: '/return', name: 'return', component: () => import("@/Pages/Terminal/ReturnPage"), meta: {title: 'Возврат билетов'}},

    {path: '/registry/orders', name: 'orders-registry', component: () => import("@/Pages/Terminal/Registries/OrdersRegistryPage"), meta: {title: 'Реестр заказов'}},
    {path: '/registry/orders/:id', name: 'order-info', component: () => import("@/Pages/Terminal/Registries/OrderInfoPage"), meta: {title: 'Заказ'}},
    {path: '/registry/tickets', name: 'tickets-registry', component: () => import("@/Pages/Terminal/Registries/TicketsRegistryPage"), meta: {title: 'Реестр билетов'}},
    {path: '/registry/tickets/:id', name: 'ticket-info', component: () => import("@/Pages/Terminal/Registries/TicketInfoPage"), meta: {title: 'Билет'}},
    {path: '/registry/reserves', name: 'reserves-registry', component: () => import("@/Pages/Terminal/Registries/ReservesRegistryPage"), meta: {title: 'Реестр броней'}},

    {path: '/:pathMatch(.*)*', name: '404', component: () => import("@/Pages/NotFound")},
];
