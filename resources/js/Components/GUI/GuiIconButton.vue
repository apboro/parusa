<template>
    <div class="icon-button" :class="classProxy" @click.prevent="click">
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        identifier: {type: String, default: null},
        disabled: {type: Boolean, default: false},
        border: {type: Boolean, default: true},
        color: {
            type: String,
            default: null,
            validation: (value) => {
                return value === null || ['blue', 'red', 'green', 'orange'].indexOf(value) !== -1;
            }
        },
    },

    computed: {
        classProxy() {
            return (this.border ? 'icon-button__border ' : '') + 'icon-button__' + this.color + (this.disabled ? ' icon-button__disabled' : '');
        }
    },

    methods: {
        click() {
            if (!this.disabled) {
                this.$emit('clicked', this.identifier);
            }
        }
    },
}
</script>

<style lang="scss">
@import "../variables";

$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$base_white_color: #ffffff !default;
$base_black_color: #1e1e1e !default;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;
$base_red_color: #EA1B00 !default;
$base_red_hover_color: lighten(#EA1B00, 5%) !default;
$base_green_color: #00AF2E !default;
$base_green_hover_color: lighten(#00AF2E, 5%) !default;
$base_orange_color: #EA8B00 !default;
$base_orange_hover_color: lighten(#EA8B00, 5%) !default;
$base_disabled_color: #3f3f3f !default;

.icon-button {
    display: inline-flex;
    justify-content: center;
    vertical-align: top;
    width: $base_size_unit;
    height: $base_size_unit;
    padding: $base_size_unit * 0.25;
    flex-grow: 0;
    flex-shrink: 0;
    box-sizing: border-box;
    border-radius: 2px;
    cursor: pointer;
    border: 1px solid transparent;
    background-color: transparent;
    color: $base_black_color;
    transition: color $animation $animation_time, background-color $animation $animation_time, border-color $animation $animation_time;
    @include no_selection;

    &__border {
        border: 1px solid $base_black_color;
    }

    &__border#{&}__blue {
        border-color: $base_primary_color;
    }

    &__border#{&}__green {
        border-color: $base_green_color;
    }

    &__border#{&}__red {
        border-color: $base_red_color;
    }

    &__border#{&}__orange {
        border-color: $base_orange_color;
    }

    &:hover {
        border-color: $base_primary_hover_color;
        background-color: $base_primary_hover_color;
        color: $base_white_color;
    }


    &__disabled {
        background-color: $base_disabled_color !important;
        border-color: $base_disabled_color !important;
        cursor: not-allowed;

        &:hover {
        }
    }

    &__blue {
        color: $base_primary_color;

        &:hover {
            color: $base_white_color;
            background-color: $base_primary_hover_color;
            border-color: $base_primary_hover_color;
        }
    }

    &__green {
        color: $base_green_color;

        &:hover {
            color: $base_white_color;
            background-color: $base_green_hover_color;
            border-color: $base_green_hover_color;
        }
    }

    &__red {
        color: $base_red_color;

        &:hover {
            color: $base_white_color;
            background-color: $base_red_hover_color;
            border-color: $base_red_hover_color;
        }
    }

    &__orange {
        color: $base_orange_color;

        &:hover {
            color: $base_white_color;
            background-color: $base_orange_hover_color;
            border-color: $base_orange_hover_color;
        }
    }
}
</style>

