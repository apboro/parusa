const genericDataSource = function (dataSourceUrl) {
    let data = {
        dataSourceUrl: null,

        data: [],
        payload: null,

        loading: false,
        has_error: false,
        error_message: null,

        load(options = {}) {
            this.loading = true;

            axios.post(this.dataSourceUrl, options)
                .then(response => {
                    this.data = response.data.data;
                    this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : null;
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    };

    data.dataSourceUrl = dataSourceUrl;

    return data;
}

export default genericDataSource;
