require('./bootstrap');

import {createApp} from 'vue';

import App from './Apps/LoginApp.vue';

const app = createApp(App);

app.mount('#app');
