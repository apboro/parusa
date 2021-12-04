const Toast = function (options) {
    let toast = {
        id: null,
        container: null,
        message: null,
        showTime: 0,
        type: null,
        element: null,
        remains: 0,
        interval: null,
        resolve: null,

        remove() {
            window.clearInterval(this.interval);
            this.element.classList.add('toaster__toast-hide');
            setTimeout(() => {
                this.unset();
            }, 200);
        },

        unset() {
            this.element.remove();
            if (typeof this.resolve === "function") {
                this.resolve(this);
            }
        },

        show() {
            setTimeout(() => {
                this.element.classList.add('toaster__toast-show');
            }, 100);
        },

        startTimer() {
            if (this.showTime !== 0) {
                this.remains = this.showTime;
                this.interval = setInterval(() => {
                    this.remains -= 100;
                    if (this.remains <= 0) {
                        this.remove();
                    }
                }, 100);
            }
        },
    };

    // Prepare options
    if (options.id) toast.id = options.id;
    if (options.container) toast.container = options.container;
    if (options.message) toast.message = options.message;
    if (options.showTime !== null) toast.showTime = options.showTime;
    if (options.type) toast.type = options.type;
    if (options.resolve) toast.resolve = options.resolve;

    // Create elements
    const close = document.createElement("div");
    close.className = "toaster__toast-close";
    close.innerHTML = '<svg class="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">' +
        '<path fill-rule="evenodd"' +
        ' d="M 0.5468683,16.299691 6.8464951,10.000001 0.5468683,3.7004089 3.700374,0.54683812 l 6.299627,6.29969938 6.299624,-6.29969938 3.153507,3.15357078 -6.299626,6.2995921 6.299626,6.29969 -3.153507,3.153471 -6.299624,-6.299599 -6.299627,6.299599 z"\n' +
        ' clip-rule="evenodd"/></svg>';
    close.addEventListener('click', () => {
        toast.remove();
    });

    toast.element = document.createElement("div");

    toast.element.className = "toaster__toast";

    if (options.type) {
        switch (options.type) {
            case 'success':
                toast.element.classList.add('toaster__toast-success');
                break;
            case 'info':
                toast.element.classList.add('toaster__toast-info');
                break;
            case 'error':
                toast.element.classList.add('toaster__toast-error');
                break;
        }
    }

    toast.element.innerHTML = toast.message;
    toast.element.appendChild(close);
    toast.container.appendChild(toast.element);

    toast.startTimer();
    toast.show();

    return toast;
};

export default Toast;
