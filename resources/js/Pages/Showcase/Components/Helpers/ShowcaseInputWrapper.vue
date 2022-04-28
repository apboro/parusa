<template>
    <label class="ap-input-wrapper" v-if="label" :class="{
        'ap-input-wrapper__dirty': dirty,
        'ap-input-wrapper__disabled': disabled,
        'ap-input-wrapper__error': !valid,
        'ap-input-wrapper__focus': hasFocus,
        'ap-input-wrapper__border': border
    }">
        <span class="ap-input-wrapper__icon" v-if="$slots.icon">
            <slot name="icon"/>
        </span>
        <slot/>
    </label>
    <div class="ap-input-wrapper" v-else :class="{
        'ap-input-wrapper__dirty': dirty,
        'ap-input-wrapper__disabled': disabled,
        'ap-input-wrapper__error': !valid,
        'ap-input-wrapper__focus': hasFocus,
        'ap-input-wrapper__border': border
    }">
        <span class="ap-input-wrapper__icon" v-if="$slots.icon">
            <slot name="icon"/>
        </span>
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        valid: {type: Boolean, default: true},
        disabled: {type: Boolean, default: true},
        dirty: {type: Boolean, default: true},
        label: {type: Boolean, default: true},
        hasFocus: {type: Boolean, default: false},
        border: {type: Boolean, default: true},
    },
}
</script>

<style lang="scss">
@import "../../variables";

.ap-input-wrapper {
    background-color: $showcase_white_color;
    border-radius: 2px;
    box-sizing: border-box;
    color: $showcase_text_color;
    cursor: text;
    display: flex;
    font-family: $showcase_font;
    position: relative;
    transition: border-color $animation $animation_time;
    width: 100%;
    flex-direction: row;

    &__icon {
        width: $showcase_size_unit;
        height: 100%;
        flex-shrink: 0;
        flex-grow: 0;
        box-sizing: border-box;
        padding: 3px 2px 3px 4px;
        color: #c9c9c9;

        & > svg {
            width: 100%;
            height: 100%;
        }
    }

    &__border {
        border: 1px solid $showcase_light_gray_color;
    }

    &:not(&__disabled):hover {
        border-color: $showcase_gray_color;
    }

    &:not(&__disabled):focus-within, &__focus:not(&__disabled) {
        border-color: $showcase_gray_color !important;
    }

    &__dirty {
        background-color: $showcase_white_color;
    }

    &__error {
        border-color: $showcase_primary_color;
    }

    &__disabled {
        background-color: $showcase_disabled_background_color;
        color: $showcase_disabled_color;
        cursor: not-allowed;
    }
}
</style>
