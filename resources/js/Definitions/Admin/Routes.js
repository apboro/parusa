import HomePage from "../../Pages/Admin/HomePage";
import NotFound from "../../Pages/NotFound";

export default [
    {path: '/', name: 'home', component: HomePage},

    {path: '/:pathMatch(.*)*', name: '404', component: NotFound},
];
