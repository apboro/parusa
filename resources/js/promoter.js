require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';
import {createStore} from 'vuex';

import App from './Apps/PartnerApp.vue';
import menu from './Definitions/Partner/Menu';
import routes from './Definitions/Partner/Routes';
import DictionaryStore from "@/Stores/dictionary-store";
import PartnerStore from "@/Stores/partner-store";

import Toast from "./Plugins/Toast/toaster";
import Dialog from "./Plugins/Dialog/dialog";
import Highlight from "./Plugins/Highlight/highlight";

// add router
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

const partnerStore = createStore({
    modules: {
        dictionary: DictionaryStore,
        partner: PartnerStore,
    }
});

let user = typeof window.user === "undefined" ? null : JSON.parse(window.user);

const app = createApp(App, {menu: menu, user: user});

app.use(router);
app.use(partnerStore);
app.use(Toast);
app.use(Dialog);
app.use(Highlight);

app.mount('#app');
