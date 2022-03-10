<template>
    <div class="balance-widget" v-if="!$route.meta['hideWidget']">
        <router-link :to="{name: 'order'}" class="balance-widget__item">
            <div class="balance-widget__item-wrapper">
                <span class="balance-widget__item-title">Текущий заказ</span>
                <span class="balance-widget__item-value">{{ loaded ? order_amount + ' руб.' : '—' }}</span>
            </div>
            <IconShoppingCart :class="'balance-widget__item-icon'"/>
        </router-link>
    </div>
</template>

<script>
import {mapState} from 'vuex';
import IconShoppingCart from "@/Components/Icons/IconShoppingCart";
import IconPlusCircle from "@/Components/Icons/IconPlusCircle";

export default {
    components: {
        IconPlusCircle,
        IconShoppingCart,
    },

    computed: {
        ...mapState('terminal', {
            loaded: state => state.loaded,
            order_amount: state => state.order_amount,
            current: state => state.current,
        }),
    },

    data: () => ({
        updater: null,
    }),

    created() {
        this.refresh();
        this.updater = setInterval(this.refresh, 15000);
    },

    methods: {
        refresh() {
            this.$store.dispatch('terminal/refresh');
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
</style>
