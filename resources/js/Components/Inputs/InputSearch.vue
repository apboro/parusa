<template>
    <label class="input-search" :class="{'input-search__dirty': isDirty}">
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
        <span class="input-search__clear" :class="{'input-search__clear-enabled': clearable}" @click.stop.prevent="clear"><IconCross/></span>
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
            if (this.clearable) {
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
$input_icon_color: #ababab !default;
$input_remove_color: #e71c1c !default;

.input-search {
    display: flex;
    flex-direction: row;
    width: 100%;
    box-sizing: border-box;
    cursor: text;
    height: $base_size_unit;
    border: 1px solid $input_border_color;
    border-radius: 2px;

    &__input {
        border: none !important;
        outline: none !important;
        box-sizing: border-box;
        height: 100%;
        line-height: $base_size_unit;
        font-family: $project_font;
        font-size: 16px;
        color: $input_color;
        padding: 0;
        flex-grow: 1;
        flex-shrink: 1;
        width: 100%;
        background-color: transparent;
        display: block;
    }

    &__dirty {
        background-color: $input_dirty_color;
    }

    &__icon, &__clear {
        height: 100%;
        width: $base_size_unit;
        flex-grow: 0;
        flex-shrink: 0;
        box-sizing: border-box;
        padding: math.div($base_size_unit, 5);

        & > svg {
            width: 100%;
            height: 100%;
        }
    }

    &__icon {
        margin-right: 2px;
        color: $input_icon_color;
    }

    &__clear {
        color: $input_remove_color;
        transition: opacity $animation $animation_time;
        opacity: 0;

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
