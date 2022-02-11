<template>
    <InputWrapper class="input-date" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <input
            class="input-date__input"
            maxlength="10"
            :class="{'input-date__input-small': small}"
            :autocomplete="'off'"
            :value="modelValue"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="change"
            @click="toggle"
            ref="input"
        />
        <span class="input-date__clear" :class="{'input-date__clear-enabled': clearable && !disabled}" @click.stop.prevent="clear">
            <IconCross/>
        </span>
        <div class="input-date__picker" :class="{'input-date__picker-shown': picker}">
            <DatePicker
                :date="modelValue"
                :from="from"
                :to="to"
                @selected="picked"
            />
        </div>
    </InputWrapper>
</template>

<script>
import empty from "@/Core/Helpers/Empty";
import IconCross from "@/Components/Icons/IconCross";
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";
import DatePicker from "@/Components/Inputs/Helpers/DatePicker";

export default {
    components: {DatePicker, InputWrapper, IconCross},

    props: {
        modelValue: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        pickOnClear: {type: Boolean, default: true},

        from: {type: String, default: null},
        to: {type: String, default: null},
    },

    emits: ['update:modelValue', 'change'],

    data: () => ({
        picker: false,
        dropping: false,
    }),

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
        clear() {
            if (this.clearable && !this.disabled) {
                this.set(null);
                if (this.pickOnClear && !this.picker) {
                    this.toggle();
                }
            }
            this.focus();
        },
        set(value) {
            if (empty(value)) {
                value = null;
            }
            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        },
        picked(value) {
            this.set(value);
            this.$refs.input.focus();
            this.$nextTick(() => {
                this.picker = false;
            });
        },
        toggle() {
            if (this.disabled) return;
            if (this.picker === true) {
                this.close();
            } else {
                this.picker = true;
                this.dropping = true;
                document.addEventListener('click', this.close);
            }
        },
        close() {
            if (this.dropping === true) {
                this.dropping = false;
            } else {
                document.removeEventListener('click', this.close);
                this.picker = false;
            }
        },
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
$input_background_color: #ffffff !default;

.input-date {
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
        padding: 0 0 0 10px;
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

    &__clear {
        box-sizing: border-box;
        flex-grow: 0;
        flex-shrink: 0;
        height: 100%;
        padding: math.div($base_size_unit, 5);
        width: $base_size_unit;
        color: $input_remove_color;
        opacity: 0;
        transition: opacity $animation $animation_time;

        & > svg {
            height: 100%;
            width: 100%;
        }

        &-enabled {
            cursor: pointer;
            opacity: 0.6;

            &:hover {
                opacity: 1;
            }
        }
    }

    &__picker {
        position: absolute;
        left: -1px;
        bottom: -6px;
        transform: translateY(100%);
        box-sizing: border-box;
        padding: 12px 20px;
        border-radius: 2px;
        z-index: 50;
        background-color: $input_background_color;
        box-shadow: $shadow_2;
        opacity: 0;
        visibility: hidden;
        transition: opacity $animation $animation_time, visibility $animation $animation_time;

        &:before {
            content: '';
            display: block;
            background-color: $input_background_color;
            width: 6px;
            height: 6px;
            position: absolute;
            left: 14px;
            top: -4px;
            transform: rotate(45deg);
            border-color: #e9e9e9;
            border-style: solid;
            border-width: 1px 0 0 1px;
        }

        &-shown {
            opacity: 1;
            visibility: visible;
        }
    }
}
</style>
