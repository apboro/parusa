<template>
    <label class="input-wrapper" v-if="label"
           :class="{'input-wrapper__dirty': dirty, 'input-wrapper__disabled': disabled, 'input-wrapper__error': !valid, 'input-wrapper__focus': hasFocus}"
    >
        <slot/>
    </label>
    <div class="input-wrapper" v-else
         :class="{'input-wrapper__dirty': dirty, 'input-wrapper__disabled': disabled, 'input-wrapper__error': !valid, 'input-wrapper__focus': hasFocus}"
    >
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: true},
        dirty: {type: Boolean, default: true},
        label: {type: Boolean, default: true},
        hasFocus: {type: Boolean, default: false},
    },
}
</script>

<style lang="scss">

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$animation_time: 150ms !default;
$input_color: #1e1e1e !default;
$input_border_color: #b7b7b7 !default;
$input_dirty_color: #f1f7ff !default;
$input_disabled_color: #626262 !default;
$input_disabled_background_color: #f3f3f3 !default;
$input_error_color: #FF1E00 !default;
$input_background_color: #ffffff !default;
$input_hover_color: #6fb4f7 !default;
$input_active_color: #0f82f1 !default;

.input-wrapper {
    background-color: $input_background_color;
    border-radius: 2px;
    border: 1px solid $input_border_color;
    box-sizing: border-box;
    color: $input_color;
    cursor: text;
    display: flex;
    font-family: $project_font;
    position: relative;
    transition: border-color $animation $animation_time;
    width: 100%;

    &:not(&__disabled):hover {
        border-color: $input_hover_color;
    }

    &:not(&__disabled):focus-within, &__focus:not(&__disabled) {
        border-color: $input_active_color !important;
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
}
</style>
