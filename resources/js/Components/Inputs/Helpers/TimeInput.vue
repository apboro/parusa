<template>
    <input class="input-time__input"
           :class="{'input-time__input-small': small}"
           v-model="display"
           :autocomplete="'off'"
           :disabled="disabled"
           :placeholder="placeholder"
           maxlength="5"
           @keydown="filterKeys"
           @focus="focus"
           @blur="blur"
           ref="input"
    />
</template>

<script>
export default {
    props: {
        modelValue: {type: String, default: null},

        placeholder: {type: String, default: null},
        disabled: {type: Boolean, default: false},

        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'focus', 'blur'],

    data: () => ({
        displayValue: null,
        innerValue: null,
        isFocused: false,
    }),

    computed: {
        display: {
            get() {
                return this.displayValue;
            },
            set(value) {
                if (value !== null && value.length === 2 && value.indexOf(':') === -1) {
                    this.displayValue = value + ':';
                } else {
                    this.displayValue = value;
                }
                const time = value !== null && value !== '' ? value.split(':') : null;
                if (time !== null) {
                    if (typeof time[0] !== "undefined" && (time[0] !== '' && time[0] >= 0 && time[0] <= 23) && typeof time[1] !== "undefined" && (time[1] !== '' && time[1] >= 0 && time[1] <= 59)) {
                        const formatted = String(time[0]).padStart(2, '0') + ':' + String(time[1]).padStart(2, '0');
                        this.$emit('update:modelValue', formatted);
                    }
                } else {
                    this.$emit('update:modelValue', null);
                }
            }
        },
    },

    watch: {
        modelValue(value) {
            this.setInner(value);
        }
    },

    created() {
        this.setInner(this.modelValue);
    },

    methods: {
        setInner(value, force = false) {
            const time = value !== null && value !== '' ? value.split(':') : null;
            const formatted = time !== null ? String(time[0]).padStart(2, '0') + ':' + String(time[1]).padStart(2, '0') : null;

            this.innerValue = formatted;
            if (!this.isFocused || force) {
                this.displayValue = this.innerValue === null ? null : formatted;
            }
        },
        filterKeys(event) {
            const accepted = [
                20, // capslock
                17, // control
                18, // option
                16, // shift
                37, 38, 39, 40, // arrow keys
                9, // tab (let blur handle tab)
                8, //backspace
                46, // delete
            ];
            if (accepted.indexOf(event.keyCode) === -1 && ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ':'].indexOf(event.key) === -1) {
                event.preventDefault();
                return true;
            }
            // console.log(this.displayValue);
            // const time = this.displayValue !== null && value !== '' ? value.split(':') : null;
            // if(time === null) return true;

            return true;
        },
        focus() {
            this.$refs.input.focus();
            this.isFocused = true;
            this.$emit('focus');
        },
        blur() {
            this.isFocused = false;
            this.setInner(this.innerValue);
            this.$emit('blur');
        },
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$input_placeholder_color: #757575 !default;
$input_icon_color: #ababab !default;
$input_remove_color: #FF1E00 !default;
$input_background_color: #ffffff !default;

.input-time {
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

        &-small {
            font-size: 14px;
        }
    }
}
</style>
