<template>
    <base-drop-down-multi
        v-model="proxyValue"
        :options="items"
        :name="name"
        :original="original"
        :key-by="keyBy"
        :value-by="valueBy"
        :placeholder="placeholder"
        :to-top="toTop"
        @dropped="refresh"
        @changed="changed"
    />
</template>

<script>
import BaseDropDownMulti from "../Base/BaseDropDownMulti";

export default {
    props: {
        modelValue: {type: Array, default: () => ([])},
        original: {type: Array, default: () => ([])},
        name: String,

        placeholder: {type: String, default: null},

        keyBy: {type: String, default: 'id'},
        valueBy: {type: String, default: 'name'},

        toTop: {type: Boolean, default: false},

        fresh: {type: Boolean, default: false},

        dictionary: String,
    },

    emits: ['update:modelValue', 'changed'],

    components: {
        BaseDropDownMulti,
    },

    computed: {
        proxyValue: {
            get() {
                return this.ready ? this.modelValue : [];
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
        items() {
            if (!this.ready) {
                return [];
            }
            return this.$store.getters['dictionary/dictionary'](this.dictionary);
        },
        ready() {
            return this.$store.getters['dictionary/ready'](this.dictionary);
        },
    },

    data: () => ({
        loaded: false,
    }),

    created() {
        this.refresh();
    },

    methods: {
        refresh() {
            if (this.loaded && !this.fresh) {
                return;
            }
            this.$store.dispatch('dictionary/refresh', this.dictionary)
                .then(() => {
                    this.loaded = true;
                });
        },
        changed(name, value) {
            this.$emit('changed', name, value);
        },
    }
}
</script>

