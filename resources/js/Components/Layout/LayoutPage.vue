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
                <span class="filters-count" style="display: none;">4</span>
                <span class="layout-page__header__filters-button" v-on:click="filtersOpen()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M4.17071 16C4.58254 14.8348 5.69378 14 7 14C8.3062 14 9.4175 14.8348 9.8293 16H20V18H9.8293C9.4175 19.1652 8.3062 20 7 20C5.69378 20 4.58254 19.1652 4.17071 18H0V16H4.17071ZM10.1707 9C10.5825 7.83481 11.6938 7 13 7C14.3062 7 15.4175 7.83481 15.8293 9H20V11H15.8293C15.4175 12.1652 14.3062 13 13 13C11.6938 13 10.5825 12.1652 10.1707 11H0V9H10.1707ZM4.17071 2C4.58254 0.83481 5.69378 0 7 0C8.3062 0 9.4175 0.83481 9.8293 2H20V4H9.8293C9.4175 5.16519 8.3062 6 7 6C5.69378 6 4.58254 5.16519 4.17071 4H0V2H4.17071ZM7 4C7.55228 4 8 3.55228 8 3C8 2.44772 7.55228 2 7 2C6.44772 2 6 2.44772 6 3C6 3.55228 6.44772 4 7 4ZM13 11C13.5523 11 14 10.5523 14 10C14 9.4477 13.5523 9 13 9C12.4477 9 12 9.4477 12 10C12 10.5523 12.4477 11 13 11ZM7 18C7.55228 18 8 17.5523 8 17C8 16.4477 7.55228 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z" fill="black"/>
                    </svg>
                </span>
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
import LayoutFilters from "@/Components/Layout/LayoutFilters";

export default {
    components: {
        LoadingProgress,
        LayoutFilters,
    },

    props: {
        title: {type: String, default: null},
        titleLink: {type: Object, default: null},

        loading: {type: Boolean, default: false},

        breadcrumbs: {type: Array, default: null},
        divider: {type: String, default: '/'},

        link: {type: Object, default: null},
        linkTitle: {type: String, default: null},
    },

    methods: {
        filtersOpen() {
            let allFiltersHidden = document.getElementsByClassName("layout-filters");
            allFiltersHidden[0].style.visibility = 'visible';
        },
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
        height: $base_size_unit;

        &__filters-button {
            display: none;
        }

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

@media (max-width: 767px) {
    .layout-page__header {
        flex-direction: column;
        row-gap: 20px;
        height: auto;
        align-items: end;
    }

    .input-dropdown__list-item {
        font-size: 13px !important;
    }

    .input-date__picker {
        left: -5% !important;
    }

    .layout-page {
        padding-left: 15px;
        padding-right: 15px;

        &__body {
            & .container.w-70 {
                width: 100%;
            }

            & .container.w-50 {
                width: 100%;
            }
        }
    }
}
</style>
