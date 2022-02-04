<template>
    <div class="actions-menu">
        <div class="actions-menu__button" @click="toggle" :class="{'actions-menu__button-active':dropped}">
            <span class="actions-menu__button-title" v-if="title">{{ title }}</span>
            <IconBars/>
        </div>
        <div class="actions-menu__actions" :class="{'actions-menu__actions-shown': dropped}">
            <slot/>
        </div>
    </div>
</template>

<script>
import IconBars from "../Icons/IconBars";

export default {
    props: {
        title: {type: String, default: 'Действия'},
    },

    components: {IconBars},

    data: () => ({
        dropped: false,
    }),

    methods: {
        toggle() {
            if (this.dropped === true) {
                this.dropped = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                this.dropped = true;
                this.$emit('dropped');
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.dropped = false;
        },
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_2: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23) !default;
$base_size_unit: 35px !default;
$base_white_color: #ffffff !default;
$base_primary_color: #1660ad !default;
$base_primary_hover_color: #1e87f0 !default;

.actions-menu {
    display: inline-block;
    flex-grow: 0;
    flex-shrink: 0;
    height: $base_size_unit;
    line-height: $base_size_unit;
    box-sizing: border-box;
    position: relative;
    text-align: left;

    &__button {
        border: 1px solid $base_primary_color;
        box-sizing: border-box;
        border-radius: 2px;
        padding: 0 10px;
        display: inline-flex;
        flex-direction: row;
        align-items: center;
        font-family: $project_font;
        color: $base_primary_color;
        font-size: 14px;
        cursor: pointer;
        height: 100%;
        background-color: transparent;
        transition: color $animation $animation_time, background-color $animation $animation_time, border-color $animation $animation_time;

        &:hover, &-active {
            color: $base_white_color;
            border-color: $base_primary_hover_color;
            background-color: $base_primary_hover_color;
        }

        &-title {
            margin-right: 8px;
        }

        & > svg {
            width: 14px;
            height: 100%;
        }
    }

    &__actions {
        position: absolute;
        right: 0;
        top: $base_size_unit + 6px;
        box-sizing: border-box;
        padding: 12px 20px;
        border-radius: 2px;
        min-width: 100%;
        z-index: 50;
        background-color: $base_white_color;
        box-shadow: $shadow_2;
        display: flex;
        flex-direction: column;
        line-height: 30px;
        opacity: 0;
        visibility: hidden;
        transition: opacity $animation $animation_time, visibility $animation $animation_time;

        &:before {
            content: '';
            display: block;
            background-color: $base_white_color;
            width: 6px;
            height: 6px;
            position: absolute;
            right: 16px;
            top: -4px;
            transform: rotate(45deg);
            border-color: #e9e9e9;
            border-style: solid;
            border-width: 1px 0 0 1px;
        }

        &-shown {
            opacity: 1;
            visibility: visible;
        }

        &>* {
            font-size: 14px;
            white-space: nowrap;
        }
    }
}
</style>
