<template>
    <div class="text" :class="classFromAttributes">
        <slot/>
    </div>
</template>

<script>

import AttributeKeysToClass from "@/Components/Helpers/AttributeKeysToClass";

export default {
    inheritAttrs: false,
    mixins: [AttributeKeysToClass],
}
</script>

<style lang="scss">
@import "../variables";

$project_font: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji !default;
$base_black_color: #1e1e1e !default;

.text {
    font-family: $project_font;
    text-align: left;
    box-sizing: border-box;
}
</style>
