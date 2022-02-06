<template>
    <GuiTabs
        :tabs="tabs"
        :initial="current"
        @change="change"
    />
</template>

<script>
import GuiTabs from "@/Components/GUI/GuiTabs";

export default {
    components: {GuiTabs},
    props: {
        tabs: {type: Object, default: null},
        initial: {type: String, default: null},
    },

    emits: ['change'],

    data: () => ({
        current: null,
    }),

    created() {
        if (this.$route.hash === '') {
            this.current = this.initial;
        } else {
            this.current = this.$route.hash.replace('#', '');
        }
    },

    methods: {
        change(value) {
            history.replaceState(
                {},
                null,
                this.$route.path + '#' + value
            );
            this.current = value;
            this.$emit('change', value);
        },
    }
}
</script>
