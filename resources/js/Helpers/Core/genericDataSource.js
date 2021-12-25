const genericDataSource = function (dataSourceUrl) {
    let data = {
        dataSourceUrl: null,

        data: [],
        payload: null,

        loading: false,
        loaded: false,
        has_error: false,
        error_message: null,

        onLoad: null,

        load(options = {}) {
            this.loading = true;

            axios.post(this.dataSourceUrl, options)
                .then(response => {
                    this.data = response.data.data;
                    this.payload = typeof response.data.payload !== "undefined" ? response.data.payload : null;
                    if (typeof this.onLoad === "function") {
                        this.onLoad(this.data, this.payload);
                    }
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

    data.dataSourceUrl = dataSourceUrl;

    return data;
}

export default genericDataSource;
