<template>
    <label class="input-text" :class="{'input-text__dirty': isDirty, 'input-text__disabled': disabled, 'input-text__error': !valid}">
        <textarea
            class="input-text__input"
            :class="{'input-text__input-small': small}"
            :value="modelValue"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="update"
            @click.stop.prevent="focus"
            ref="input"/>
    </label>
</template>

<script>

export default {
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
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$input_color: #1e1e1e !default;
$input_border_color: #b7b7b7 !default;
$input_dirty_color: #f1f7ff !default;
$input_placeholder_color: #757575 !default;
$input_disabled_color: #626262 !default;
$input_disabled_background_color: #f3f3f3 !default;
$input_error_color: #FF1E00 !default;
$input_background_color: #ffffff !default;
$input_hover_color: #6fb4f7 !default;
$input_active_color: #0f82f1 !default;

.input-text {
    display: flex;
    width: 100%;
    border: 1px solid $input_border_color;
    border-radius: 2px;
    box-sizing: border-box;
    color: $input_color;
    cursor: text;
    position: relative;
    background-color: $input_background_color;
    transition: border-color $animation $animation_time;

    &:not(&__disabled):hover {
        border-color: $input_hover_color;
    }

    &:not(&__disabled):focus-within {
        border-color: $input_active_color;
    }

    &__dirty {
        background-color: $input_dirty_color;
    }

    &__error {
        border-color: $input_error_color;
    }

    &__disabled {
        background-color: $input_disabled_background_color;
        color: $input_disabled_color;
        cursor: not-allowed;
    }

    &__input {
        border: none !important;
        outline: none !important;
        box-sizing: border-box;
        min-height: $base_size_unit * 3;
        line-height: $base_size_unit * 0.75;
        font-family: $project_font;
        font-size: 16px;
        color: inherit;
        padding: 0 10px;
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
