require('./bootstrap');

import {createApp} from 'vue';
import {createRouter, createWebHistory} from 'vue-router';
import { createStore } from 'vuex';

import App from './Apps/TerminalApp.vue';
import menu from './Definitions/Terminal/Menu';
import routes from './Definitions/Terminal/Routes';
import DictionaryStore from "@/Stores/dictionary-store";
import TerminalStore from "@/Stores/terminal-store";

import Toast from "./Plugins/Toast/toaster";
import Dialog from "./Plugins/Dialog/dialog";
import Highlight from "./Plugins/Highlight/highlight";

// add router
const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

const store = createStore({
    modules: {
        dictionary: DictionaryStore,
        terminal: TerminalStore,
    }
});

let user = typeof window.user === "undefined" ? null : JSON.parse(window.user);

const app = createApp(App, {menu: menu, user: user});

app.use(router);
app.use(store);
app.use(Toast);
app.use(Dialog);
app.use(Highlight);

app.mount('#app');
