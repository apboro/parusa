<template>
    <div class="ap-button" :class="classProxy" @click.stop.prevent="click">
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        disabled: {type: Boolean, default: false},
        color: {type: String, default: 'default', validation: value => ['default', 'light'].indexOf(value) !== -1},
    },

    emits: ['clicked'],

    computed: {
        classProxy() {
            return 'ap-button__' + this.color + (this.disabled ? ' ap-button__disabled' : '');
        }
    },

    methods: {
        click(event) {
            if (!this.disabled) {
                this.$emit('clicked', event);
            }
        }
    }
}
</script>

<style lang="scss" scoped>
@use "sass:math";
@import "../variables";

$base_purple_color: #241B5B !default;
$base_purple_hover_color: lighten(#241B5B, 20%) !default;
$base_white_color: #ffffff !default;

.ap-button {
    display: inline-block;
    text-decoration: none;
    height: $showcase_size_unit;
    line-height: $showcase_size_unit;
    text-align: center;
    cursor: pointer;
    box-sizing: border-box;
    padding: 0 math.div($showcase_size_unit, 2) 0;
    letter-spacing: 0.03rem;
    transition: background-color $animation $animation_time, border-color $animation $animation_time, color $animation $animation_time;
    font-family: $showcase_font;
    font-size: 14px;
    white-space: nowrap;
    border: 1px solid;
    @include no_selection;

    &__disabled {
        color: $showcase_white_color !important;
        border-color: $showcase_disabled_color !important;
        background-color: $showcase_disabled_color !important;
        cursor: not-allowed;

        &:hover {
        }
    }

    &__default:not(&__disabled) {
        background-color: $showcase_primary_color;
        border-color: $showcase_primary_color;
        color: $showcase_white_color;

        &:hover {
            background-color: $showcase_primary_hover_color;
            border-color: $showcase_primary_hover_color;
            color: $showcase_white_color;
        }
    }

    &__light:not(&__disabled) {
        background-color: $showcase_white_color;
        border-color: $showcase_primary_hover_color;
        color: $showcase_primary_hover_color;

        &:hover {
            background-color: $showcase_primary_hover_color;
            border-color: $showcase_primary_hover_color;
            color: $showcase_white_color;
        }
    }

    &__purple {
        background-color: $base_purple_color;
        border-color: $base_purple_color;
        color: $base_white_color;

        &:hover {
            background-color: $base_purple_hover_color;
            border-color: $base_purple_hover_color;
        }
    }
}
</style>
