require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';

import App from './Apps/PartnerApp.vue';
import menu from './Definitions/Partner/Menu';
import routes from './Definitions/Partner/Routes';
import store from './Stores/partnerStore';

import Toast from "./Plugins/Toast/toaster";
import Dialog from "./Plugins/Dialog/dialog";
import Highlight from "./Plugins/Highlight/highlight";

// add router
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

let user = typeof window.user === "undefined" ? null : JSON.parse(window.user);

const app = createApp(App, {menu: menu, user: user});

app.use(router);
app.use(store);
app.use(Toast);
app.use(Dialog);
app.use(Highlight);

app.mount('#app');
