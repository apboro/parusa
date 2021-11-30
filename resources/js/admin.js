require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';

import App from './Apps/AdminApp.vue';
import menu from './Definitions/Admin/Menu';
import routes from './Definitions/Admin/Routes';

// add router
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

// add stores

const app = createApp(App, {menu: menu});

app.use(router);

const vm = app.mount('#app');
