import DialogPlugin from './js/dialogs';

export default {
    install: (app, options) => {
        if(!options) {
            options = {};
        }

        app.config.globalProperties.$dialog = new DialogPlugin(options);
    }
}
