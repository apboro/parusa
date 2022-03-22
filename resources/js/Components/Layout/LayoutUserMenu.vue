<template>
    <div class="application__user-menu" @click="toggle">
        <div class="application__user-menu-icon">
            <icon-user v-if="!user.avatar"/>
        </div>
        <div class="application__user-menu-info">
            <span class="application__user-menu-info-name">{{ user.name }}</span>
            <span class="application__user-menu-info-org">{{ user.position }}</span>
        </div>
        <icon-dropdown class="application__user-menu-drop" :class="{'application__user-menu-drop-dropped': show_menu}"/>
        <div class="application__user-menu-actions" :class="{'application__user-menu-actions-shown': show_menu}">
            <slot v-if="$slots.default"/>
        </div>
    </div>
</template>

<script>
import IconUser from "../Icons/IconUser";
import IconDropdown from "../Icons/IconDropdown";

export default {
    components: {IconDropdown, IconUser},
    props: {
        user: {
            type: Object,
            default: () => ({
                name: null,
                organization: null,
                position: null,
                avatar: null,
            })
        },
    },
    data: () => ({
        show_menu: false,
    }),
    methods: {
        toggle() {
            if (this.show_menu === true) {
                this.show_menu = false;
                setTimeout(() => {
                    window.removeEventListener('click', this.close);
                }, 100);
            } else {
                this.show_menu = true;
                setTimeout(() => {
                    window.addEventListener('click', this.close);
                }, 100);
            }
        },

        close() {
            window.removeEventListener('click', this.close);
            this.show_menu = false;
        },
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$page_header_height: 60px!default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_2: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23) !default;

$base_text_gray_color: #3f3f3f !default;
$base_gray_color: #8f8f8f !default;
$base_white_color: #ffffff !default;
$base_primary_color: #1660ad !default;
$base_primary_hover_color: #1e87f0 !default;

.application__user-menu {
    display: flex;
    flex-grow: 0;
    flex-shrink: 0;
    align-items: center;
    height: 100%;
    box-sizing: border-box;
    padding: 0 5px 0 0;
    cursor: pointer;
    position: relative;

    &-icon {
        height: $page_header_height;
        padding: math.div($page_header_height, 4) 8px;
        box-sizing: border-box;
        color: $base_gray_color;

        & > svg {
            display: block;
            width: 100%;
            height: 100%;
        }
    }

    &-info {
        display: flex;
        flex-direction: column;
        font-family: $project_font;

        &-name {
            font-size: 12px;
            font-weight: bold;
            line-height: 18px;
            color: $base_primary_color;
        }

        &-org {
            font-size: 12px;
            line-height: 18px;
            color: $base_text_gray_color;
        }
    }

    &-drop {
        width: 8px;
        margin-left: 10px;
        display: flex;
        align-items: center;
        transition: transform $animation $animation_time;

        &-dropped {
            transform: rotate(-180deg);
        }
    }

    &-actions {
        position: absolute;
        right: 0;
        bottom: 5px;
        transform: translateY(100%);
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
        cursor: default;

        &-shown {
            opacity: 1;
            visibility: visible;
        }

        & > * {
            color: $base_primary_color;
            transition: color $animation $animation_time;
            font-family: $project_font;
            font-size: 14px;
            white-space: nowrap;
            cursor: pointer;
            text-decoration: none;
        }

        & > *:hover {
            color: $base_primary_hover_color;
        }
    }
}
</style>
