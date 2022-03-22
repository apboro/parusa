window._ = require('lodash');

import Promise from "promise-polyfill";
import ObjectAssign from "es6-object-assign";

window.Promise = Promise;
ObjectAssign.polyfill();

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import {createApp} from 'vue';
import CheckoutApp from '@/Apps/CheckoutApp.vue';

const checkoutApp = createApp(CheckoutApp, {});

document.addEventListener('DOMContentLoaded', () => {
    checkoutApp.mount('#ap-checkout');
})
