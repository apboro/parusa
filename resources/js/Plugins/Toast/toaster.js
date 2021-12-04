import ToastPlugin from './js/toaster';

export default {
    install: (app, options) => {
        if(!options) {
            options = {};
        }

        app.config.globalProperties.$toast = new ToastPlugin(options);
    }
}
