import Toast from './toast';

const ToastPlugin = function (_options) {

    this.options = _options;
    this.containerElement = null;
    this.toasts = [];
    this.index = 0;

    this.getContainer = () => {
        if (this.containerElement !== null) {
            return this.containerElement;
        }

        let container = document.getElementById('toaster');

        if (container) {
            this.containerElement = container;

            return container;
        }

        container = document.createElement('div');
        container.id = 'toaster';
        container.className = 'toaster';
        document.body.appendChild(container);
        this.containerElement = container;

        return container;
    };

    this._show = (message, delay = null, type = null) => {
        const container = this.getContainer();
        const index = ++this.index;
        let toast = new Toast({
            container: container,
            message: message,
            showTime: delay,
            type: type,
            id: index,
            resolve: () => {
                this.toasts.some((toast, key) => {
                    if (toast !== null && toast.id === index) {
                        this.toasts.splice(key, 1);
                        return true;
                    }
                    return false;
                });
            },
        });

        this.toasts.push(toast);
    };

    this.show = (message, delay, type = null) => {
        this._show(message, delay, type)
    };
    this.success = (message, delay = null) => {
        this._show(message, delay, 'success')
    };
    this.info = (message, delay = null) => {
        this._show(message, delay, 'info')
    };
    this.error = (message, delay = null) => {
        this._show(message, delay, 'error')
    };

    this.clear = () => {
        this.toasts.map((toast) => {
            toast.remove();
        });
        this.toasts = [];
    };

    return this;
};

export default ToastPlugin;
