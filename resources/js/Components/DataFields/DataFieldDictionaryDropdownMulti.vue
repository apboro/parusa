<template>
    <field-dictionary-drop-down-multi
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
import empty from "@/Core/Helpers/Empty";
import FieldDictionaryDropDownMulti from "../Fields/FieldDictionaryDropDownMulti";

export default {
    props: {
        datasource: Object,
        name: String,
        disabled: {type: Boolean, default: false},
        dictionary: String,
    },

    components: {
        FieldDictionaryDropDownMulti,
    },

    computed: {
        title() {
            return this.getValue('titles', this.name);
        },
        value() {
            return this.getValue('values', []);
        },
        original() {
            return this.getValue('originals', []);
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
