window._ = require('lodash');

import Promise from "promise-polyfill";
import ObjectAssign from "es6-object-assign";

window.Promise = Promise;
ObjectAssign.polyfill();

const origin = window.location.protocol + "//" + window.location.host;
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Access-Control-Allow-Origin'] = origin;
window.axios.defaults.headers.common['Access-Control-Allow-Methods'] = 'POST';
window.axios.defaults.withCredentials = true;

import {createApp} from 'vue';
import {createStore} from "vuex";
import createPersistedState from 'vuex-persistedstate';

import ShowcaseApp from '@/Apps/ShowcaseApp.vue';
import ShowcaseStore from "@/Stores/showcase-store";

const showcaseStore = createStore({
    modules: {
        showcase: ShowcaseStore,
    },
    plugins: [createPersistedState({
        key: 'ap-showcase-store'
    })]
});

const config = require('config');

const showcaseApp = createApp(ShowcaseApp, {crm_url: config['crm_url'], debug: config['debug']});
showcaseApp.use(showcaseStore);

document.addEventListener('DOMContentLoaded', () => {
    showcaseApp.mount('#ap-showcase');
})
