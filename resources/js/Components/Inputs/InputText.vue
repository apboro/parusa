<template>
    <InputWrapper class="input-text" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <textarea
            class="input-text__input"
            :class="{'input-text__input-small': small}"
            :value="modelValue"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="update"
            ref="input"/>
    </InputWrapper>
</template>

<script>

import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";

export default {
    components: {InputWrapper},
    props: {
        name: String,
        modelValue: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change'],

    computed: {
        isDirty() {
            return this.original !== this.modelValue;
        },
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },

        update(event) {
            let value = event.target.value !== '' ? String(event.target.value) : null;
            this.$emit('update:modelValue', value);
            this.$emit('change', value, this.name);
        },
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_size_unit: 35px !default;
$input_placeholder_color: #757575 !default;

.input-text {

    &__input {
        border: none !important;
        outline: none !important;
        box-sizing: border-box;
        min-height: $base_size_unit * 3;
        line-height: $base_size_unit * 0.75;
        font-family: $project_font;
        font-size: 16px;
        color: inherit;
        padding: 5px 10px;
        flex-grow: 1;
        flex-shrink: 1;
        width: 100%;
        background-color: transparent;
        display: block;
        cursor: inherit;
        resize: vertical;

        &-small {
            font-size: 14px;
        }

        &::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: $input_placeholder_color;
            opacity: 1; /* Firefox */
        }

        &:-ms-input-placeholder { /* Internet Explorer 10-11 */
            color: $input_placeholder_color;
        }

        &::-ms-input-placeholder { /* Microsoft Edge */
            color: $input_placeholder_color;
        }
    }
}
</style>
