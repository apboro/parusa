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
                                this.$toast.success(response.data.message, 5000);
                                resolve();
                            })
                            .catch(error => {
                                this.deleting = false;
                                this.$toast.error(error.response.data.message, 5000);
                                reject();
                            });
                    }
                });
            });
        }
    }
}
</script>
