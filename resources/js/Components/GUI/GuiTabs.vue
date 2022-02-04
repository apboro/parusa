<template>
    <div class="tabs" v-if="tabs">
        <span class="tabs__tab" v-for="(tab, key) in tabs"
              :key="key"
              :class="{'tabs__tab-active': key === current}"
              @click="current = key"
        >{{ tab }}</span>
    </div>
</template>

<script>
export default {
    props: {
        tabs: {type: Object, default: null},
        initial: {type: String, default: null},
    },

    emits: ['change'],

    data: () => ({
        current_tab: null,
    }),

    computed: {
        current: {
            get() {
                return this.current_tab !== null ? this.current_tab : Object.keys(this.tabs)[0];
            },
            set(value) {
                if (this.current_tab === value) {
                    return;
                }
                this.current_tab = value;
                this.$emit('change', value);
            }
        }
    },

    created() {
        this.current_tab = (typeof this.initial !== "undefined" && this.initial !== null) ? this.initial : Object.keys(this.tabs)[0];
        this.$emit('change', this.current);
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$base_white_color: #ffffff;
$base_black_color: #1e1e1e;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;
$base_light_gray_color: #e5e5e5 !default;
$base_lightest_gray_color: #f7f7f7 !default;

.tabs {
    width: 100%;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap-reverse;
    box-sizing: border-box;
    padding: 15px 3px 10px;
    @include no_selection;

    &__tab {
        font-size: 14px;
        font-family: $project_font;
        height: $base_size_unit;
        line-height: $base_size_unit;
        white-space: nowrap;
        box-sizing: border-box;
        padding: 0 12px;
        border-style: solid;
        color: $base_primary_color;
        border-color: $base_light_gray_color;
        border-width: 1px 1px 0;
        border-radius: 4px 4px 0 0;
        margin: 3px 2px 0;
        background-color: $base_lightest_gray_color;
        cursor: pointer;
        transition: color $animation $animation_time;
        position: relative;

        &:before, &:after {
            content: '';
            display: block;
            height: 1px;
            background-color: $base_light_gray_color;
            position: absolute;
            bottom: 0;
        }

        &:before {
            width: calc(50% + 6px);
            left: -3px;
        }

        &:after {
            width: calc(50% + 6px);
            right: -3px;
        }

        &:not(&-active):hover {
            color: $base_primary_hover_color;
        }

        &-active {
            color: $base_black_color;
            background-color: $base_white_color;
            cursor: default;

            &:before, &:after {
                width: 3px;
            }
        }
    }
}
</style>
