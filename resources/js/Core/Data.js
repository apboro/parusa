const data = function (url) {
    let data = {
        url: null,

        data: {},
        payload: {},

        is_loading: false,
        is_loaded: false,

        /** Callbacks */
        loaded_callback: null,
        load_failed_callback: null,

        toaster: null,

        /**
         * Load data.
         *
         * @param options Options to pass to request.
         *
         * @returns {Promise}
         */
        load(options = {}) {
            return new Promise((resolve, reject) => {
                this.is_loading = true;

                axios.post(this.url, options)
                    .then(response => {
                        this.data = response.data.data;
                        this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : {};
                        if (typeof this.loaded_callback === "function") {
                            this.loaded_callback(this.data, this.payload);
                        }
                        this.is_loaded = true;
                        resolve(this.data, this.payload);
                    })
                    .catch(error => {
                        this.notify(error.response.data.message);
                        if (typeof this.load_failed_callback === "function") {
                            this.load_failed_callback(error.response.status, error.response.data);
                        }
                        console.log(error.response.status);
                        reject(error.response.status);
                    })
                    .finally(() => {
                        this.is_loading = false;
                    });
            });
        },

        /**
         * Alias for clarifying data processing.
         *
         * @param options Options to pass to request.
         *
         * @returns {Promise}
         */
        save(options = {}) {
            return this.load(options);
        },

        /**
         * Show notification to user.
         *
         * @param message
         * @param delay
         * @param type
         */
        notify(message, delay, type) {
            if (this.toaster !== null) {
                this.toaster.show(message, delay, type);
            } else {
                if (type === 'error') {
                    console.error(message);
                } else {
                    console.log(message);
                }
            }
        },
    };

    data.url = url;

    return data;
}

export default data;
