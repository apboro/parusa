const Dialog = function (options) {
    let dialog = {
        id: null,
        element: null,
        resolve: null,
        promise: null,
        promise_resolve: null,

        click(result) {
            this.promise_resolve(result);
            this.remove();
        },

        remove() {
            this.element.classList.add('dialogs__overlay-hide');
            setTimeout(() => {
                this.unset();
            }, 300);
        },

        unset() {
            this.element.remove();
            if (typeof this.resolve === "function") {
                this.resolve(this);
            }
        },

        show() {
            setTimeout(() => {
                this.element.classList.add('dialogs__overlay-shown');
            }, 100);
        },

        discard(event) {
            if (event.target === this.element) {
                this.promise_resolve(null);
                this.remove();
            }
        },
    };

    // Create message container
    const message_container = document.createElement("div");
    message_container.className = "dialogs__dialog-message";

    // Create icon
    if (options.type !== null) {
        const message_icon = document.createElement("div");
        message_icon.className = "dialogs__dialog-message-icon";
        message_icon.classList.add('dialogs__dialog-message-icon-' + options.color);
        switch (options.type) {
            case 'success':
                message_icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"/></svg>';
                break;
            case 'info':
                message_icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zm-248 50c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"/></svg>';
                break;
            case 'error':
                message_icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"/></svg>';
                break;
            case 'question':
                message_icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" shape-rendering="geometricPrecision"><path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"/></svg>';
                break;
        }
        message_container.appendChild(message_icon);
    }

    // Create message
    const message_text = document.createElement("div");
    message_text.className = "dialogs__dialog-message-text";
    message_text.innerHTML = options.message;
    message_container.appendChild(message_text);

    // Create button container
    const buttons_container = document.createElement("div");
    buttons_container.className = "dialogs__dialog-buttons";
    buttons_container.classList.add("dialogs__dialog-buttons-" + options.buttons_align);

    // Create buttons
    options.buttons.map(button => {
        const dialog_button = document.createElement("span");
        dialog_button.className = 'button';
        dialog_button.classList.add('button__' + button.color);
        dialog_button.innerHTML = button.caption;
        dialog_button.addEventListener('click', () => dialog.click(button.result));
        buttons_container.appendChild(dialog_button);
    });

    // Dialog wrapper
    const wrapper = document.createElement("div");
    wrapper.className = "dialogs__dialog-wrapper";
    wrapper.appendChild(message_container);
    wrapper.appendChild(buttons_container);

    // Create dialog
    const dialog_container = document.createElement("div");
    dialog_container.className = "dialogs__dialog";
    dialog_container.appendChild(wrapper);

    // Create overlay
    const overlay = document.createElement("div");
    overlay.className = "dialogs__overlay";
    overlay.addEventListener('click', dialog.discard.bind(dialog));
    overlay.appendChild(dialog_container);

    // Prepare options
    dialog.element = overlay;
    if (options.id) dialog.id = options.id;
    if (options.resolve) dialog.resolve = options.resolve;

    dialog.promise = new Promise(function (resolve) {
        dialog.promise_resolve = resolve;
    });

    options.container.appendChild(dialog.element);

    dialog.show();

    return dialog;
};

export default Dialog;
