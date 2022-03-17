export default [
    {path: '/', name: 'home', component: () => import('@/Pages/Terminal/TripsListPage'), meta: {title: 'Подбор билетов'}},

    {path: '/order', name: 'order', component: () => import("@/Pages/Terminal/OrderMakePage"), meta: {title: 'Оформление заказа'}},

    {path: '/reserves', name: 'reserve-search', component: () => import("@/Pages/Terminal/ReserveSearchPage"), meta: {title: 'Поиск брони'}},
    {path: '/reserves/:id', name: 'reserve', component: () => import("@/Pages/Terminal/ReservePage"), meta: {title: 'Бронь'}},

    {path: '/return', name: 'return-search', component: () => import("@/Pages/Terminal/ReturnSearchPage"), meta: {title: 'Возврат билетов'}},
    {path: '/return/:id', name: 'return', component: () => import("@/Pages/Terminal/ReturnPage"), meta: {title: 'Заказ'}},

    {path: '/registry/orders', name: 'orders-registry', component: () => import('@/Pages/Terminal/OrdersRegistryPage'), meta: {title: 'Реестр заказов'}},
    {path: '/registry/orders/:id', name: 'order-info', component: () => import("@/Pages/Terminal/OrderPage"), meta: {title: 'Заказ'}},

    {path: '/:pathMatch(.*)*', name: '404', component: () => import("@/Pages/NotFound")},
];
