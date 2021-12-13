require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';

import App from './Apps/AdminApp.vue';
import menu from './Definitions/Admin/Menu';
import routes from './Definitions/Admin/Routes';
import store from './Stores/store';

import Toast from "./Plugins/Toast/toaster";
import Dialog from "./Plugins/Dialog/dialog";
import Highlight from "./Plugins/Highlight/highlight";

// add router
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

const app = createApp(App, {menu: menu});

app.use(router);
app.use(store);
app.use(Toast);
app.use(Dialog);
app.use(Highlight);

app.mount('#app');
