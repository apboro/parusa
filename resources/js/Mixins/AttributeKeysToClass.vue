<script>
export default {
    data: () => ({
        attributesClassPrefix: null,
        attributesClassExcept: [],
    }),

    computed: {
        classFromAttributes() {
            let keys = Object.keys(this.$attrs);
            if(this.attributesClassPrefix !== null) {
                keys = keys.map(key => this.attributesClassPrefix + key);
            }
            if(this.attributesClassExcept !== []) {
                keys = keys.filter(key => this.attributesClassExcept.indexOf(key) === -1);
            }

            return keys;
        },

        classFromExceptedAttributes() {
            let keys = Object.keys(this.$attrs);
            if(this.attributesClassPrefix !== null) {
                keys = keys.map(key => this.attributesClassPrefix + key);
            }
            if(this.attributesClassExcept !== []) {
                keys = keys.filter(key => this.attributesClassExcept.indexOf(key) === -1);
            }

            return keys;
        }
    }
}
</script>
