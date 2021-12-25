<template>
    <field-input
        :name="name"
        :title="title"
        :original="original"
        :valid="valid"
        :type="type"
        :autocomplete="autocomplete"
        :model-value="value"
        :errors="errors"
        :disabled="disabled"
        :required="required"
        @changed="changed"
    />
</template>

<script>
import FieldInput from "../Fields/FieldInput";
import empty from "../../Helpers/Lib/empty";

export default {
    props: {
        datasource: Object,
        name: String,
        disabled: {type: Boolean, default: false},
        type: {type: String, default: 'text'},
        autocomplete: {type: String, default: 'off'},
    },

    emits: ['changed'],

    components: {
        FieldInput,
    },

    computed: {
        title() {
            return this.getValue('titles', this.name);
        },
        value() {
            return this.getValue('values', null);
        },
        original() {
            return this.getValue('originals', null);
        },
        valid() {
            return !this.datasource.loaded ? true : this.getValue('valid', true);
        },
        errors() {
            return this.getValue('validation_errors', []);
        },
        required() {
            const rules = this.getValue('validation_rules', null);

            return empty(rules) ? false : Object.keys(rules).some(rule => rule === 'required');
        },
    },

    methods: {
        getValue(section, def) {
            if (!this.datasource.loaded) {
                return null;
            }
            return typeof this.datasource[section][this.name] !== "undefined" ? this.datasource[section][this.name] : def;
        },

        changed(name, value) {
            this.datasource.values[name] = value;
            this.datasource.validate(name, value);
            this.$emit('changed', name, value);
        },
    }
}
</script>
