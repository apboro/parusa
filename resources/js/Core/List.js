import clone from "@/Core/Helpers/Clone";

const list = function (url, options = {}, pagination = true) {
    let list = {
        /** List load url */
        url: null,
        /** Default options to pass to request */
        options: {},
        /** Pagination */
        pagination: null,

        titles: {},
        list: [],
        payload: {},

        filters: {},
        filters_original: {},

        search: null,
        search_by: {},

        order: null,
        order_by: null,

        is_loading: false,
        is_loaded: false,

        toaster: null,

        /** Callbacks */
        loaded_callback: null,
        load_failed_callback: null,

        reload() {
            return this.load(this.pagination.current_page, this.pagination.per_page);
        },

        initial() {
            return this.load(1, null, true);
        },

        load(page = 1, perPage = null, initial = false) {
            return new Promise((resolve, reject) => {
                this.is_loading = true;

                let options = clone(this.options);

                options['filters'] = this.filters;
                options['search'] = this.search;
                options['search_by'] = this.search_by;
                options['order'] = this.order;
                options['order_by'] = this.order_by;
                options['page'] = page;
                options['per_page'] = perPage === null ? this.pagination.per_page : perPage;
                options['initial'] = initial;

                axios.post(this.url, options)
                    .then(response => {
                        this.list = response.data.list;
                        this.titles = typeof response.data.titles !== "undefined" ? response.data.titles : {};
                        this.pagination = typeof response.data.pagination !== "undefined" ? response.data.pagination : null;
                        this.filters = typeof response.data.filters !== "undefined" && response.data.filters !== null && Object.keys(response.data.filters).length > 0 ? response.data.filters : {};
                        this.filters_original = typeof response.data.filters_original !== "undefined" && response.data.filters_original !== null ? response.data.filters_original : {};
                        this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : {};
                        this.is_loaded = true;
                        if (typeof this.loaded_callback === "function") {
                            this.loaded_callback({titles: this.titles, list: this.list, payload: this.payload});
                        }
                        resolve({titles: this.titles, list: this.list, payload: this.payload});
                    })
                    .catch(error => {
                        this.notify(error.response.message, 0, 'error');
                        if (typeof this.load_failed_callback === "function") {
                            this.load_failed_callback({code: error.response.status, message: error.response.data.message});
                        }
                        reject({code: error.response.status, message: error.response.data.message});
                    })
                    .finally(() => {
                        this.is_loading = false;
                    });
            });
        },

        hasItems() {
            return this.is_loaded && this.list && this.list.length > 0;
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

    list.url = url;
    list.options = options;
    if (pagination) {
        list.pagination = {
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
            per_page: 10,
        };
    }

    return list;
}

export default list;
