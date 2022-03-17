<template>
    <FieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <InputDropDown
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :has-null="hasNull"
            :options="options"
            :disabled-options="disabledOptions"
            :identifier="identifier"
            :show="show"
            :multi="multi"
            :search="search"
            :top="top"
            :right="right"
            :center="center"
            :placeholder="placeholder"
            :small="small"
            @change="change"
            @drop="$emit('drop')"
            ref="input"
        />
    </FieldWrapper>
</template>

<script>
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import InputDropDown from "@/Components/Inputs/InputDropDown";

export default {
    components: {InputDropDown, FieldWrapper},
    props: {
        name: String,
        modelValue: {type: [Boolean, String, Number, Object], default: null},
        original: {type: [Boolean, String, Number, Object], default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},
        hideTitle: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        options: {type: Array, default: () => ([])},
        disabledOptions: {type: Boolean, default: false},
        identifier: {type: String, default: null},
        show: {type: String, default: null},
        hasNull: {type: Boolean, default: false},

        multi: {type: Boolean, default: false},

        search: {type: Boolean, default: false},
        top: {type: Boolean, default: false},
        right: {type: Boolean, default: false},
        center: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change', 'drop'],

    computed: {
        proxyValue: {
            get() {
                return this.modelValue
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },
        change(value, name) {
            this.$emit('change', value, name);
        },
    }
}
</script>

