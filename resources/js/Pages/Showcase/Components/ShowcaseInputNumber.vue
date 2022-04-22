<template>
    <ShowcaseInputWrapper class="ap-input-number" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <span class="ap-input-number__decrease" v-if="quantity"
              :class="{'ap-input-number__decrease-disabled': disabled}"
              tabindex="-1"
              @click="decrease"
        >
            <IconMinus/>
        </span>
        <input
            class="ap-input-number__input"
            :class="{'ap-input-number__input-small': small, 'ap-input-number__input-quantity': quantity}"
            :value="modelValue"
            :type="'number'"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="update"
            ref="input"
        />
        <span class="ap-input-number__increase" v-if="quantity"
              :class="{'ap-input-number__increase-disabled': disabled}"
              tabindex="-1"
              @click="increase"
        >
            <IconPlus/>
        </span>
    </ShowcaseInputWrapper>
</template>

<script>
import IconMinus from "@/Components/Icons/IconMinus";
import IconPlus from "@/Components/Icons/IconPlus";
import ShowcaseInputWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseInputWrapper";

export default {
    components: {ShowcaseInputWrapper, IconPlus, IconMinus},
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

.ap-input-number {
    height: $showcase_size_unit;

    &__input {
        border: none !important;
        outline: none !important;
        box-sizing: border-box;
        height: $showcase_size_unit;
        line-height: $showcase_size_unit;
        font-family: $showcase_font;
        font-size: 16px;
        color: $showcase_link_color;
        padding: 0.15em 10px 0;
        flex-grow: 1;
        flex-shrink: 1;
        width: 100%;
        background-color: transparent;
        display: block;
        cursor: inherit;
        font-weight: bold;

        &-small {
            font-size: 14px;
        }

        &-quantity {
            padding: 0;
            text-align: center;
        }

        &::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: $showcase_placeholder_color;
            opacity: 1; /* Firefox */
            font-weight: normal;
        }

        &:-ms-input-placeholder { /* Internet Explorer 10-11 */
            color: $showcase_placeholder_color;
            font-weight: normal;
        }

        &::-ms-input-placeholder { /* Microsoft Edge */
            color: $showcase_placeholder_color;
            font-weight: normal;
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
        width: $showcase_size_unit;
        height: $showcase_size_unit;
        padding: $showcase_size_unit * 0.25;
        flex-grow: 0;
        flex-shrink: 0;
        box-sizing: border-box;
        cursor: pointer;
        background-color: transparent;
        color: $showcase_text_color;
        transition: color $animation $animation_time,;
        @include no_selection;

        &:not(&-disabled):hover {
            color: $showcase_primary_hover_color;
        }

        &-disabled {
            color: $showcase_disabled_color;
            cursor: not-allowed;
        }
    }
}
</style>
