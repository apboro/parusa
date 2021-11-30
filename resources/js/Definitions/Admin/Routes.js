import HomePage from "../../Pages/Admin/HomePage";
import NotFound from "../../Pages/NotFound";
import PartnersListPage from "../../Pages/Admin/PartnersListPage";

export default [
    {path: '/', name: 'home', component: HomePage},

    {path: '/partners', name: 'partners-list', component: PartnersListPage},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
