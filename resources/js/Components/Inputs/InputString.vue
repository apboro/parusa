<template>
    <label class="input-string" :class="{'input-string__dirty': isDirty, 'input-string__disabled': disabled, 'input-string__error': !valid}">
        <input
            class="input-string__input"
            :value="modelValue"
            :type="type"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            @input="update"
            @click.stop.prevent="focus"
            ref="input">
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

        type: {type: String, default: 'text', validation: (value) => ['text', 'password'].indexOf(value) !== -1},
        autocomplete: {type: String, default: 'off'},
        placeholder: {type: String, default: null},
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
$input_color: #1e1e1e !default;
$input_border_color: #b7b7b7 !default;
$input_dirty_color: #f1f7ff !default;
$input_placeholder_color: #757575 !default;
$input_disabled_color: #626262 !default;
$input_disabled_background_color: #e5e5e5 !default;
$input_error_color: #FF1E00 !default;
$input_background_color: #ffffff !default;

.input-string {
    display: flex;
    width: 100%;
    height: $base_size_unit;
    border: 1px solid $input_border_color;
    border-radius: 2px;
    box-sizing: border-box;
    color: $input_color;
    cursor: text;
    position: relative;
    background-color: $input_background_color;

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
        height: $base_size_unit;
        line-height: $base_size_unit;
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
