import PromoterStore from "@/Stores/promoter-store";

require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';
import {createStore} from 'vuex';

import App from './Apps/PromoterApp.vue';
import menu from './Definitions/Promoter/Menu';
import routes from './Definitions/Promoter/Routes';
import DictionaryStore from "@/Stores/dictionary-store";

import Toast from "./Plugins/Toast/toaster";
import Dialog from "./Plugins/Dialog/dialog";
import Highlight from "./Plugins/Highlight/highlight";

// add router
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

const promoterStore = createStore({
    modules: {
        dictionary: DictionaryStore,
        promoter: PromoterStore,
    }
});

let user = typeof window.user === "undefined" ? null : JSON.parse(window.user);

const app = createApp(App, {menu: menu, user: user});

app.use(router);
app.use(promoterStore);
app.use(Toast);
app.use(Dialog);
app.use(Highlight);

app.mount('#app');
