<template>
    <InputWrapper class="input-search" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <span class="input-search__icon">
            <IconSearch/>
        </span>
        <input
            class="input-search__input"
            :class="{'input-search__input-small': small}"
            :autocomplete="'off'"
            :value="modelValue"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="change"
            @keyup.enter="search"
            ref="input"
        />
        <span class="input-search__clear" :class="{'input-search__clear-enabled': clearable && !disabled}" @click.stop.prevent="clear">
            <IconCross/>
        </span>
    </InputWrapper>
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import IconCross from "@/Components/Icons/IconCross";
import IconSearch from "@/Components/Icons/IconSearch";
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";

export default {
    components: {InputWrapper, IconSearch, IconCross},

    props: {
        name: String,
        modelValue: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change', 'search'],

    computed: {
        clearable() {
            return !empty(this.modelValue);
        },
        isDirty() {
            return empty(this.original) ? !empty(this.modelValue) : this.original !== this.modelValue;
        },
    },

    methods: {
        focus() {
            this.$refs.input.focus()
        },
        change(event) {
            this.set(event.target.value);
        },
        search() {
            this.$emit('search', this.modelValue);
        },
        clear() {
            if (this.clearable && !this.disabled) {
                this.set(null);
                this.search();
            }
            this.focus();
        },
        set(value) {
            if (empty(value)) {
                value = null;
            }
            this.$emit('update:modelValue', value);
            this.$emit('change', value, this.name);
        }
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$input_placeholder_color: #757575 !default;
$input_icon_color: #ababab !default;
$input_remove_color: #FF1E00 !default;

.input-search {
    height: $base_size_unit;

    &__input {
        background-color: transparent;
        border: none !important;
        box-sizing: border-box;
        color: inherit;
        cursor: inherit;
        display: block;
        flex-grow: 1;
        flex-shrink: 1;
        font-family: $project_font;
        font-size: 16px;
        height: 100%;
        line-height: $base_size_unit;
        outline: none !important;
        padding: 0;
        width: 100%;

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

    &__icon, &__clear {
        box-sizing: border-box;
        flex-grow: 0;
        flex-shrink: 0;
        height: 100%;
        padding: math.div($base_size_unit, 5);
        width: $base_size_unit;

        & > svg {
            height: 100%;
            width: 100%;
        }
    }

    &__icon {
        color: $input_icon_color;
        margin-right: 2px;
    }

    &__clear {
        color: $input_remove_color;
        opacity: 0;
        transition: opacity $animation $animation_time;

        &-enabled {
            cursor: pointer;
            opacity: 0.6;

            &:hover {
                opacity: 1;
            }
        }
    }
}
</style>
