<template>
    <div class="layout-page">
        <loading-progress :loading="loading">
            <div class="layout-page__header">
                <slot name="header" v-if="$slots.header"/>
                <template v-else>
                    <div class="layout-page__header-main">
                        <div class="layout-page__header-main-breadcrumbs" v-if="breadcrumbs">
                            <template v-for="link in breadcrumbs">
                                <router-link class="layout-page__header-main-breadcrumbs-link" :to="link['to']">{{ link['caption'] }}</router-link>
                                <span class="layout-page__header-main-breadcrumbs-divider">{{ divider }}</span>
                            </template>
                        </div>
                        <router-link v-if="titleLink" :to="titleLink" class="layout-page__header-main-title-link">{{ title }}</router-link>
                        <span v-else class="layout-page__header-main-title">{{ title }}</span>
                    </div>
                    <div class="layout-page__header-actions" v-if="$slots.actions || link">
                        <div class="layout-page__header-actions-link" v-if="link">
                            <router-link :class="'layout-page__header-actions-link-href'" :to="link">{{ linkTitle }}</router-link>
                        </div>
                        <slot name="actions" v-if="$slots.actions"/>
                    </div>
                </template>
            </div>
            <div class="layout-page__body">
                <slot/>
            </div>
            <div class="layout-page__footer" v-if="$slots.footer">
                <slot name="footer"/>
            </div>
        </loading-progress>
    </div>
</template>

<script>
import LoadingProgress from "@/Components/LoadingProgress";

export default {
    components: {LoadingProgress},

    props: {
        title: {type: String, default: null},
        titleLink: {type: Object, default: null},

        loading: {type: Boolean, default: false},

        breadcrumbs: {type: Array, default: null},
        divider: {type: String, default: '/'},

        link: {type: Object, default: null},
        linkTitle: {type: String, default: null},
    },
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$base_size_unit: 35px !default;
$page_max_width: 1200px !default;
$shadow_1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24) !default;
$base_white_color: #ffffff !default;
$base_black_color: #1e1e1e !default;
$base_light_gray_color: #e5e5e5 !default;
$base_text_gray_color: #3f3f3f !default;
$base_gray_color: #8f8f8f !default;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;

.layout-page {
    max-width: $page_max_width;
    width: calc(100% - 20px);
    margin: 20px auto 30px;
    background-color: $base_white_color;
    box-shadow: $shadow_1;
    border-radius: 3px;
    box-sizing: border-box;
    padding: 20px 30px 30px;
    border-style: solid;
    border-color: #e5e5e5;
    border-width: 1px;

    &__header {
        box-sizing: content-box;
        padding-bottom: 20px;
        border-bottom: 1px solid $base_light_gray_color;
        display: flex;
        flex-direction: row;
        height: $base_size_unit;

        &-main {
            flex-shrink: 0;
            flex-grow: 1;
            font-family: $project_font;
            font-size: 20px;
            display: flex;
            flex-direction: row;
            align-items: center;
            box-sizing: border-box;

            &-breadcrumbs {
                &-link {
                    color: $base_primary_color;
                    text-decoration: none;
                    font-weight: bold;

                    &:hover {
                        color: $base_primary_hover_color
                    }
                }

                &-divider {
                    margin: 0 5px;
                    color: $base_gray_color;
                }
            }

            &-title, &-title-link {
                font-weight: bold;
                color: $base_text_gray_color;
            }

            &-title-link {
            }
        }

        &-actions {
            display: flex;
            flex-shrink: 0;
            align-items: center;

            &-link {
                font-size: 14px;
                margin-right: 20px;

                &-href {
                    font-family: $project_font;
                    color: $base_primary_color;
                    cursor: pointer;
                    transition: color $animation $animation_time;
                    text-decoration: none;

                    &:hover {
                        color: $base_primary_hover_color;
                    }
                }
            }
        }
    }

    &__body {
    }

    &__footer {
        box-sizing: border-box;
        padding-top: 10px;
        border-top: 1px solid $base_light_gray_color;
        font-family: $project_font;
        font-size: 16px;
    }
}
</style>
