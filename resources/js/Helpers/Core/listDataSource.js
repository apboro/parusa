import initialPagination from "./initialPagination";
import clone from "@/Core/Helpers/Clone";

const listDataSource = function (dataSourceUrl, usePagination = true, options = {}) {
    let list = {
        dataSourceUrl: null,
        options: {},

        titles: [],
        data: [],

        filters_original: {},

        search: null,
        filters: {},
        order: null,
        order_by: null,
        pagination: usePagination ? initialPagination : null,

        payload: {},

        loading: false,
        loaded: false,
        has_error: false,
        error_message: null,

        reload() {
            this.load(this.pagination.current_page, this.pagination.per_page);
        },

        load(page = 1, perPage = null, initial = false) {
            this.loading = true;

            let options = clone(this.options);

            options['filters'] = this.filters;
            options['search'] = this.search;
            options['order'] = this.order;
            options['order_by'] = this.order_by;
            options['page'] = page;
            options['per_page'] = perPage === null ? this.pagination.per_page : perPage;
            options['initial'] = initial;

            axios.post(this.dataSourceUrl, options)
                .then(response => {
                    this.data = response.data.list;
                    this.titles = typeof response.data.titles !== "undefined" ? response.data.titles : null;
                    this.pagination = typeof response.data.pagination !== "undefined" && usePagination ? response.data.pagination : null;
                    let payload = typeof response.data.payload !== "undefined" ? response.data.payload : null;
                    if (payload !== null && typeof payload['filters'] !== "undefined") {
                        this.filters = payload['filters'];
                        delete payload['filters'];
                    }
                    if (payload !== null && typeof payload['filters_original'] !== "undefined") {
                        this.filters_original = payload['filters_original'];
                        delete payload['filters_original'];
                    }
                    this.payload = payload;
                    this.loaded = true;
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    };

    list.dataSourceUrl = dataSourceUrl;
    list.options = options;

    return list;
}

export default listDataSource;
