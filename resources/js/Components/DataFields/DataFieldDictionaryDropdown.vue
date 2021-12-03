<template>
    <field-dictionary-drop-down
        :name="name"
        :dictionary="dictionary"
        :title="title"
        :original="original"
        :valid="valid"
        :model-value="value"
        :errors="errors"
        :disabled="disabled"
        :required="required"
        @changed="changed"
    />
</template>

<script>
import FieldDictionaryDropDown from "../Fields/FieldDictionaryDropDown";
import empty from "../../Helpers/Lib/empty";

export default {
    props: {
        datasource: Object,
        name: String,
        disabled: {type: Boolean, default: false},
        dictionary: String,
    },

    components: {
        FieldDictionaryDropDown,
    },

    computed: {
        title() {
            return this.getValue('titles', this.name);
        },
        value() {
            let value = this.getValue('values', null);
            if (typeof value === "string") {
                value = parseInt(value);
            }
            return value;
        },
        original() {
            let value = this.getValue('originals', null);
            if (typeof value === "string") {
                value = parseInt(value);
            }
            return value;
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
            this.datasource.validateAll();
        },
    }
}
</script>
