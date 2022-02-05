<template>
    <label class="input-search" :class="{'input-search__dirty': isDirty, 'input-search__disabled': disabled}">
        <span class="input-search__icon">
            <IconSearch/>
        </span>
        <input
            class="input-search__input"
            :autocomplete="'off'"
            :value="modelValue"
            :disabled="disabled"
            @input="change"
            @keyup.enter="search"
            ref="input"
        >
        <span class="input-search__clear" :class="{'input-search__clear-enabled': clearable && !disabled}" @click.stop.prevent="clear">
            <IconCross/>
        </span>
    </label>
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import IconCross from "@/Components/Icons/IconCross";
import IconSearch from "@/Components/Icons/IconSearch";

export default {
    components: {IconSearch, IconCross},

    props: {
        modelValue: {type: String, default: null},
        original: {type: String, default: null},

        disabled: {type: Boolean, default: false},
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
            this.$emit(this.modelValue);
        },
        clear() {
            if (this.clearable && !this.disabled) {
                this.set(null);
            } else {
                this.focus();
            }
        },
        set(value) {
            if (empty(value)) {
                value = null;
            }
            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        }
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_size_unit: 35px !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$input_color: #1e1e1e !default;
$input_border_color: #b7b7b7 !default;
$input_dirty_color: #f1f7ff !default;
$input_disabled_color: #626262 !default;
$input_disabled_background_color: #e5e5e5 !default;
$input_icon_color: #ababab !default;
$input_remove_color: #FF1E00 !default;

.input-search {
    border-radius: 2px;
    border: 1px solid $input_border_color;
    box-sizing: border-box;
    color: $input_color;
    cursor: text;
    display: flex;
    flex-direction: row;
    height: $base_size_unit;
    width: 100%;

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
    }

    &__dirty {
        background-color: $input_dirty_color;
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

    &__disabled {
        background-color: $input_disabled_background_color;
        color: $input_disabled_color;
        cursor: not-allowed;
    }
}
</style>
