<template>
    <div class="application__header-widget" @click="redirect">
        <div class="application__header-widget-icon">
            <slot name="icon"/>
        </div>
        <div class="application__header-widget-info">
            <span v-if="title" class="application__header-widget-info-title">{{ title }}</span>
            <span v-if="subtitle" class="application__header-widget-info-subtitle">{{ subtitle }}</span>
            <span v-if="sum" class="application__header-widget-info-sum">{{ sum }}</span>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        route: {type: Object, default: null},
        title: {type: String, default: null},
        subtitle: {type: String, default: null},
        sum: {type: String, default: null},
    },

    methods: {
        redirect() {
            if (this.route !== null) {
                const name = this.route['name'];
                const params = typeof this.route['params'] !== "undefined" ? this.route['params'] : {};
                this.$router.push({name: name, params: params});
            }
        },
    }
}
</script>

<style lang="scss">
@use "sass:math";
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$page_header_height: 60px !default;
$base_primary_color: #1660ad !default;
$base_gray_color: #8f8f8f !default;

.application__header-widget {
    display: flex;
    flex-grow: 0;
    flex-shrink: 0;
    align-items: center;
    height: 100%;
    box-sizing: border-box;
    padding: 0 5px 0 0;
    cursor: pointer;
    position: relative;
    margin-right: 10px;

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

        &-title {
            font-size: 14px;
        }

        &-subtitle {
            font-size: 12px;
            color: $base_primary_color;
        }

        &-sum {
            font-size: 18px;
        }
    }
}
</style>
