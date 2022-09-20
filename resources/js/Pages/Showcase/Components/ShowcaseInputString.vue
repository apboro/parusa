<template>
    <ShowcaseInputWrapper class="ap-input-string" :dirty="isDirty" :disabled="disabled" :valid="valid">
        <input
            class="ap-input-string__input"
            :class="{'ap-input-string__input-small': small}"
            :value="modelValue"
            :type="type"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            @input="update"
            ref="input"
        />
    </ShowcaseInputWrapper>
</template>

<script>

import ShowcaseInputWrapper from "@/Pages/Showcase/Components/Helpers/ShowcaseInputWrapper";

export default {
    components: {ShowcaseInputWrapper},
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

<style lang="scss" scoped>
@import "../variables";

.ap-input-string {
    height: $showcase_size_unit;

    &__input {
        border: none !important;
        outline: none !important;
        box-sizing: border-box;
        height: $showcase_size_unit;
        line-height: $showcase_size_unit;
        font-family: $showcase_font;
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
            color: $showcase_placeholder_color;
            opacity: 1; /* Firefox */
        }

        &:-ms-input-placeholder { /* Internet Explorer 10-11 */
            color: $showcase_placeholder_color;
        }

        &::-ms-input-placeholder { /* Microsoft Edge */
            color: $showcase_placeholder_color;
        }
    }
}
</style>
