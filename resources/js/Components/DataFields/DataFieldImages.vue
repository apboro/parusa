<template>
    <field-images
        :name="name"
        :title="title"
        :original="original"
        :valid="valid"
        :model-value="value"
        :errors="errors"
        :disabled="disabled"
        :required="required"
        :max-images="maxImages"
        @changed="changed"
    />
</template>

<script>
import empty from "../../Helpers/Lib/empty";
import FieldImages from "../Fields/FieldImages";

export default {
    props: {
        datasource: Object,
        name: String,
        disabled: {type: Boolean, default: false},
    },

    components: {
        FieldImages,
    },

    computed: {
        title() {
            return this.getValue('titles', this.name);
        },
        value() {
            return this.getValue('values', []);
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
            const rules = this.getValue('validation_rules', {});

            return empty(rules) ? false : Object.keys(rules).some(rule => rule === 'required');
        },
        maxImages() {
            let max = 0;
            const rules = this.getValue('validation_rules', {});
            if (rules !== null && typeof rules !== "undefined") {
                Object.keys(rules).some(rule => {
                    if (rule === 'max') {
                        max = rules[rule];
                        return true;
                    }
                    return false;
                });
            }
            return Number(max);
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
