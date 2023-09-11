
require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from "vue-router";

import App from './Apps/ControllerApp.vue';
import Toast from "./Plugins/Toast/toaster";
import Dialog from "./Plugins/Dialog/dialog";
import Highlight from "./Plugins/Highlight/highlight";
import routes from './Definitions/Controller/Routes';

let user = typeof window.user === "undefined" ? null : JSON.parse(window.user);
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})
const app = createApp(App, {user: user});

app.use(router)
app.use(Toast);
app.use(Dialog);
app.use(Highlight);

app.mount('#app');
