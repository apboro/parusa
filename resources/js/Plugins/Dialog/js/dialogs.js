import Dialog from './dialog';

const DialogPlugin = function (_options) {

    this.options = _options;
    this.containerElement = null;
    this.dialogs = [];
    this.index = 0;

    this.getContainer = () => {
        if (this.containerElement !== null) {
            return this.containerElement;
        }

        let container = document.getElementById('dialogs');

        if (container) {
            this.containerElement = container;

            return container;
        }

        container = document.createElement('div');
        container.id = 'dialogs';
        container.className = 'dialogs';
        document.body.appendChild(container);
        this.containerElement = container;

        return container;
    };

    this._show = (message, type, color, buttons, buttons_align) => {
        const container = this.getContainer();
        const index = ++this.index;
        let dialog = new Dialog({
            id: index,
            container: container,
            message: message,
            type: type,
            color: color,
            buttons: buttons,
            buttons_align: buttons_align,
            resolve: () => {
                this.dialogs.some((dialog, key) => {
                    if (dialog !== null && dialog.id === index) {
                        this.dialogs.splice(key, 1);
                        return true;
                    }
                    return false;
                });
            },
        });

        this.dialogs.push(dialog);

        return dialog.promise;
    };

    this.show = (message, type, color, buttons, buttons_align = 'center') => {
        return this._show(message, type, color, buttons, buttons_align);
    };

    this.clear = () => {
        this.dialogs.map((dialog) => {
            if (dialog !== null) {
                dialog.remove();
            }
        });
        this.dialogs = [];
    };

    this.button = (result, caption, color) => {
        return {
            result: result,
            caption: caption,
            color: color,
        }
    }

    return this;
};

export default DialogPlugin;
