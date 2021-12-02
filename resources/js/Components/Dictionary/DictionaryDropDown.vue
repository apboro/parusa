<template>
    <base-drop-down
        :options="items"
        v-model="val"
        :key-by="'id'"
        :value-by="'name'"
        :placeholder="placeholder"
        :has-null="hasNull"
        @dropped="refresh"
    />
</template>

<script>
import BaseDropDown from "../Base/BaseDropDown";

export default {
    props: {
        dictionary: String,
        placeholder: {
            type: String,
            default: null,
        },
        hasNull: {
            type: Boolean,
            default: false,
        },
    },

    components: {
        BaseDropDown,
    },

    data: ()=>({
        val: null,
    }),

    computed: {
        items() {
            return this.$store.getters['dictionary/dictionary'](this.dictionary);
        },
        ready() {
            return this.$store.getters['dictionary/ready'](this.dictionary);
        },
    },

    created() {
        this.refresh();
    },

    methods: {
        refresh() {
            this.$store.dispatch('dictionary/refresh', this.dictionary);
        }
    }
}
</script>

