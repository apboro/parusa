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

import ShowcaseApp from '@/Apps/ShowcaseApp3.vue';

const config = require('config');

const showcaseApp = createApp(ShowcaseApp, {crm_url: config['crm_url'], debug: config['debug']});

document.addEventListener('DOMContentLoaded', () => {
    showcaseApp.mount('#ap-showcase3');
})
