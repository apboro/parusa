<template>
    <button class="button" :class="classProxy" @click.stop.prevent="click">
        <slot></slot>
    </button>
</template>

<script>
export default {
    props: {
        identifier: {type: String, default: null},
        disabled: {type: Boolean, default: false},
        color: {
            type: String,
            default: null,
            validation: (value) => {
                return value === null || ['red', 'green', 'orange'].indexOf(value) !== -1;
            }
        },
    },

    emits: ['clicked'],

    computed: {
        classProxy() {
            return 'button__' + this.color + (this.disabled ? ' button__disabled' : '');
        }
    },

    methods: {
        click() {
            if (!this.disabled) {
                this.$emit('clicked', this.identifier);
            }
        }
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24) !default;
$shadow_hover: 0 2px 4px rgba(0, 0, 0, 0.25), 0 2px 4px rgba(0, 0, 0, 0.22) !default;
$base_size_unit: 35px !default;
$base_white_color: #ffffff !default;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;
$base_red_color: #EA1B00 !default;
$base_red_hover_color: lighten(#EA1B00, 5%) !default;
$base_green_color: #00AF2E !default;
$base_green_hover_color: lighten(#00AF2E, 5%) !default;
$base_orange_color: #EA8B00 !default;
$base_orange_hover_color: lighten(#EA8B00, 5%) !default;
$base_disabled_color: #3f3f3f !default;

.button {
    display: inline-block;
    text-decoration: none;
    height: $base_size_unit;
    line-height: $base_size_unit;
    text-align: center;
    cursor: pointer;
    border-radius: 2px;
    box-sizing: border-box;
    padding: 0 math.div($base_size_unit, 2);
    letter-spacing: 0.03rem;
    color: $base_white_color;
    border: 1px solid $base_primary_color;
    background-color: $base_primary_color;
    transition: background-color $animation $animation_time, border-color $animation $animation_time, box-shadow $animation $animation_time;
    font-family: $project_font;
    font-size: 14px;
    box-shadow: $shadow_1;
    text-transform: uppercase;
    white-space: nowrap;
    @include no_selection;

    &:not(:last-child) {
        margin-right: 20px;
    }

    &:hover {
        box-shadow: $shadow_hover;
        background-color: $base_primary_hover_color;
        border-color: $base_primary_hover_color;
    }

    &:not(&__disabled):active {
        box-shadow: none;
    }

    &__disabled {
        background-color: $base_disabled_color !important;
        border-color: $base_disabled_color !important;
        cursor: not-allowed;
        box-shadow: $shadow_1 !important;

        &:hover {
        }
    }

    &__green {
        background-color: $base_green_color;
        border-color: $base_green_color;

        &:hover {
            background-color: $base_green_hover_color;
            border-color: $base_green_hover_color;
        }
    }

    &__red {
        background-color: $base_red_color;
        border-color: $base_red_color;

        &:hover {
            background-color: $base_red_hover_color;
            border-color: $base_red_hover_color;
        }
    }

    &__orange {
        background-color: $base_orange_color;
        border-color: $base_orange_color;

        &:hover {
            background-color: $base_orange_hover_color;
            border-color: $base_orange_hover_color;
        }
    }
}
</style>
