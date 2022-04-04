require('./bootstrap');

import {createApp} from 'vue';

import App from './Apps/LoginApp.vue';
import Dialog from "@/Plugins/Dialog/dialog";

const app = createApp(App);
app.use(Dialog);

app.mount('#app');
