<template>
    <div class="ap-message">
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

.ap-message {
    font-family: $checkout_font;
    font-size: 20px;
    width: 100%;
    text-align: center;
    padding: 30px 20px;
    color: $checkout_text_color;
    box-sizing: border-box;
}
</style>
