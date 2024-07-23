window._ = require('lodash');

import Promise from "promise-polyfill";
import ObjectAssign from "es6-object-assign";
import { createPinia } from 'pinia'

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

const kazanApp = createApp(KazanApp);

kazanApp.use(pinia);
document.addEventListener('DOMContentLoaded', () => {
    kazanApp.mount('#ap-kazan');
})

