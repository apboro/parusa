<template>
    <FieldWrapper :title="title" :required="required" :disabled="disabled" :valid="valid" :errors="errors">
        <InputDaysOfWeek
            v-model="proxyValue"
            :name="name"
            :original="original"
            :valid="valid"
            :disabled="disabled"
            :small="small"
            @change="change"
            ref="input"
        />
    </FieldWrapper>
</template>

<script>
import FieldWrapper from "@/Components/Fields/Helpers/FieldWrapper";
import InputDaysOfWeek from "@/Components/Inputs/InputDaysOfWeek";

export default {
    components: {InputDaysOfWeek, FieldWrapper},
    props: {
        name: {type: String, required: true},
        modelValue: {type: Array, default: null},
        original: {type: Array, default: null},

        title: {type: String, default: null},
        required: {type: Boolean, default: false},
        disabled: {type: Boolean, default: false},
        valid: {type: Boolean, default: true},
        errors: {type: Array, default: null},

        small: {type: Boolean, default: false},
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

