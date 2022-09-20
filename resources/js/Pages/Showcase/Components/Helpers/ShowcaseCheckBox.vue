<template>
    <label class="ap-checkbox" :class="{'ap-checkbox__disabled': disabled, 'ap-checkbox__error': !valid}">
        <input class="ap-checkbox__input" type="checkbox"
               v-model="proxyValue"
               :value="value"
               :disabled="disabled"
        >
        <span class="ap-checkbox__check">
            <IconCheck class="ap-checkbox__check-checked"/>
        </span>
        <span class="ap-checkbox__label" v-if="!label" :class="{'ap-checkbox__label-small': small}"><slot/></span>
        <span class="ap-checkbox__label" v-else :class="{'ap-checkbox__label-small': small}">{{ label }}</span>
    </label>
</template>

<script>
import IconCheck from "@/Components/Icons/IconCheck";

export default {
    components: {IconCheck},
    props: {
        modelValue: {type: [String, Number, Boolean, Array], default: null},
        label: {type: String, default: null},
        value: {type: [String, Number, Boolean], default: null},
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: false},
        small: {type: Boolean, default: false},
    },

    emits: ['update:modelValue'],

    computed: {
        proxyValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
    }
}
</script>

<style lang="scss" scoped>
@import "../../variables";

.ap-checkbox {
    height: 100%;
    display: flex;
    align-items: center;
    cursor: pointer;
    position: relative;

    &__disabled {
        cursor: not-allowed;
    }

    &__input {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        width: 0;
        height: 0;
    }

    &__check {
        width: 16px;
        height: 16px;
        border: 1px solid $showcase_placeholder_color;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        &-checked {
            color: inherit;
            display: none;
            width: 80%;
            height: 80%;
        }
    }

    &__error:not(&__disabled) &__check {
        border-color: $showcase_primary_color !important;
    }

    &__disabled &__check {
        border-color: transparentize($showcase_disabled_color, 0.5) !important;
        color: $showcase_disabled_color !important;
        background-color: transparentize($showcase_disabled_color, 0.75) !important;
    }

    &__input:checked + &__check {
        border-color: $showcase_primary_color;
        background-color: $showcase_primary_color;
        color: $showcase_white_color;
    }

    &__input:checked + &__check > &__check-checked {
        display: block;
    }

    &__label {
        margin: 0 7px 0 7px;
        font-size: 16px;
        font-family: $showcase_font;
        display: inline-block;
        color: $showcase_text_color;
        position: relative;
        top: 1px;
        @include no_selection;

        &-small {
            font-size: 14px;
        }
    }

    &__disabled &__label {
        color: $showcase_disabled_color;
    }
}
</style>
