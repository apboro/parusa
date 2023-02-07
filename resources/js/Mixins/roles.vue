<script>
export default {
    methods: {
        hasRole(role) {
            // console.log(role)
            if (typeof role === "string") {
                return this.$store.getters['roles/hasRole'](role);
            }
            if (typeof role === "object" && role !== null) {
                return Object.keys(role).some(key => this.$store.getters['roles/hasRole'](role[key]));
            }
        },
    }
}
</script>
