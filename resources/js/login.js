require('./bootstrap');

// Add a response interceptor
axios.interceptors.response.use(
    response => {
        return response;
    },
    (error) => {
        if (error.response.status === 419 && error.response.config && !error.response.config.__isRetryRequest) {
            return new Promise((resolve) => {
                axios.get('/login/token')
                    .then((resp) => {
                        const token = resp.data.token;
                        document.head.querySelector('meta[name="csrf-token"]').content = token;
                        error.response.config.__isRetryRequest = true;
                        error.response.config.headers['X-CSRF-TOKEN'] = token;
                        resolve(axios(error.response.config));
                        console.log('New token retrieved.');
                    })
                    .catch((err) => {
                        console.log('Can not retrieve new token', err);
                    });
            });
        }

        return Promise.reject(error);
    },
);

import {createApp} from 'vue';

import App from './Apps/LoginApp.vue';

const app = createApp(App);

app.mount('#app');
