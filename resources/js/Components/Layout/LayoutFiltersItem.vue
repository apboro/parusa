<template>
    <div class="layout-filters-item">
        <div class="layout-filters-item__title" :class="{'layout-filters-item__title-empty': !title}">
            <span v-if="title">{{ title }}</span>
        </div>
        <div class="layout-filters-item__container">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: null,
        }
    }
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_text_gray_color: #3f3f3f !default;

.layout-filters-item {
    display: flex;
    flex-direction: column;

    &__title {
        flex-grow: 1;
        font-size: 14px;
        font-family: $project_font;
        margin-bottom: 5px;
        color: $base_text_gray_color;

        &-empty {
            margin-bottom: 0;
        }
    }

    &__container {
        display: flex;
    }
}
</style>
