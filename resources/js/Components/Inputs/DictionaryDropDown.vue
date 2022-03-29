<template>
    <InputDropDown
        :name="name"
        v-model="proxyValue"
        :original="original"
        :valid="valid"
        :disabled="disabled"
        :has-null="hasNull"
        :placeholder="placeholder"
        :disabled-options="disabledOptions"
        :identifier="identifier"
        :show="show"
        :multi="multi"
        :search="search"
        :top="top"
        :right="right"
        :center="center"
        :small="small"
        :options="items"
        @drop="refresh"
        @change="change"
    />
</template>

<script>
import InputDropDown from "@/Components/Inputs/InputDropDown";

export default {
    props: {
        name: String,
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        original: {type: [Boolean, String, Number, Object], default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        hasNull: {type: Boolean, default: false},
        placeholder: {type: String, default: null},

        disabledOptions: {type: Boolean, default: false},
        identifier: {type: String, default: 'id'},
        show: {type: String, default: 'name'},

        search: {type: Boolean, default: false},
        top: {type: Boolean, default: false},
        right: {type: Boolean, default: false},
        center: {type: Boolean, default: false},
        small: {type: Boolean, default: false},

        multi: {type: Boolean, default: false},

        dictionary: String,
        fresh: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change'],

    components: {InputDropDown},

    computed: {
        proxyValue: {
            get() {
                return this.ready ? this.modelValue : null;
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
            return this.$store.getters['dictionary/ready'](this.dictionary) !== null;
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
        change(value, name) {
            this.$emit('change', value, name);
        },
    }
}
</script>

