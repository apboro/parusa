<template>
    <InputWrapper class="input-phone" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <IMaskComponent
            class="input-phone__input"
            :class="{'input-phone__input-small': small}"
            v-model="innerValue"
            :mask="'+{7} (000) 000-00-00'"
            radix="."
            :placeholder="placeholderProxy"
            :autocomplete="autocomplete"
            @focus="focus"
            @blur="blur"
            @update:masked="typed"
            @complete="complete"
            ref="input"
        />
    </InputWrapper>
</template>

<script>
import InputWrapper from "@/Components/Inputs/Helpers/InputWrapper";
import {IMaskComponent} from 'vue-imask';

export default {
    components: {InputWrapper, IMaskComponent},

    props: {
        name: String,
        modelValue: {type: String, default: null},
        original: {type: String, default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},

        type: {type: String, default: 'text', validation: (value) => ['text', 'password'].indexOf(value) !== -1},
        autocomplete: {type: String, default: 'off'},
        placeholder: {type: String, default: null},
        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'change'],

    data: () => ({
        focused: false,
        innerValue: '',
        innerInitialized: false,
        innerChange: false,
    }),

    computed: {
        placeholderProxy() {
            return this.focused ? null : this.placeholder;
        },
        isDirty() {
            return this.original !== this.modelValue;
        },
    },

    watch: {
        modelValue(value) {
            if (!this.innerChange) {
                this.innerValue = value === null ? '' : value;
                return;
            }
            this.innerChange = false;
        }
    },

    created() {
        this.innerValue = this.modelValue === null ? '' : this.modelValue;
    },

    methods: {
        focus() {
            this.focused = true;
        },
        blur() {
            this.focused = false;
        },
        typed(value) {
            if (!this.innerInitialized) {
                this.innerInitialized = true;
                return;
            }
            if (value.length !== 18) {
                this.update(null);
            }
        },
        complete(value) {
            if (!this.innerInitialized) {
                this.innerInitialized = true;
                return;
            }
            this.update(value);
        },
        update(value) {
            value = value !== '' && value !== null ? String(value) : null;
            this.innerChange = true;
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
$input_placeholder_color: #757575 !default;

.input-phone {
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
