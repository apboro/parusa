<script>
export default {
    data: () => ({
        deleting: false,
    }),
    methods: {
        deleteEntry(confirmation, url, options) {
            return new Promise((resolve, reject) => {
                this.$dialog.show(confirmation, 'question', 'red', [
                    this.$dialog.button('no', 'Отмена', 'blue'),
                    this.$dialog.button('yes', 'Продолжить', 'red'),
                ]).then(result => {
                    if (result === 'yes') {
                        // delete logic
                        this.deleting = true;
                        axios.post(url, options)
                            .then((response) => {
                                this.deleting = false;
                                this.$dialog.show(response.data.data, 'success', 'green', [
                                    this.$dialog.button('ok', 'OK', 'blue')
                                ], 'center')
                                    .finally(() => {
                                        resolve();
                                    });
                            })
                            .catch(error => {
                                this.deleting = false;
                                let message;
                                if (error.response.status === 404) {
                                    message = 'Запись не найдена';
                                } else {
                                    message = typeof error.response.data.status !== "undefined" ? error.response.data.status : error.response.data;
                                }
                                this.$dialog.show(message, 'error', 'red', [
                                    this.$dialog.button('ok', 'OK', 'blue')
                                ], 'center').finally(() => reject());
                            });
                    }
                });
            });
        }
    }
}
</script>
