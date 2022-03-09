export default [
    {path: '/', name: 'home', component: () => import('@/Pages/Terminal/TripsSelectPage'), meta: {title: 'Подбор билетов'}},
    {path: '/', name: 'tickets-select', component: () => import('@/Pages/Terminal/TripsSelectPage'), meta: {title: 'Подбор билетов'}},

    {path: '/order', name: 'order', component: () => import("@/Pages/Terminal/OrderPage"), meta: {title: 'Оформление заказа'}},
    {path: '/current', name: 'current', component: () => import("@/Pages/Terminal/CurrentOrderPage"), meta: {title: 'Заказ'}},

    {path: '/:pathMatch(.*)*', name: '404', component: () => import("@/Pages/NotFound")},
];
