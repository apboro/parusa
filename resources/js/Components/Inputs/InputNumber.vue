<template>
    <InputWrapper class="input-number" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <span class="input-number__decrease" v-if="quantity"
              :class="{'input-number__decrease-disabled': disabled}"
              tabindex="-1"
              @click="decrease"
        >
            <IconMinus/>
        </span>
        <input
            class="input-number__input"
            :class="{'input-number__input-small': small, 'input-number__input-quantity': quantity}"
            :value="modelValue"
            :type="'number'"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="update"
            ref="input"
        />
        <span class="input-number__increase" v-if="quantity"
              :class="{'input-number__increase-disabled': disabled}"
              tabindex="-1"
              @click="increase"
        >
            <IconPlus/>
        </span>
    </InputWrapper>
</template>

<script>
import IconMinus from "@/Components/Icons/IconMinus";
import IconPlus from "@/Components/Icons/IconPlus";
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";

export default {
    components: {InputWrapper, IconPlus, IconMinus},
    props: {
        name: String,
        modelValue: {type: Number, default: null},
        original: {type: Number, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        step: {type: Number, default: 1},
        quantity: {type: Boolean, default: false},

        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},

        min: {type: Number, default: null},
        max: {type: Number, default: null},
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
            let value = event.target.value !== '' ? Number(event.target.value) : null;
            this.$emit('update:modelValue', value);
            this.$emit('change', value, this.name);
        },

        decrease() {
            if (this.disabled || this.min !== null && (this.min > this.modelValue - this.step)) return;
            this.$refs.input.focus();
            this.$emit('update:modelValue', this.modelValue - this.step);
            this.$emit('change', this.modelValue - this.step, this.name);
        },

        increase() {
            if (this.disabled || this.max !== null && (this.max < this.modelValue + this.step)) return;
            this.$refs.input.focus();
            this.$emit('update:modelValue', this.modelValue + this.step);
            this.$emit('change', this.modelValue + this.step, this.name);
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
$input_placeholder_color: #757575 !default;
$input_disabled_color: #626262 !default;
$input_active_color: #0f82f1 !default;

.input-number {
    height: $base_size_unit;

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

        &-quantity {
            padding: 0;
            text-align: center;
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

        &::-webkit-outer-spin-button, &::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
        }

        &[type=number] {
            -moz-appearance: textfield; /* Firefox */
        }
    }

    &__increase, &__decrease {
        display: inline-flex;
        justify-content: center;
        vertical-align: top;
        width: $base_size_unit;
        height: $base_size_unit;
        padding: $base_size_unit * 0.25;
        flex-grow: 0;
        flex-shrink: 0;
        box-sizing: border-box;
        cursor: pointer;
        background-color: transparent;
        color: $input_color;
        transition: color $animation $animation_time,;
        @include no_selection;

        &:not(&-disabled):hover {
            color: $input_active_color;
        }

        &-disabled {
            color: $input_disabled_color;
            cursor: not-allowed;
        }
    }
}
</style>
