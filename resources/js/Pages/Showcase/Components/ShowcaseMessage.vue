<template>
    <div class="ap-message" :class="classFromAttributes">
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

<style lang="scss" scoped>
@import "../variables";

.ap-message {
    font-family: $showcase_font;
    font-size: 20px;
    width: 100%;
    text-align: center;
    padding: 30px 20px;
    color: $showcase_text_color;
    box-sizing: border-box;
}
</style>
