<template>
    <FieldWrapper :title="title" :hide-title="hideTitle" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <InputNumber
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :step="step"
            :quantity="quantity"
            :min="min"
            :max="max"
            :placeholder="placeholder"
            :small="small"
            @change="change"
            ref="input"
        />
    </FieldWrapper>
</template>

<script>
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import InputNumber from "@/Components/Inputs/InputNumber";

export default {
    components: {InputNumber, FieldWrapper},
    props: {
        name: String,
        modelValue: {type: Number, default: null},
        original: {type: Number, default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},
        hideTitle: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        step: {type: Number, default: 1},
        quantity: {type: Boolean, default: false},
        min: {type: Number, default: null},
        max: {type: Number, default: null},
    },

    emits: ['update:modelValue', 'change'],

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

