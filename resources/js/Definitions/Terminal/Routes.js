export default [
    {path: '/', name: 'home', component: () => import('@/Pages/Terminal/TripsSelectPage'), meta: {title: 'Подбор билетов'}},
    {path: '/', name: 'tickets-select', component: () => import('@/Pages/Terminal/TripsSelectPage'), meta: {title: 'Подбор билетов'}},

    {path: '/order', name: 'order', component: () => import("@/Pages/Terminal/OrderMakePage"), meta: {title: 'Оформление заказа', hideWidget: true}},

    {path: '/:pathMatch(.*)*', name: '404', component: () => import("@/Pages/NotFound")},
];
