<script>
export default {
    data: () => ({
        attributesClassPrefix: null,
        attributesClassExcept: [],
    }),

    computed: {
        classFromAttributes() {
            let keys = Object.keys(this.$attrs);
            if (this.attributesClassExcept !== []) {
                keys = keys.filter(key => this.attributesClassExcept.indexOf(key) === -1);
            }
            if (this.attributesClassPrefix !== null) {
                keys = keys.map(key => this.attributesClassPrefix + key);
            }

            return keys;
        },

        classFromExceptedAttributes() {
            let keys = Object.keys(this.$attrs);
            if (this.attributesClassExcept !== []) {
                keys = keys.filter(key => this.attributesClassExcept.indexOf(key) !== -1);
            }

            keys = keys.map(key => typeof this.$attrs[key] !== 'undefined' ? this.$attrs[key] : key);

            if (this.attributesClassPrefix !== null) {
                keys = keys.map(key => this.attributesClassPrefix + key);
            }

            return keys;
        }
    }
}
</script>
