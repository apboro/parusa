import config from "config";

window._ = require('lodash');

import Promise from "promise-polyfill";
import ObjectAssign from "es6-object-assign";
import { createPinia } from 'pinia'

const dictionaryStore = createStore({
    modules: {
        dictionary: DictionaryStore,
    }
});

window.Promise = Promise;
ObjectAssign.polyfill();

const pinia = createPinia();
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Access-Control-Allow-Origin'] = origin;
window.axios.defaults.headers.common['Access-Control-Allow-Methods'] = 'POST';
window.axios.defaults.withCredentials = true;

import {createApp} from 'vue';

import KazanApp from '@/Apps/KazanApp.vue';
import {createStore} from "vuex";
import DictionaryStore from "@/Stores/dictionary-store";
import RolesStore from "@/Stores/roles-store";

const kazanApp = createApp(KazanApp, {crm_url: config['crm_url'], debug: config['debug']});

kazanApp.use(pinia);
kazanApp.use(dictionaryStore);
document.addEventListener('DOMContentLoaded', () => {
    kazanApp.mount('#ap-kazan');
})

