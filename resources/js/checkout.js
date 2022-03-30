window._ = require('lodash');

import Promise from "promise-polyfill";
import ObjectAssign from "es6-object-assign";

window.Promise = Promise;
ObjectAssign.polyfill();

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import {createApp} from 'vue';
import CheckoutApp from '@/Apps/CheckoutApp.vue';

const config = require('config');

const checkoutApp = createApp(CheckoutApp, {crm_url: config['crm_url'], debug: config['debug']});

document.addEventListener('DOMContentLoaded', () => {
    checkoutApp.mount('#ap-checkout');
})
