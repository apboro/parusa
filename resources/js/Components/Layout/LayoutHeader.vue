<template>
    <header class="application__header">
        <div class="application__header-wrapper">
            <div class="application__header-title">
                <router-link :to="{name:'home'}" :class="'application__header-title-link'">
                    <IconLogo :class="'application__header-title-link-icon'"/>
                </router-link>
                <span class="application__header-title-text">{{ user.organization }}</span>
            </div>
            <div class="application__header-menu" v-if="!hideMenu">
                <slot name="menu"/>
            </div>
            <div class="application__header-widgets">
                <slot name="widgets"/>
            </div>
            <div class="application__header-personal">
                <slot name="personal"/>
            </div>
        </div>
    </header>
</template>

<script>
import IconLogo from "../Icons/IconLogo";

export default {
    components: {IconLogo},

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
        hideMenu: {type: Boolean, default: false},
    },
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$page_max_width: 1200px !default;
$page_header_height: 60px !default;
$shadow_1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24) !default;
$base_light_gray_color: #e5e5e5 !default;
$base_white_color: #ffffff !default;

.application__header {
    height: $page_header_height;
    box-sizing: border-box;
    width: 100%;
    box-shadow: $shadow_1;
    background-color: $base_white_color;

    &-wrapper {
        width: calc(100% - 20px);
        max-width: $page_max_width;
        margin: 0 auto 20px;
        display: flex;
        height: $page_header_height;
        box-sizing: border-box;
    }

    &-title {
        flex-grow: 0;
        height: $page_header_height;
        min-width: $page_header_height;
        box-sizing: border-box;
        padding: 5px;
        display: flex;
        align-items: center;

        &-link {
            height: calc(#{$page_header_height} - 10px);
            width: calc(#{$page_header_height} - 10px);
            box-sizing: border-box;
            padding: 5px;
            display: block;
            border-radius: 50%;
            border: 1px solid $base_light_gray_color;

            &-icon {
                width: 100%;
                height: 100%;
            }
        }

        &-text {
            box-sizing: border-box;
            padding-left: 10px;
            font-family: $project_font;
            font-weight: bold;
            font-size: 14px;
            position: relative;
            top: -2px;
        }
    }

    &-menu {
        flex-grow: 1;
        height: 100%;
        margin: 0 15px;
        align-items: flex-end;
        display: flex;
    }

    &-personal, &-widgets {
        flex-grow: 0;
        height: 100%;
    }
}

@media (max-width: 767px) {
    .application__header {
        height: auto;
        width: calc(100% - 20px);
        margin: 0 auto;

        &-wrapper {
            height: auto;
            flex-direction: column;
        }

        &-personal {
            display: inline-flex;
            justify-content: flex-end;
        }

        &-menu {
            align-items: flex-start;
            flex-direction: column;
            padding-top: 20px;
        }
    }
}
</style>
