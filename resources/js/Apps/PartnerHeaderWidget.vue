<template>
    <div class="balance-widget__wrapper">
        <div class="balance-widget">
            <router-link :to="{name: 'order'}" class="balance-widget__item">
                <div class="balance-widget__item-wrapper">
                    <span class="balance-widget__item-title">Ваш заказ</span>
                    <span class="balance-widget__item-value">{{ loaded ? order_amount + ' руб.' : '—' }}</span>
                </div>
                <icon-shopping-cart :class="'balance-widget__item-icon'"/>
            </router-link>

            <router-link :to="{name: 'reserves-registry'}" class="balance-widget__item" v-if="can_reserve">
                <div class="balance-widget__item-wrapper">
                    <span class="balance-widget__item-title">Брони</span>
                    <span class="balance-widget__item-value center">{{ loaded ? reserves + ' руб.' : '—' }}</span>
                </div>
            </router-link>

            <router-link :to="{name: 'company-account'}" class="balance-widget__item">
                <div class="balance-widget__item-wrapper">
                    <span class="balance-widget__item-title">Баланс лицевого счёта</span>
                    <span class="balance-widget__item-value">{{ loaded ? amount + ' руб.' : '—' }}</span>
                </div>
            </router-link>

            <router-link :to="{name: 'company-account'}" class="balance-widget__item">
                <div class="balance-widget__item-wrapper">
                    <span class="balance-widget__item-title">Вы заработали</span>
                    <span class="balance-widget__item-value center">{{ loaded ? total + ' руб.' : '—' }}</span>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
import Container from "@/Components/GUI/GuiContainer";
import {mapState} from 'vuex';
import IconShoppingCart from "@/Components/Icons/IconShoppingCart";

export default {
    components: {
        IconShoppingCart,
        Container,
    },

    computed: {
        ...mapState('partner', {
            loaded: state => state.loaded,
            amount: state => state.amount,
            total: state => state.total,
            limit: state => state.limit,
            reserves: state => state.reserves,
            can_reserve: state => state.can_reserve,
            order_amount: state => state.order_amount,
        }),
    },

    data: () => ({
        updater: null,
    }),

    created() {
        this.updater = setInterval(this.refresh, 10000);
    },

    methods: {
        refresh() {
            this.$store.dispatch('partner/refresh');
        },
    },
}
</script>

<style lang="scss">
$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$animation_time: 150ms !default;
$animation: cubic-bezier(0.24, 0.19, 0.28, 1.29) !default;
$shadow_1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24) !default;
$shadow_hover: 0 2px 4px rgba(0, 0, 0, 0.25), 0 2px 4px rgba(0, 0, 0, 0.22) !default;
$base_white_color: #ffffff !default;
$base_light_gray_color: #e5e5e5 !default;
$base_black_color: #1e1e1e;
$base_primary_color: #0D74D7 !default;
$base_primary_hover_color: lighten(#0D74D7, 10%) !default;
$widget_base: 90px;

.balance-widget {
    max-width: 1200px;
    width: calc(100% - 20px);
    margin: 0 auto;
    text-align: right;

    &__item {
        display: inline-flex;
        font-family: $project_font;
        height: $widget_base;
        min-width: $widget_base;
        background-color: $base_white_color;
        box-shadow: $shadow_1;
        box-sizing: border-box;
        border-radius: 2px;
        padding: 20px;
        text-align: left;
        text-decoration: none;
        transition: box-shadow $animation $animation_time;

        &:not(:last-child) {
            margin-right: 20px;
        }

        &:hover {
            box-shadow: $shadow_hover;
        }

        &-wrapper {
            display: flex;
            flex-direction: column;
        }

        &-title {
            font-size: 14px;
            color: $base_primary_color;
            height: 20px;
            line-height: 20px;
            transition: color $animation $animation_time;
        }

        &:hover &-title{
            color: $base_primary_hover_color;
        }

        &-value {
            font-size: 24px;
            color: $base_black_color;
            height: 30px;
            line-height: 30px;
        }

        &-icon {
            color: $base_light_gray_color;
            margin-left: 15px;
            width: 35px;
        }
    }
}

@media (max-width: 767px) {
    .application__header {
        width: 100%;

        &-wrapper {
            align-items: center;
            flex-direction: row;
        }

        &-menu {
            order: 3;
            padding-top: 0;
            flex-grow: 0;
            margin: 0 15px 5px;
        }

        &-title {
            flex-grow: 1;

            &-text {
                display: none;
            }
        }
    }

    .application__user-menu {
        margin-top: 0;

        &-drop {
            display: none;
        }

        &-icon {
            padding-right: 20px;
        }
    }

    .application__menu-wrapper {
        .application__menu-burger span {
            background: #49454F;

            &::before {
                background: #49454F;
            }

            &::after {
                background: #49454F;
            }
        }
    }

    .balance-widget__wrapper {
        overflow: auto;
        padding-bottom: 10px;

        &::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        &::-webkit-scrollbar-track {
            background: #BAC1C6;
            width: 2px;
            height: 4px;
        }

        &::-webkit-scrollbar-thumb {
            background-color: #0B68C2;
            border-radius: 10px;
            width: 4px;
            height: 4px;
        }
    }

    .balance-widget {
        width: max-content;
        padding: 0 10px;

        &__item {
            border-radius: 8px;
            border: 1px solid #E6E6E6;
            box-shadow: none;
            height: auto;
            min-width: unset;
            padding: 10px 15px;

            &:not(:last-child) {
                margin-right: 10px;
            }

            &-title {
                font-size: 12px;
                color: #1E96F4;
            }

            &-value {
                font-size: 18px;
            }

            &-icon {
                display: none;
            }
        }
    }

    .pagination {
        justify-content: center;
        flex-direction: row !important;
        flex-wrap: wrap;

        &__links {
            width: 100%;

            &-button {
                width: 25px !important;
                height: auto !important;
            }
        }
    }

    .layout-page {
        box-shadow: none !important;
        border-color: transparent !important;
        padding-left: 0 !important;
        padding-right: 0 !important;

        &__header {
            flex-direction: row !important;

            &__filters-button {
                display: inline-block !important;
            }
        }
    }

    html, body {
        background-color: transparent !important;
        overflow-x: hidden;
    }

    .list-table__wrapper {
        overflow: auto;

        &::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        &::-webkit-scrollbar-track {
            background: #BAC1C6;
            width: 2px;
            height: 4px;
        }

        &::-webkit-scrollbar-thumb {
            background-color: #0B68C2;
            border-radius: 10px;
            width: 4px;
            height: 4px;
        }
    }

    .application__menu-wrapper-active {
        .application__menu {
            position: fixed;
            background: #fff;
            left: 0;
            top: 0;
            height: 100% !important;
            padding: 20px 30px 30px 20px !important;
            width: 70%;
            z-index: 999;

            .application__header-title {
                flex-grow: 0;
                display: flex !important;
                margin-bottom: 20px;
            }

            &::after {
                position: absolute;
                right: -30%;
                top: 0;
                height: 100%;
                width: 30%;
                content: '';
                background: #000;
                opacity: 0.2;
            }
        }

        .application__menu-burger {
            position: fixed;
            right: 25%;
            top: 35px;
            z-index: 9999;

            span {
                background: #1E96F4;

                &::before {
                    background: #1E96F4;
                }
            }
        }
    }

    .layout-filters {
        position: fixed;
        background: #fff;
        left: 0;
        top: 0;
        height: 100%;
        padding: 30px 30px 30px 20px !important;
        width: 84%;
        z-index: 999;
        visibility: hidden;

        &::after {
            position: absolute;
            right: -25%;
            top: 0;
            height: 100%;
            width: 25%;
            content: '';
            background: #000;
            opacity: 0.2;
        }

        &__title {
            color: #000;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
            font-size: 22px;
            font-style: normal;
            font-weight: 500;
            line-height: 28px;
            margin-bottom: 30px;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
        }

        &__close {
            color: #1E96F4;
            font-size: 40px;
            font-weight: 400;
            padding-bottom: 5px;
        }
    }

    .input-wrapper {
        border-radius: 5px !important;
        border: 0.5px solid #BFBFBF !important;
        height: 39px !important;
    }

    .svg-calendar {
        top: 7px;
        right: 14px;
        position: absolute;
        display: inline-block !important;
    }

    .filters-count {
        border-radius: 60px;
        background: #0B68C2;
        color: #fff;
        display: flex !important;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 5px 9px;
        font-size: 12px;
        margin-right: 15px;
        margin-bottom: 2px;
    }

    .input-dropdown__value {
        align-items: center;
    }

    .icon-button {
        display: none !important;
    }
}

@media (max-width: 385px) {
    .list-table__header-cell {
        padding-left: 25px !important;
        padding-right: 25px !important;
    }
}

</style>
