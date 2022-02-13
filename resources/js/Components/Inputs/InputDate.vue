<template>
    <InputWrapper class="input-date" :dirty="isDirty" :disabled="disabled" :valid="valid" :has-focus="isFocused">
        <DateInput
            v-model="proxyValue"
            :from="from"
            :to="to"
            :placeholder="placeholder"
            :disabled="disabled"
            :small="small"
            :clearable="clearable"
            :pickOnClear="pickOnClear"
            @focus="isFocused = true"
            @blur="isFocused = false"
            ref="input"
        />
    </InputWrapper>
</template>

<script>
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";
import DateInput from "@/Components/Inputs/Helpers/DateInput";

export default {
    components: {DateInput, InputWrapper},

    props: {
        name: String,
        modelValue: {type: String, default: null},
        from: {type: String, default: null},
        to: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},
        clearable: {type: Boolean, default: false},
        pickOnClear: {type: Boolean, default: true},
    },

    emits: ['update:modelValue', 'change'],

    data: () => ({
        isFocused: false,
    }),

    computed: {
        isDirty() {
            return this.original !== this.modelValue;
        },
        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
                this.$emit('change', value, this.name);
            }
        },
    },

    methods: {
        addDays(value) {
            this.$refs.input.addDays(value);
        }
    }
}
</script>

<style lang="scss">
@import "../variables";

$base_size_unit: 35px !default;

.input-date {
    height: $base_size_unit;
}
</style>
