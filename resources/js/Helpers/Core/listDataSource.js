import initialPagination from "./initialPagination";

const listDataSource = function (dataSourceUrl, usePagination = true) {
    let list = {
        dataSourceUrl: null,

        titles: [],
        data: [],

        search: null,
        filters: {},
        order: null,
        order_by: null,
        pagination: usePagination ? initialPagination : null,

        payload: null,

        loading: false,
        has_error: false,
        error_message: null,

        load(page = 1, perPage = null, initial = false) {
            this.loading = true;

            const options = {
                filters: this.filters,
                search: this.search,
                order: this.order,
                order_by: this.order_by,
                page: page,
                per_page: perPage === null ? this.pagination.per_page : perPage,
                initial: initial,
            }

            axios.post(this.dataSourceUrl, options)
                .then(response => {
                    this.data = response.data.list;
                    this.titles = typeof response.data.titles !== "undefined" ? response.data.titles : null;
                    this.pagination = typeof response.data.pagination !== "undefined" && usePagination ? response.data.pagination : null;
                    let payload = typeof response.data.payload !== "undefined" ? response.data.payload : null;
                    if (typeof payload['filters'] !== "undefined") {
                        this.filters = payload['filters'];
                        delete payload['filters'];
                    }
                    this.payload = payload;
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

    return list;
}

export default listDataSource;
