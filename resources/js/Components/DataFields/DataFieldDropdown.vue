<template>
    <field-drop-down
        :name="name"
        :options="options"
        :key-by="keyBy"
        :value-by="valueBy"
        :title="title"
        :original="original"
        :valid="valid"
        :model-value="value"
        :errors="errors"
        :disabled="disabled"
        :required="required"
        :placeholder="placeholder"
        :has-null="hasNull"
        :to-top="toTop"
        @changed="changed"
    />
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import FieldDropDown from "../FieldsOld/FieldDropDown";

export default {
    props: {
        datasource: Object,
        name: String,
        disabled: {type: Boolean, default: false},

        options: {type: Array, default: () => ([])},
        keyBy: {type: String, default: null},
        valueBy: {type: String, default: null},
        placeholder: {type: String, default: null},
        hasNull: {type: Boolean, default: false},
        toTop: {type: Boolean, default: false},
    },

    components: {
        FieldDropDown,
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
        },
    }
}
</script>
